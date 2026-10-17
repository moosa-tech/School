@extends('layouts.app')

@section('content')
<style>
    @media print {
        .attendance-register-filters, .attendance-register-actions, #app-sidebar, nav { display: none !important; }
        main { width: 100% !important; }
        .attendance-register-table { font-size: 8px !important; }
        .attendance-register-table th, .attendance-register-table td { padding: 3px !important; }
    }
</style>

<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Student Monthly Attendance Register</h1>
            <p class="text-sm text-slate-500">Unrecorded dates are shown as — and excluded from attendance percentages.</p>
        </div>
        <a href="{{ route('school-admin.attendance.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700">Daily Roll Call</a>
    </div>

    @if($noAssignments)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">No classes have been assigned to you yet. Please contact your school administrator.</div>
    @else
        <form method="GET" action="{{ route('school-admin.attendance.register') }}" class="attendance-register-filters grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-6">
            <label class="text-xs font-semibold text-slate-600">
                Academic Year
                <select id="student-register-year" name="academic_year_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    @foreach($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}" @selected($selectedYearId === $academicYear->id)>{{ $academicYear->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-semibold text-slate-600">
                Class
                <select id="student-register-class" name="class_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    <option value="">Select class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected($selectedClassId === $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-semibold text-slate-600">
                Section
                <select id="student-register-section" name="section_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    <option value="">All sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" data-class-id="{{ $section->class_id }}" @selected($selectedSectionId === $section->id)>{{ $section->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-semibold text-slate-600">
                Month
                <select name="month" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    @foreach(range(1, 12) as $monthNumber)
                        <option value="{{ $monthNumber }}" @selected($month === $monthNumber)>{{ \Carbon\Carbon::create()->month($monthNumber)->format('F') }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-semibold text-slate-600">
                Year
                <input type="number" name="year" min="2000" max="2100" value="{{ $year }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
            </label>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Load Register</button>
            </div>
        </form>

        @if($selectedClassId)
            <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <div class="attendance-register-actions flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $school->name }}</p>
                        <h2 class="text-lg font-bold text-slate-900">{{ $classes->firstWhere('id', $selectedClassId)?->name }}{{ $selectedSectionId ? ' · '.$sections->firstWhere('id', $selectedSectionId)?->name : '' }}</h2>
                        <p class="text-sm text-slate-500">{{ $dates->first()->format('F Y') }} · {{ $summary['students'] }} students · {{ $summary['recorded'] }} attendance entries</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('school-admin.attendance.register', ['academic_year_id' => $selectedYearId, 'class_id' => $selectedClassId, 'section_id' => $selectedSectionId ?: null, 'month' => $month, 'year' => $year, 'export' => 'csv']) }}" class="rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white">Export CSV</a>
                        <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Print / Save PDF</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <div class="rounded-xl bg-slate-50 p-3"><div class="text-xs text-slate-500">Students</div><div class="text-xl font-bold">{{ $summary['students'] }}</div></div>
                    <div class="rounded-xl bg-emerald-50 p-3"><div class="text-xs text-emerald-700">Present</div><div class="text-xl font-bold text-emerald-800">{{ $summary['present'] }}</div></div>
                    <div class="rounded-xl bg-rose-50 p-3"><div class="text-xs text-rose-700">Absent</div><div class="text-xl font-bold text-rose-800">{{ $summary['absent'] }}</div></div>
                    <div class="rounded-xl bg-amber-50 p-3"><div class="text-xs text-amber-700">Late</div><div class="text-xl font-bold text-amber-800">{{ $summary['late'] }}</div></div>
                    <div class="rounded-xl bg-blue-50 p-3"><div class="text-xs text-blue-700">Leave</div><div class="text-xl font-bold text-blue-800">{{ $summary['leave'] }}</div></div>
                    <div class="rounded-xl bg-indigo-50 p-3"><div class="text-xs text-indigo-700">Recorded attendance</div><div class="text-xl font-bold text-indigo-800">{{ $summary['percentage'] }}%</div></div>
                </div>
                <p class="text-xs text-slate-500">Attendance percentage = (Present + Late) ÷ recorded entries. Missing records are not counted as Present.</p>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="attendance-register-table min-w-max border-collapse text-left text-xs text-slate-700">
                        <thead class="bg-slate-100 uppercase text-slate-600">
                            <tr>
                                <th class="sticky left-0 z-10 min-w-12 bg-slate-100 px-2 py-2">#</th>
                                <th class="sticky left-12 z-10 min-w-28 bg-slate-100 px-2 py-2">Admission No.</th>
                                <th class="sticky left-40 z-10 min-w-40 bg-slate-100 px-2 py-2">Student</th>
                                @foreach($dates as $date)
                                    <th class="min-w-8 px-1 py-2 text-center {{ $date->isWeekend() ? 'bg-slate-200' : '' }}" title="{{ $date->format('l, F j, Y') }}">{{ $date->format('j') }}</th>
                                @endforeach
                                <th class="px-2 py-2">P</th><th class="px-2 py-2">A</th><th class="px-2 py-2">L</th><th class="px-2 py-2">Leave</th><th class="px-2 py-2">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($students as $index => $student)
                                <tr class="odd:bg-white even:bg-slate-50">
                                    <td class="sticky left-0 bg-inherit px-2 py-2">{{ $index + 1 }}</td>
                                    <td class="sticky left-12 bg-inherit px-2 py-2 font-mono">{{ $student->admission_number }}</td>
                                    <td class="sticky left-40 bg-inherit px-2 py-2 font-semibold">{{ $student->full_name }}</td>
                                    @foreach($dates as $date)
                                        @php($status = $student->monthlyAttendance->get($date->toDateString())?->status)
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
                                    <td class="px-2 py-2 font-semibold text-emerald-700">{{ $student->monthlyTotals['present'] }}</td>
                                    <td class="px-2 py-2 font-semibold text-rose-700">{{ $student->monthlyTotals['absent'] }}</td>
                                    <td class="px-2 py-2 font-semibold text-amber-700">{{ $student->monthlyTotals['late'] }}</td>
                                    <td class="px-2 py-2 font-semibold text-blue-700">{{ $student->monthlyTotals['leave'] }}</td>
                                    <td class="px-2 py-2 font-semibold">{{ is_null($student->monthlyTotals['percentage']) ? '—' : $student->monthlyTotals['percentage'].'%' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $dates->count() + 8 }}" class="px-4 py-8 text-center text-slate-500">No active students are enrolled for this class and academic year.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    @endif
</div>
<script>
    const registerClass = document.getElementById('student-register-class');
    const registerSection = document.getElementById('student-register-section');
    const registerYear = document.getElementById('student-register-year');

    function filterRegisterSections() {
        if (!registerClass || !registerSection) {
            return;
        }

        const selectedClass = registerClass.value;
        [...registerSection.options].forEach(option => {
            if (option.value) {
                option.hidden = option.dataset.classId !== selectedClass;
                option.disabled = option.dataset.classId !== selectedClass;
            }
        });
        if (registerSection.selectedOptions[0]?.disabled) {
            registerSection.value = '';
        }
    }

    registerClass?.addEventListener('change', filterRegisterSections);
    registerYear?.addEventListener('change', () => {
        if (registerClass) {
            registerClass.value = '';
        }
        if (registerSection) {
            registerSection.value = '';
        }
    });
    filterRegisterSections();
</script>
@endsection
