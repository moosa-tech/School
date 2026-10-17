@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Attendance Roll Call</h1>
            <p class="text-slate-500 text-sm">Daily student attendance with session history and audited corrections.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('school-admin.attendance.qr-scanner') }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm">USB QR Scanner</a>
            <a href="{{ route('school-admin.attendance.register') }}" class="rounded-xl bg-white px-4 py-2.5 text-xs font-semibold text-indigo-700 shadow-sm border border-slate-200">Monthly Student Register</a>
            @if($canManageTeacherAttendance)
                <a href="{{ route('school-admin.staff-attendance.index') }}" class="rounded-xl bg-white px-4 py-2.5 text-xs font-semibold text-indigo-700 shadow-sm border border-slate-200">Teacher Attendance</a>
            @endif
            @if($selectedDate && $selectedYearId && $selectedClassId && $selectedSectionId)
                <a href="{{ route('school-admin.attendance.export', ['academic_year_id' => $selectedYearId, 'class_id' => $selectedClassId, 'section_id' => $selectedSectionId, 'date' => $selectedDate]) }}" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition inline-flex items-center space-x-2">
                    <i class="fa-solid fa-file-csv"></i>
                    <span>Export Daily CSV</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Session Selector Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('school-admin.attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Academic Year</label>
                <select name="academic_year_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    @foreach($academicYears as $yr)
                        <option value="{{ $yr->id }}" {{ $selectedYearId == $yr->id ? 'selected' : '' }}>{{ $yr->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Class</label>
                <select name="class_id" id="attendance-class" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Section</label>
                <select name="section_id" id="attendance-section" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    @foreach($classes as $c)
                        @foreach($c->sections as $s)
                            <option value="{{ $s->id }}" data-class-id="{{ $c->id }}" {{ $selectedSectionId == $s->id ? 'selected' : '' }}>{{ $c->name }} - {{ $s->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Attendance Date</label>
                <input type="date" name="date" value="{{ $selectedDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition">
                    Load Roll Call
                </button>
            </div>
        </form>
    </div>

    @if($noAssignments)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900">
            No classes have been assigned to you yet. Please contact your school administrator.
        </div>
    @elseif($students->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4 gap-2">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Enrolled Students ({{ $students->count() }})</h2>
                    <p class="text-xs text-slate-400">Date: {{ $selectedDate }} • Session Status:
                        <span class="font-bold uppercase text-indigo-700">{{ $session?->status ?? 'Not Started' }}</span>
                    </p>
                </div>

                <!-- Quick Action Buttons -->
                <div class="flex items-center space-x-2 text-xs">
                    <button type="button" onclick="setAll('present')" class="px-2.5 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg font-semibold transition">All Present</button>
                    <button type="button" onclick="setAll('absent')" class="px-2.5 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg font-semibold transition">All Absent</button>
                </div>
            </div>

            <form action="{{ route('school-admin.attendance.save') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">
                <input type="hidden" name="date" value="{{ $selectedDate }}">

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Adm #</th>
                                <th class="px-4 py-3">Student Name</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach($students as $student)
                                @php
                                    $rec = $existingRecords->get($student->id);
                                    $currStatus = $rec?->status === 'excused' ? 'leave' : $rec?->status;
                                @endphp
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-mono font-bold text-slate-700">{{ $student->admission_number }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $student->full_name }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="inline-flex rounded-xl bg-slate-100 p-1 space-x-1">
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendance[{{ $student->id }}][status]" value="present" class="peer hidden status-radio" {{ $currStatus === 'present' ? 'checked' : '' }}>
                                                <span class="px-3 py-1 rounded-lg text-xs font-semibold peer-checked:bg-emerald-600 peer-checked:text-white text-slate-600 transition block">Present</span>
                                            </label>
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendance[{{ $student->id }}][status]" value="absent" class="peer hidden status-radio" {{ $currStatus === 'absent' ? 'checked' : '' }}>
                                                <span class="px-3 py-1 rounded-lg text-xs font-semibold peer-checked:bg-rose-600 peer-checked:text-white text-slate-600 transition block">Absent</span>
                                            </label>
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendance[{{ $student->id }}][status]" value="late" class="peer hidden status-radio" {{ $currStatus === 'late' ? 'checked' : '' }}>
                                                <span class="px-3 py-1 rounded-lg text-xs font-semibold peer-checked:bg-amber-500 peer-checked:text-white text-slate-600 transition block">Late</span>
                                            </label>
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendance[{{ $student->id }}][status]" value="leave" class="peer hidden status-radio" {{ $currStatus === 'leave' ? 'checked' : '' }}>
                                                <span class="px-3 py-1 rounded-lg text-xs font-semibold peer-checked:bg-blue-600 peer-checked:text-white text-slate-600 transition block">Leave</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="attendance[{{ $student->id }}][remarks]" value="{{ $rec?->remarks }}" placeholder="Optional note..."
                                            class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-indigo-600">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($session && in_array($session->status, ['finalized', 'corrected'], true))
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs space-y-1">
                        <label class="font-bold text-amber-900 block">Correction Reason (Required because session is already finalized):</label>
                        <input type="text" name="correction_reason" required maxlength="1000" placeholder="e.g. Student arrived late with doctor slip" class="w-full px-3 py-2 bg-white border border-amber-300 rounded-lg text-xs">
                    </div>
                @endif

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <button type="submit" name="action" value="save_draft" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl text-xs transition">
                        Save as Draft
                    </button>
                    <button type="submit" name="action" value="finalize" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl text-xs shadow-md transition flex items-center space-x-2">
                        <i class="fa-solid fa-lock text-xs"></i>
                        <span>Finalize Attendance</span>
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-12 text-center text-slate-400">
            <i class="fa-solid fa-clipboard-question text-4xl mb-3 text-slate-300"></i>
            @if($selectedYearId && $selectedClassId && $selectedSectionId && $selectedDate)
                <p>No active students are enrolled in the selected class, section, and academic year.</p>
                <p class="mt-2 text-sm">Check student enrollments or choose a different academic year, class, or section.</p>
            @elseif($isTeacher)
                <p>No assigned classes are available for the selected academic year.</p>
            @else
                <p>Select academic year, class, and section above to load enrolled students.</p>
            @endif
        </div>
    @endif

</div>

<script>
    const attendanceClass = document.getElementById('attendance-class');
    const attendanceSection = document.getElementById('attendance-section');

    function updateAttendanceSections() {
        if (!attendanceClass || !attendanceSection) {
            return;
        }

        const classId = attendanceClass.value;
        const availableSections = [...attendanceSection.options].filter(option => option.dataset.classId === classId);
        const selectedIsAvailable = availableSections.some(option => option.selected);

        [...attendanceSection.options].forEach(option => {
            option.hidden = option.dataset.classId !== classId;
            option.disabled = option.dataset.classId !== classId;
        });

        if (!selectedIsAvailable && availableSections.length > 0) {
            availableSections[0].selected = true;
        }
    }

    attendanceClass.addEventListener('change', updateAttendanceSections);
    updateAttendanceSections();

    function setAll(status) {
        document.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach(el => el.checked = true);
    }
</script>
@endsection
