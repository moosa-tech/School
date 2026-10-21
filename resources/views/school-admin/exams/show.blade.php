@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-slate-900">{{ $exam->name }}</h1>
                <span class="px-2.5 py-1 text-xs font-bold uppercase rounded-full {{ $exam->status === 'published' ? 'bg-emerald-100 text-emerald-800' : ($exam->status === 'locked' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700') }}">
                    {{ $exam->status }}
                </span>
            </div>
            <p class="text-slate-500 text-sm mt-0.5">Session: {{ $exam->academicYear->name }} • Dates: {{ $exam->starts_on->format('M d') }} to {{ $exam->ends_on->format('M d, Y') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <form action="{{ route('school-admin.exams.toggle-publish', $exam) }}" method="POST">
                @csrf
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-sm transition">
                    {{ $exam->status === 'draft' ? 'Publish Results' : ($exam->status === 'published' ? 'Lock Results' : 'Unlock to Draft') }}
                </button>
            </form>
            <a href="{{ route('school-admin.exams.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700 px-2">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Scheduled Exam Subjects List -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Exam Subject Schedule</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 uppercase font-semibold text-slate-500">
                        <tr>
                            <th class="p-3">Class</th>
                            <th class="p-3">Subject</th>
                            <th class="p-3">Exam Date</th>
                            <th class="p-3">Max / Pass Marks</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($exam->examSubjects as $es)
                            <tr>
                                <td class="p-3 font-semibold text-slate-900">{{ $es->classSubject->class->name }}</td>
                                <td class="p-3 font-bold text-indigo-700">{{ $es->classSubject->subject->name }}</td>
                                <td class="p-3 text-slate-500">{{ $es->exam_date?->format('Y-m-d') ?? 'TBD' }}</td>
                                <td class="p-3 font-mono font-semibold">{{ $es->max_marks }} / {{ $es->passing_marks }}</td>
                                <td class="p-3 text-right">
                                    <div class="inline-flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('school-admin.exams.marks.entry', $es) }}" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold px-3 py-1.5 rounded-lg transition inline-flex items-center space-x-1">
                                        <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                        <span>Enter Marks</span>
                                    </a>
                                    <a href="{{ route('school-admin.exams.marks.import', $es) }}" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-3 py-1.5 rounded-lg transition inline-flex items-center space-x-1">
                                        <span>Import Marks</span>
                                    </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-center text-slate-400">No subjects scheduled for this exam yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Subject Form -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Schedule Subject</h2>

            <form action="{{ route('school-admin.exams.subjects.add', $exam) }}" method="POST" class="space-y-3">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Class Subject *</label>
                    <select name="class_subject_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                        @foreach($classSubjects as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->class->name }} - {{ $cs->subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase mb-1">Max Marks *</label>
                        <input type="number" step="0.5" name="max_marks" value="100" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase mb-1">Passing Marks *</label>
                        <input type="number" step="0.5" name="passing_marks" value="40" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Paper Date</label>
                    <input type="date" name="exam_date" value="{{ $exam->starts_on->toDateString() }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-xl text-xs transition">
                    Add to Exam Schedule
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
