<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\School;
use App\Models\StaffAttendance;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = session('current_school_id');
        $filters = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $date = $filters['date'] ?? now()->toDateString();
        $employees = Employee::forSchool($schoolId)
            ->where('role', 'teacher')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        $existingRecords = StaffAttendance::forSchool($schoolId)
            ->where('attendance_date', $date)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->keyBy('employee_id');

        return view('school-admin.staff-attendance.index', compact('date', 'employees', 'existingRecords'));
    }

    public function save(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*' => ['required', 'array'],
            'attendance.*.status' => ['required', Rule::in(['present', 'absent', 'late', 'leave', 'excused'])],
            'attendance.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $employeeIds = array_keys($validated['attendance']);
        if (count(array_filter($employeeIds, fn ($id): bool => filter_var($id, FILTER_VALIDATE_INT) === false)) > 0) {
            return back()->withErrors(['attendance' => 'The teacher attendance list contains an invalid teacher.'])->withInput();
        }

        $eligibleCount = Employee::forSchool($schoolId)
            ->where('role', 'teacher')
            ->where('status', 'active')
            ->whereIn('id', $employeeIds)
            ->count();
        if ($eligibleCount !== count($employeeIds)) {
            return back()->withErrors(['attendance' => 'Attendance may only be recorded for active teachers in this school.'])->withInput();
        }

        DB::transaction(function () use ($schoolId, $validated): void {
            $timestamp = now();
            $records = collect($validated['attendance'])
                ->map(fn (array $entry, string $employeeId): array => [
                    'school_id' => $schoolId,
                    'employee_id' => $employeeId,
                    'attendance_date' => $validated['date'],
                    'status' => $entry['status'] === 'leave' ? 'excused' : $entry['status'],
                    'notes' => $entry['notes'] ?? null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])
                ->values()
                ->all();

            StaffAttendance::query()->upsert(
                $records,
                ['school_id', 'employee_id', 'attendance_date'],
                ['status', 'notes', 'updated_at']
            );
        });

        return redirect()->route('school-admin.staff-attendance.index', ['date' => $validated['date']])
            ->with('status', 'Teacher attendance saved successfully.');
    }

    public function register(Request $request): View|StreamedResponse
    {
        $schoolId = session('current_school_id');
        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'export' => ['nullable', Rule::in(['csv'])],
        ]);
        $month = (int) ($filters['month'] ?? now()->month);
        $year = (int) ($filters['year'] ?? now()->year);
        $startDate = CarbonImmutable::create($year, $month, 1)->startOfMonth();
        $dates = collect(range(0, $startDate->daysInMonth - 1))
            ->map(fn (int $offset): CarbonImmutable => $startDate->addDays($offset));
        $employees = Employee::forSchool($schoolId)
            ->where('role', 'teacher')
            ->orderBy('name')
            ->get();
        $records = StaffAttendance::forSchool($schoolId)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('attendance_date', [$startDate->toDateString(), $startDate->endOfMonth()->toDateString()])
            ->get()
            ->groupBy(fn (StaffAttendance $record): int => (int) $record->employee_id);
        $summary = ['teachers' => $employees->count(), 'present' => 0, 'absent' => 0, 'late' => 0, 'leave' => 0, 'recorded' => 0, 'percentage' => 0];

        foreach ($employees as $employee) {
            $employeeRecords = $records->get($employee->id, collect());
            $employee->monthlyAttendance = $employeeRecords->keyBy(fn (StaffAttendance $record): string => $record->attendance_date->toDateString());
            $monthlyTotals = [
                'present' => $employeeRecords->where('status', 'present')->count(),
                'absent' => $employeeRecords->where('status', 'absent')->count(),
                'late' => $employeeRecords->where('status', 'late')->count(),
                'leave' => $employeeRecords->where('status', 'excused')->count(),
                'recorded' => $employeeRecords->count(),
            ];
            $monthlyTotals['percentage'] = $monthlyTotals['recorded'] > 0
                ? round(($monthlyTotals['present'] + $monthlyTotals['late']) / $monthlyTotals['recorded'] * 100, 1)
                : null;
            $employee->monthlyTotals = $monthlyTotals;
        }

        foreach (['present', 'absent', 'late'] as $status) {
            $summary[$status] = $records->flatten()->where('status', $status)->count();
        }
        $summary['leave'] = $records->flatten()->where('status', 'excused')->count();
        $summary['recorded'] = $records->flatten()->count();
        $summary['percentage'] = $summary['recorded'] > 0
            ? round(($summary['present'] + $summary['late']) / $summary['recorded'] * 100, 1)
            : 0;

        if (($filters['export'] ?? null) === 'csv') {
            return $this->exportMonthlyCsv($employees, $dates, $month, $year);
        }

        $school = School::findOrFail($schoolId);

        return view('school-admin.staff-attendance.register', compact('employees', 'dates', 'month', 'year', 'summary', 'school'));
    }

    /**
     * @param  Collection<int, Employee>  $employees
     * @param  Collection<int, CarbonImmutable>  $dates
     */
    private function exportMonthlyCsv(Collection $employees, Collection $dates, int $month, int $year): StreamedResponse
    {
        return response()->streamDownload(function () use ($employees, $dates): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_merge(
                ['Teacher code', 'Teacher name'],
                $dates->map(fn (CarbonImmutable $date): string => $date->format('Y-m-d'))->all(),
                ['Present', 'Absent', 'Late', 'Leave', 'Recorded', 'Attendance %']
            ));

            foreach ($employees as $employee) {
                $totals = $employee->monthlyTotals;
                $row = [$employee->employee_number, $employee->name];
                foreach ($dates as $date) {
                    $status = $employee->monthlyAttendance->get($date->toDateString())?->status;
                    $row[] = match ($status) {
                        'excused' => 'Leave',
                        null => '-',
                        default => ucfirst($status),
                    };
                }
                $row = array_merge($row, array_values($totals));
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, sprintf('teacher-attendance-%04d-%02d.csv', $year, $month), ['Content-Type' => 'text/csv']);
    }
}
