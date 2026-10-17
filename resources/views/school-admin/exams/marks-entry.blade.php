@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Marks Entry: {{ $examSubject->classSubject->subject->name }}</h1>
            <p class="text-slate-500 text-sm">
                Exam: <strong>{{ $examSubject->exam->name }}</strong> • Class: <strong>{{ $examSubject->classSubject->class->name }}</strong> • Max Marks: <strong>{{ $examSubject->max_marks }}</strong>
            </p>
        </div>
        <a href="{{ route('school-admin.exams.show', $examSubject->exam) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">Back to Schedule</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-4">
        <form action="{{ route('school-admin.exams.marks.save', $examSubject) }}" method="POST" class="space-y-4">
            @csrf

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Adm #</th>
                            <th class="px-4 py-3">Student Name</th>
                            <th class="px-4 py-3">Evaluation Status</th>
                            <th class="px-4 py-3">Marks Obtained (Max: {{ $examSubject->max_marks }})</th>
                            <th class="px-4 py-3 text-right">Report Card</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($students as $st)
                            @php
                                $m = $existingMarks->get($st->id);
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 font-mono font-bold text-slate-700">{{ $st->admission_number }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $st->full_name }}</td>
                                <td class="px-4 py-3">
                                    <select name="marks[{{ $st->id }}][status]" class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700">
                                        <option value="graded" {{ ($m?->status ?? 'graded') === 'graded' ? 'selected' : '' }}>Graded</option>
                                        <option value="absent" {{ ($m?->status) === 'absent' ? 'selected' : '' }}>Absent</option>
                                        <option value="exempt" {{ ($m?->status) === 'exempt' ? 'selected' : '' }}>Exempt</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.5" min="0" max="{{ $examSubject->max_marks }}" name="marks[{{ $st->id }}][obtained]"
                                        value="{{ $m?->marks_obtained }}" placeholder="0 - {{ $examSubject->max_marks }}"
                                        class="w-32 px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg font-bold text-indigo-700 text-sm focus:ring-2 focus:ring-indigo-600">
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('school-admin.exams.report-card', ['exam' => $examSubject->exam_id, 'student' => $st->id]) }}" target="_blank"
                                        class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg font-semibold text-[11px] transition">
                                        View Card
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No students enrolled in this class.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-4 border-t border-slate-100 text-right">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl text-xs shadow-md transition">
                    Save Examination Marks
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
