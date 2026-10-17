<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Support\AnyFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Report index hub.
     */
    public function index(): View
    {
        return view('school-admin.reports.index');
    }

    /**
     * Student Register Report.
     */
    public function studentRegister(Request $request): View|StreamedResponse
    {
        $schoolId = session('current_school_id');
        $query = Student::forSchool($schoolId)->with(['currentEnrollment.class', 'currentEnrollment.section', 'guardians']);

        AnyFilter::apply($query, [
            $request->filled('class_id') ? fn (Builder $filter) => $filter->whereHas('currentEnrollment', fn (Builder $enrollment) => $enrollment->where('class_id', $request->class_id)) : null,
            $request->filled('status') ? fn (Builder $filter) => $filter->where('status', $request->status) : null,
        ]);

        if ($request->has('export')) {
            return $this->exportCsv('student-register.csv', ['Admission No', 'Name', 'Gender', 'Class', 'Section', 'Guardian', 'Phone', 'Status'], $query->get()->map(function ($s) {
                return [
                    $s->admission_number,
                    $s->full_name,
                    ucfirst($s->gender),
                    $s->currentEnrollment?->class?->name ?? 'N/A',
                    $s->currentEnrollment?->section?->name ?? 'N/A',
                    $s->primaryGuardian()?->name ?? 'N/A',
                    $s->emergency_phone ?? $s->primaryGuardian()?->phone ?? 'N/A',
                    ucfirst($s->status),
                ];
            })->toArray());
        }

        $students = $query->paginate(30)->withQueryString();
        $classes = SchoolClass::forSchool($schoolId)->get();

        return view('school-admin.reports.student-register', compact('students', 'classes'));
    }

    /**
     * Fee Collection Report.
     */
    public function feeCollection(Request $request): View|StreamedResponse
    {
        $schoolId = session('current_school_id');
        $query = FeePayment::forSchool($schoolId)->with(['student', 'receiver'])->where('status', 'completed');

        $hasDateFilter = $request->filled('date_from') || $request->filled('date_to');

        AnyFilter::apply($query, [
            $hasDateFilter ? function (Builder $filter) use ($request): void {
                if ($request->filled('date_from')) {
                    $filter->whereDate('payment_date', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $filter->whereDate('payment_date', '<=', $request->date_to);
                }
            } : null,
            $request->filled('method') ? fn (Builder $filter) => $filter->where('method', $request->method) : null,
        ]);

        $totalCollected = (clone $query)->sum('amount');

        if ($request->has('export')) {
            return $this->exportCsv('fee-collection.csv', ['Receipt No', 'Date', 'Student', 'Amount', 'Method', 'Reference'], $query->get()->map(function ($p) {
                return [
                    $p->receipt_number,
                    $p->payment_date->format('Y-m-d'),
                    $p->student->full_name,
                    $p->amount,
                    strtoupper($p->method),
                    $p->reference ?? 'N/A',
                ];
            })->toArray());
        }

        $payments = $query->latest('payment_date')->paginate(30)->withQueryString();

        return view('school-admin.reports.fee-collection', compact('payments', 'totalCollected'));
    }

    /**
     * Outstanding Dues Report.
     */
    public function outstandingDues(Request $request): View|StreamedResponse
    {
        $schoolId = session('current_school_id');
        $query = FeeInvoice::forSchool($schoolId)
            ->with(['student.currentEnrollment.class', 'student.guardians'])
            ->whereIn('status', ['pending', 'partial', 'overdue']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $totalOutstanding = (clone $query)->get()->sum(fn ($i) => $i->outstandingBalance());

        if ($request->has('export')) {
            return $this->exportCsv('outstanding-dues.csv', ['Invoice No', 'Student', 'Class', 'Due Date', 'Total', 'Paid', 'Outstanding', 'Status'], $query->get()->map(function ($i) {
                return [
                    $i->invoice_number,
                    $i->student->full_name,
                    $i->student->currentEnrollment?->class?->name ?? 'N/A',
                    $i->due_date->format('Y-m-d'),
                    $i->total_amount,
                    $i->paid_amount,
                    $i->outstandingBalance(),
                    ucfirst($i->status),
                ];
            })->toArray());
        }

        $invoices = $query->latest('due_date')->paginate(30)->withQueryString();

        return view('school-admin.reports.outstanding-dues', compact('invoices', 'totalOutstanding'));
    }

    /**
     * Stream CSV download helper.
     */
    protected function exportCsv(string $filename, array $headings, array $rows): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($headings, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headings);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
