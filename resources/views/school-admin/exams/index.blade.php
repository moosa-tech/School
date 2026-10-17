@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Examinations & Assessments</h1>
            <p class="text-slate-500 text-sm">Schedule examination terms, map subjects, record marks, and publish report cards</p>
        </div>
    </div>

    <!-- Exams Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($exams as $exam)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $exam->academicYear->name }}</span>
                        <h2 class="text-lg font-bold text-slate-900 mt-0.5">{{ $exam->name }}</h2>
                    </div>
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full {{ $exam->status === 'published' ? 'bg-emerald-100 text-emerald-800' : ($exam->status === 'locked' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700') }}">
                        {{ ucfirst($exam->status) }}
                    </span>
                </div>

                <div class="text-xs text-slate-500 flex items-center space-x-2">
                    <i class="fa-solid fa-calendar-day text-indigo-600"></i>
                    <span>{{ $exam->starts_on->format('M d') }} - {{ $exam->ends_on->format('M d, Y') }}</span>
                </div>

                <div class="text-xs text-slate-600 border-t border-slate-100 pt-3">
                    <strong>{{ $exam->examSubjects->count() }}</strong> subjects scheduled
                </div>

                <div class="flex items-center space-x-2 pt-2">
                    <a href="{{ route('school-admin.exams.show', $exam) }}" class="flex-1 text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold py-2 rounded-xl text-xs transition">
                        Manage Schedule & Marks
                    </a>
                    <form action="{{ route('school-admin.exams.toggle-publish', $exam) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 py-2 px-3 rounded-xl text-xs font-semibold transition" title="Toggle Publish / Lock">
                            <i class="fa-solid {{ $exam->status === 'published' ? 'fa-lock' : 'fa-globe' }}"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="md:col-span-3 bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400">
                No exams scheduled yet. Create one below.
            </div>
        @endforelse
    </div>

    <!-- Create Exam Form -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <h2 class="text-base font-bold text-slate-900 mb-4">Create New Examination Term</h2>
        <form action="{{ route('school-admin.exams.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Academic Session</label>
                <select name="academic_year_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}">{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Exam Name</label>
                <input type="text" name="name" required placeholder="e.g. Mid Term Exam 2026" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Exam Type</label>
                <select name="exam_type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                    <option value="mid_term">Mid Term</option>
                    <option value="final_term">Final Term</option>
                    <option value="quiz">Monthly Quiz</option>
                    <option value="unit_test">Unit Test</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Start Date</label>
                <input type="date" name="starts_on" required value="{{ now()->toDateString() }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">End Date</label>
                <input type="date" name="ends_on" required value="{{ now()->addDays(7)->toDateString() }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
            </div>

            <div class="sm:col-span-3 flex items-end justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2 rounded-xl text-xs shadow-md transition">
                    Create Examination
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
