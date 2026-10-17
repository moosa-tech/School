@extends('layouts.app')

@section('content')
<style>
    @media print {
        .staff-register-filters, .staff-register-actions, #app-sidebar, nav { display: none !important; }
        main { width: 100% !important; }
        .staff-register-table { font-size: 8px !important; }
        .staff-register-table th, .staff-register-table td { padding: 3px !important; }
    }
</style>

<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Teacher Monthly Attendance Register</h1>
            <p class="text-sm text-slate-500">Unrecorded dates are excluded from attendance percentages.</p>
        </div>
        <a href="{{ route('school-admin.staff-attendance.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700">Daily Teacher Attendance</a>
    </div>

    <form method="GET" action="{{ route('school-admin.staff-attendance.register') }}" class="staff-register-filters flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        <label class="text-xs font-semibold text-slate-600">Month
            <select name="month" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 sm:w-48">
                @foreach(range(1, 12) as $monthNumber)
                    <option value="{{ $monthNumber }}" @selected($month === $monthNumber)>{{ \Carbon\Carbon::create()->month($monthNumber)->format('F') }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-xs font-semibold text-slate-600">Year
            <input type="number" name="year" min="2000" max="2100" value="{{ $year }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 sm:w-32">
        </label>
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Load Register</button>
    </form>

    <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="staff-register-actions flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $school->name }}</p>
                <h2 class="text-lg font-bold text-slate-900">{{ $dates->first()->format('F Y') }}</h2>
                <p class="text-sm text-slate-500">{{ $summary['teachers'] }} teachers · {{ $summary['recorded'] }} attendance entries</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school-admin.staff-attendance.register', ['month' => $month, 'year' => $year, 'export' => 'csv']) }}" class="rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white">Export CSV</a>
                <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Print / Save PDF</button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl bg-slate-50 p-3"><div class="text-xs text-slate-500">Teachers</div><div class="text-xl font-bold">{{ $summary['teachers'] }}</div></div>
            <div class="rounded-xl bg-emerald-50 p-3"><div class="text-xs text-emerald-700">Present</div><div class="text-xl font-bold text-emerald-800">{{ $summary['present'] }}</div></div>
            <div class="rounded-xl bg-rose-50 p-3"><div class="text-xs text-rose-700">Absent</div><div class="text-xl font-bold text-rose-800">{{ $summary['absent'] }}</div></div>
            <div class="rounded-xl bg-amber-50 p-3"><div class="text-xs text-amber-700">Late</div><div class="text-xl font-bold text-amber-800">{{ $summary['late'] }}</div></div>
            <div class="rounded-xl bg-blue-50 p-3"><div class="text-xs text-blue-700">Leave</div><div class="text-xl font-bold text-blue-800">{{ $summary['leave'] }}</div></div>
            <div class="rounded-xl bg-indigo-50 p-3"><div class="text-xs text-indigo-700">Recorded attendance</div><div class="text-xl font-bold text-indigo-800">{{ $summary['percentage'] }}%</div></div>
        </div>
        <p class="text-xs text-slate-500">Attendance percentage = (Present + Late) ÷ recorded entries. Half-day is not part of the existing attendance status schema.</p>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="staff-register-table min-w-max border-collapse text-left text-xs text-slate-700">
                <thead class="bg-slate-100 uppercase text-slate-600">
                    <tr>
                        <th class="sticky left-0 z-10 min-w-28 bg-slate-100 px-2 py-2">Teacher code</th>
                        <th class="sticky left-28 z-10 min-w-40 bg-slate-100 px-2 py-2">Teacher name</th>
                        @foreach($dates as $date)
                            <th class="min-w-8 px-1 py-2 text-center {{ $date->isWeekend() ? 'bg-slate-200' : '' }}" title="{{ $date->format('l, F j, Y') }}">{{ $date->format('j') }}</th>
                        @endforeach
                        <th class="px-2 py-2">P</th><th class="px-2 py-2">A</th><th class="px-2 py-2">Late</th><th class="px-2 py-2">Leave</th><th class="px-2 py-2">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $employee)
                        <tr class="odd:bg-white even:bg-slate-50">
                            <td class="sticky left-0 bg-inherit px-2 py-2 font-mono">{{ $employee->employee_number }}</td>
                            <td class="sticky left-28 bg-inherit px-2 py-2 font-semibold">{{ $employee->name }}</td>
                            @foreach($dates as $date)
                                @php($status = $employee->monthlyAttendance->get($date->toDateString())?->status)
                                <td class="px-1 py-2 text-center {{ $date->isWeekend() ? 'bg-slate-100' : '' }}" title="{{ $date->format('l, F j') }}: {{ $status === 'excused' ? 'Leave' : ($status ? ucfirst($status) : 'Not recorded') }}">
                                    @switch($status)
                                        @case('present')<span class="font-bold text-emerald-700">P</span>@break
                                        @case('absent')<span class="font-bold text-rose-700">A</span>@break
                                        @case('late')<span class="font-bold text-amber-700">L</span>@break
                                        @case('excused')<span class="font-bold text-blue-700">Lv</span>@break
                                        @default<span class="text-slate-300">—</span>
                                    @endswitch
                                </td>
                            @endforeach
                            <td class="px-2 py-2 text-emerald-700">{{ $employee->monthlyTotals['present'] }}</td>
                            <td class="px-2 py-2 text-rose-700">{{ $employee->monthlyTotals['absent'] }}</td>
                            <td class="px-2 py-2 text-amber-700">{{ $employee->monthlyTotals['late'] }}</td>
                            <td class="px-2 py-2 text-blue-700">{{ $employee->monthlyTotals['leave'] }}</td>
                            <td class="px-2 py-2 font-semibold">{{ is_null($employee->monthlyTotals['percentage']) ? '—' : $employee->monthlyTotals['percentage'].'%' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $dates->count() + 7 }}" class="px-4 py-8 text-center text-slate-500">No teacher employees are configured for this school.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
