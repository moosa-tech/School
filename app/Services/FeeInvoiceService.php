<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\FeeAdjustment;
use App\Models\FeeAuditLog;
use App\Models\FeeCategory;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeePayment;
use App\Models\FeePaymentAllocation;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FeeInvoiceService
{
    /**
     * Create an itemized fee invoice for a student.
     */
    public function generateInvoice(
        int $schoolId,
        int $studentId,
        int $academicYearId,
        string $billingPeriod,
        string $issueDate,
        string $dueDate,
        array $items, // array of ['category_id' => x, 'description' => '', 'amount' => 100]
        float $discountTotal = 0.00,
        float $fineTotal = 0.00
    ): FeeInvoice {
        Student::forSchool($schoolId)->findOrFail($studentId);
        AcademicYear::forSchool($schoolId)->findOrFail($academicYearId);

        foreach ($items as $item) {
            if ((float) ($item['amount'] ?? 0) < 0) {
                throw ValidationException::withMessages(['items' => 'Invoice item amounts cannot be negative.']);
            }

            if (! empty($item['category_id']) && ! FeeCategory::forSchool($schoolId)->whereKey($item['category_id'])->exists()) {
                throw ValidationException::withMessages(['items' => 'A fee category does not belong to this school.']);
            }
        }

        if ($discountTotal < 0 || $fineTotal < 0) {
            throw ValidationException::withMessages(['discount_total' => 'Discount and fine amounts cannot be negative.']);
        }

        // Prevent duplicate invoice for same student and billing period
        $exists = FeeInvoice::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('billing_period', $billingPeriod)
            ->exists();

        if ($exists) {
            throw new \RuntimeException("An invoice for student #{$studentId} for '{$billingPeriod}' already exists.");
        }

        return DB::transaction(function () use ($schoolId, $studentId, $academicYearId, $billingPeriod, $issueDate, $dueDate, $items, $discountTotal, $fineTotal) {
            $subtotal = 0.00;
            foreach ($items as $item) {
                $subtotal += (float) ($item['amount'] ?? 0);
            }

            $totalAmount = max(0.00, $subtotal - $discountTotal + $fineTotal);
            $invoiceNumber = 'INV-'.str_pad((string) $schoolId, 3, '0', STR_PAD_LEFT).'-'.strtoupper(Str::random(6));

            $invoice = FeeInvoice::create([
                'school_id' => $schoolId,
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
                'invoice_number' => $invoiceNumber,
                'billing_period' => $billingPeriod,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'fine_total' => $fineTotal,
                'total_amount' => $totalAmount,
                'paid_amount' => 0.00,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                FeeInvoiceItem::create([
                    'school_id' => $schoolId,
                    'fee_invoice_id' => $invoice->id,
                    'fee_category_id' => $item['category_id'] ?? null,
                    'description' => $item['description'] ?? 'Fee item',
                    'amount' => (float) $item['amount'],
                ]);
            }

            FeeAuditLog::create([
                'school_id' => $schoolId,
                'entity_type' => 'FeeInvoice',
                'entity_id' => $invoice->id,
                'action' => 'created',
                'old_values' => null,
                'new_values' => $invoice->toArray(),
                'actor_id' => auth()->id(),
                'created_at' => now(),
            ]);

            return $invoice;
        });
    }

    /**
     * Receive payment, create receipt, and allocate to invoice.
     */
    public function receivePayment(
        int $schoolId,
        int $studentId,
        int $feeInvoiceId,
        float $amount,
        string $paymentDate,
        string $method = 'cash',
        ?string $reference = null,
        ?string $notes = null,
        ?int $receivedBy = null
    ): FeePayment {
        return DB::transaction(function () use ($schoolId, $studentId, $feeInvoiceId, $amount, $paymentDate, $method, $reference, $notes, $receivedBy) {
            $invoice = FeeInvoice::forSchool($schoolId)->lockForUpdate()->findOrFail($feeInvoiceId);

            if ((int) $invoice->student_id !== $studentId) {
                abort(404);
            }

            if ($amount <= 0 || $amount > $invoice->outstandingBalance()) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment must be positive and cannot exceed the invoice balance.',
                ]);
            }

            $receiptNumber = 'REC-'.str_pad((string) $schoolId, 3, '0', STR_PAD_LEFT).'-'.strtoupper(Str::random(6));

            $payment = FeePayment::create([
                'school_id' => $schoolId,
                'student_id' => $studentId,
                'receipt_number' => $receiptNumber,
                'payment_date' => $paymentDate,
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
                'received_by' => $receivedBy ?? auth()->id(),
                'status' => 'completed',
                'notes' => $notes,
            ]);

            FeePaymentAllocation::create([
                'school_id' => $schoolId,
                'fee_payment_id' => $payment->id,
                'fee_invoice_id' => $invoice->id,
                'amount_applied' => $amount,
            ]);

            // Server-side recalculation of invoice status
            $invoice->recalculateStatus();

            FeeAuditLog::create([
                'school_id' => $schoolId,
                'entity_type' => 'FeePayment',
                'entity_id' => $payment->id,
                'action' => 'payment_received',
                'old_values' => null,
                'new_values' => $payment->toArray(),
                'actor_id' => $receivedBy ?? auth()->id(),
                'created_at' => now(),
            ]);

            return $payment;
        });
    }

    /**
     * Record a fee adjustment (discount, waiver, write-off).
     */
    public function adjustFee(
        int $schoolId,
        int $studentId,
        ?int $invoiceId,
        string $type,
        float $amount,
        string $reason,
        int $approvedBy
    ): FeeAdjustment {
        return DB::transaction(function () use ($schoolId, $studentId, $invoiceId, $type, $amount, $reason, $approvedBy) {
            Student::forSchool($schoolId)->findOrFail($studentId);

            $invoice = null;
            if ($invoiceId !== null) {
                $invoice = FeeInvoice::forSchool($schoolId)->lockForUpdate()->findOrFail($invoiceId);

                if ((int) $invoice->student_id !== $studentId) {
                    abort(404);
                }

                if (in_array($type, ['discount', 'waiver'], true) && $amount > $invoice->outstandingBalance()) {
                    throw ValidationException::withMessages([
                        'amount' => 'The adjustment cannot exceed the unpaid invoice balance.',
                    ]);
                }
            }

            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'The adjustment amount must be positive.']);
            }

            $adjustment = FeeAdjustment::create([
                'school_id' => $schoolId,
                'student_id' => $studentId,
                'invoice_id' => $invoiceId,
                'type' => $type,
                'amount' => $amount,
                'reason' => $reason,
                'approved_by' => $approvedBy,
            ]);

            if ($invoice && in_array($type, ['discount', 'waiver'], true)) {
                $invoice->discount_total += $amount;
                $invoice->total_amount = max(0, $invoice->subtotal - $invoice->discount_total + $invoice->fine_total);
                $invoice->save();
                $invoice->recalculateStatus();
            }

            FeeAuditLog::create([
                'school_id' => $schoolId,
                'entity_type' => 'FeeAdjustment',
                'entity_id' => $adjustment->id,
                'action' => 'adjustment_approved',
                'old_values' => null,
                'new_values' => $adjustment->toArray(),
                'actor_id' => $approvedBy,
                'created_at' => now(),
            ]);

            return $adjustment;
        });
    }
}
