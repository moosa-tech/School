@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Homework & Assignments</h1>
            <p class="text-slate-500 text-sm">Classroom assignments, tasks, and submission deadlines</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Homework List -->
        <div class="lg:col-span-2 space-y-3">
            @forelse($homeworkList as $hw)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-full">
                                {{ $hw->classSubject->class->name }} • {{ $hw->classSubject->subject->name }}
                            </span>
                            <h2 class="text-base font-bold text-slate-900 mt-2">{{ $hw->title }}</h2>
                        </div>
                        <div class="text-right text-xs">
                            <span class="text-slate-400">Due Date:</span>
                            <div class="font-bold text-rose-600">{{ $hw->due_on->format('M d, Y') }}</div>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">{{ $hw->description }}</p>

                    <div class="text-[11px] text-slate-400 border-t border-slate-100 pt-2 flex justify-between">
                        <span>Assigned by: {{ $hw->creator?->name ?? 'Faculty' }}</span>
                        <span>Assigned on: {{ $hw->assigned_on->format('Y-m-d') }}</span>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs">
                    No homework assignments created.
                </div>
            @endforelse

            <div class="p-2">
                {{ $homeworkList->links() }}
            </div>
        </div>

        <!-- Add Homework Form -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Assign Homework</h2>

            <form action="{{ route('school-admin.homework.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf

                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Class Subject *</label>
                    <select name="class_subject_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                        @foreach($classSubjects as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->class->name }} - {{ $cs->subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Assignment Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Chapter 4 Exercises 1 to 10" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                </div>

                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Instructions / Description *</label>
                    <textarea name="description" rows="3" required placeholder="Detailed task requirements..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Assigned Date</label>
                        <input type="date" name="assigned_on" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Due Date</label>
                        <input type="date" name="due_on" value="{{ now()->addDays(2)->toDateString() }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-xl text-xs transition">
                    Publish Assignment
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
