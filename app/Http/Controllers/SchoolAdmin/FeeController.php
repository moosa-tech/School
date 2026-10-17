<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\FeeInvoiceService;
use App\Support\AnyFilter;
use App\Support\SchoolAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeController extends Controller
{
    public function __construct(
        protected FeeInvoiceService $feeInvoiceService
    ) {}

    /**
     * Invoices listing with search & filters.
     */
    public function invoices(Request $request): View
    {
        $schoolId = session('current_school_id');
        $query = FeeInvoice::forSchool($schoolId)->with(['student', 'academicYear', 'items.category', 'allocations.payment']);

        $search = $request->filled('search') ? '%'.trim($request->search).'%' : null;
        $billingPeriod = $request->filled('billing_period') ? '%'.trim($request->billing_period).'%' : null;

        AnyFilter::apply($query, [
            $search ? fn (Builder $filter) => $filter->where(function (Builder $fields) use ($search): void {
                $fields->where('invoice_number', 'like', $search)
                    ->orWhere('billing_period', 'like', $search)
                    ->orWhereHas('student', fn (Builder $student) => $student->where('first_name', 'like', $search)->orWhere('admission_number', 'like', $search));
            }) : null,
            $request->filled('status') ? fn (Builder $filter) => $filter->where('status', $request->status) : null,
            $billingPeriod ? fn (Builder $filter) => $filter->where('billing_period', 'like', $billingPeriod) : null,
        ]);

        $invoices = $query->latest()->paginate(25)->withQueryString();
        $classes = SchoolClass::forSchool($schoolId)->get();
        $academicYears = AcademicYear::forSchool($schoolId)->get();

        return view('school-admin.fees.invoices', compact('invoices', 'classes', 'academicYears'));
    }

    /**
     * Show Invoice Details & printable voucher.
     */
    public function showInvoice(FeeInvoice $invoice): View
    {
        $schoolId = session('current_school_id');
        if ($invoice->school_id !== $schoolId) {
            abort(403);
        }

        $invoice->load(['student.guardians', 'academicYear', 'items.category', 'allocations.payment.receiver', 'adjustments.approver']);

        return view('school-admin.fees.invoice-detail', compact('invoice'));
    }

    /**
     * Show form to generate invoice.
     */
    public function createInvoice(): View
    {
        $schoolId = session('current_school_id');
        $students = Student::forSchool($schoolId)->where('status', 'active')->orderBy('first_name')->get();
        $classes = SchoolClass::forSchool($schoolId)->get();
        $categories = FeeCategory::forSchool($schoolId)->where('is_active', true)->get();
        $currentYear = AcademicYear::forSchool($schoolId)->where('is_current', true)->first();

        return view('school-admin.fees.create-invoice', compact('students', 'classes', 'categories', 'currentYear'));
    }

    /**
     * Store single invoice.
     */
    public function storeInvoice(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'student_id' => ['required', SchoolAccess::existsInSchool('students', $schoolId)],
            'academic_year_id' => ['required', SchoolAccess::existsInSchool('academic_years', $schoolId)],
            'billing_period' => ['required', 'string', 'max:50'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.category_id' => ['nullable', SchoolAccess::existsInSchool('fee_categories', $schoolId)],
            'items.*.description' => ['required', 'string'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'fine_total' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $invoice = $this->feeInvoiceService->generateInvoice(
                $schoolId,
                $validated['student_id'],
                $validated['academic_year_id'],
                $validated['billing_period'],
                $validated['issue_date'],
                $validated['due_date'],
                $validated['items'],
                (float) ($validated['discount_total'] ?? 0),
                (float) ($validated['fine_total'] ?? 0)
            );

            return redirect()->route('school-admin.fees.invoices.show', $invoice)
                ->with('status', "Invoice {$invoice->invoice_number} generated successfully.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['billing_period' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Batch invoice generation for an entire class/section.
     */
    public function generateBatchInvoices(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'class_id' => ['required', SchoolAccess::existsInSchool('classes', $schoolId)],
            'academic_year_id' => ['required', SchoolAccess::existsInSchool('academic_years', $schoolId)],
            'billing_period' => ['required', 'string', 'max:50'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
        ]);

        // Find enrolled students
        $enrollments = StudentEnrollment::forSchool($schoolId)
            ->where('class_id', $validated['class_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('status', 'enrolled')
            ->get();

        // Find standard fee structures for this class
        $structures = FeeStructure::forSchool($schoolId)
            ->where('class_id', $validated['class_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('is_active', true)
            ->with('category')
            ->get();

        if ($structures->isEmpty()) {
            return back()->withErrors(['class_id' => 'No active fee structures found for this class and year.']);
        }

        $items = [];
        foreach ($structures as $st) {
            $items[] = [
                'category_id' => $st->fee_category_id,
                'description' => $st->category->name ?? 'Tuition Fee',
                'amount' => $st->amount,
            ];
        }

        $createdCount = 0;
        foreach ($enrollments as $enrollment) {
            try {
                $this->feeInvoiceService->generateInvoice(
                    $schoolId,
                    $enrollment->student_id,
                    $validated['academic_year_id'],
                    $validated['billing_period'],
                    $validated['issue_date'],
                    $validated['due_date'],
                    $items
                );
                $createdCount++;
            } catch (\RuntimeException) {
                // Ignore duplicates
            }
        }

        return redirect()->route('school-admin.fees.invoices')
            ->with('status', "Generated {$createdCount} invoices for {$validated['billing_period']}.");
    }

    /**
     * Record Fee Payment and allocate to invoice.
     */
    public function recordPayment(Request $request, FeeInvoice $invoice): RedirectResponse
    {
        $schoolId = session('current_school_id');
        if ($invoice->school_id !== $schoolId) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date'],
            'method' => ['required', 'in:cash,bank_transfer,card,cheque,online'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = $this->feeInvoiceService->receivePayment(
            $schoolId,
            $invoice->student_id,
            $invoice->id,
            (float) $validated['amount'],
            $validated['payment_date'],
            $validated['method'],
            $validated['reference'] ?? null,
            $validated['notes'] ?? null,
            auth()->id()
        );

        return back()->with('status', "Payment of {$payment->amount} recorded. Receipt #: {$payment->receipt_number}");
    }

    /**
     * Payments List / Receipts.
     */
    public function payments(Request $request): View
    {
        $schoolId = session('current_school_id');
        $query = FeePayment::forSchool($schoolId)->with(['student', 'receiver', 'allocations.invoice']);

        $search = $request->filled('search') ? '%'.trim($request->search).'%' : null;

        AnyFilter::apply($query, [
            $search ? fn (Builder $filter) => $filter->where(function (Builder $fields) use ($search): void {
                $fields->where('receipt_number', 'like', $search)
                    ->orWhere('reference', 'like', $search)
                    ->orWhereHas('student', fn (Builder $student) => $student->where('first_name', 'like', $search)->orWhere('admission_number', 'like', $search));
            }) : null,
            $request->filled('method') ? fn (Builder $filter) => $filter->where('method', $request->method) : null,
        ]);

        $payments = $query->latest('payment_date')->paginate(25)->withQueryString();

        return view('school-admin.fees.payments', compact('payments'));
    }

    /**
     * Printable Fee Receipt.
     */
    public function showReceipt(FeePayment $payment): View
    {
        $schoolId = session('current_school_id');
        if ($payment->school_id !== $schoolId) {
            abort(403);
        }

        $payment->load(['student.guardians', 'allocations.invoice', 'receiver']);

        return view('school-admin.fees.receipt', compact('payment'));
    }

    /**
     * Categories & Fee Structures.
     */
    public function structures(): View
    {
        $schoolId = session('current_school_id');
        $categories = FeeCategory::forSchool($schoolId)->get();
        $classes = SchoolClass::forSchool($schoolId)->get();
        $currentYear = AcademicYear::forSchool($schoolId)->where('is_current', true)->first();
        $structures = FeeStructure::forSchool($schoolId)->with(['class', 'category', 'academicYear'])->get();

        return view('school-admin.fees.structures', compact('categories', 'classes', 'currentYear', 'structures'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        FeeCategory::create([
            'school_id' => $schoolId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('status', "Fee Category '{$validated['name']}' created.");
    }

    public function storeStructure(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');
        $validated = $request->validate([
            'academic_year_id' => ['required', SchoolAccess::existsInSchool('academic_years', $schoolId)],
            'class_id' => ['required', SchoolAccess::existsInSchool('classes', $schoolId)],
            'fee_category_id' => ['required', SchoolAccess::existsInSchool('fee_categories', $schoolId)],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:monthly,annual,one_time,term'],
            'due_day' => ['required', 'integer', 'between:1,28'],
        ]);

        FeeStructure::create([
            'school_id' => $schoolId,
            'academic_year_id' => $validated['academic_year_id'],
            'class_id' => $validated['class_id'],
            'fee_category_id' => $validated['fee_category_id'],
            'amount' => $validated['amount'],
            'frequency' => $validated['frequency'],
            'due_day' => $validated['due_day'],
            'is_active' => true,
        ]);

        return back()->with('status', 'Fee structure configured successfully.');
    }

    /**
     * Record Fee Adjustment (discount / waiver / write-off).
     */
    public function storeAdjustment(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');
        $validated = $request->validate([
            'student_id' => ['required', SchoolAccess::existsInSchool('students', $schoolId)],
            'invoice_id' => ['nullable', SchoolAccess::existsInSchool('fee_invoices', $schoolId)],
            'type' => ['required', 'in:discount,waiver,correction,write_off'],
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string'],
        ]);

        $this->feeInvoiceService->adjustFee(
            $schoolId,
            $validated['student_id'],
            $validated['invoice_id'] ?? null,
            $validated['type'],
            (float) $validated['amount'],
            $validated['reason'],
            auth()->id()
        );

        return back()->with('status', 'Fee adjustment approved and recorded.');
    }
}
