@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Subjects & Teacher Assignments</h1>
            <p class="text-slate-500 text-sm">School subject catalogue and mapping of subjects to classes with instructors</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Subject Offerings & Assigned Teachers -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Class Subject Offerings</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 uppercase font-semibold text-slate-500">
                        <tr>
                            <th class="p-3">Class</th>
                            <th class="p-3">Subject</th>
                            <th class="p-3">Code</th>
                            <th class="p-3">Assigned Faculty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($classSubjects as $cs)
                            <tr>
                                <td class="p-3 font-semibold text-slate-900">{{ $cs->class->name }}</td>
                                <td class="p-3 font-bold text-indigo-700">{{ $cs->subject->name }}</td>
                                <td class="p-3 font-mono text-slate-400">{{ $cs->subject->code ?? '-' }}</td>
                                <td class="p-3">
                                    <form action="{{ route('school-admin.subjects.teacher.update', $cs->id) }}" method="POST" class="flex min-w-56 flex-col gap-2 sm:flex-row">
                                        @csrf
                                        <select name="employee_id" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs">
                                            <option value="">Unassigned</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" @selected($cs->teacherAssignments->first()?->employee_id === $teacher->id || (! $cs->teacherAssignments->isNotEmpty() && $cs->teacher_id === $teacher->user_id))>{{ $teacher->name }} ({{ $teacher->employee_number }})</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-4 text-center text-slate-400">No subjects assigned to classes yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Form: Assign Subject to Class -->
            <form action="{{ route('school-admin.subjects.assign') }}" method="POST" class="pt-4 border-t border-slate-100 space-y-3">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $currentYear?->id }}">
                <h3 class="text-xs font-bold text-slate-700 uppercase">Offer Subject to Class</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Class</label>
                        <select name="class_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Subject</label>
                        <select name="subject_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Teacher</label>
                        <select name="employee_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                            <option value="">No Instructor (Assign later)</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->employee_number }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="text-right pt-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-xl text-xs transition">
                        Assign to Class
                    </button>
                </div>
            </form>
        </div>

        <!-- Catalog Form & List -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Subject Catalogue</h2>

            <div class="space-y-1.5 max-h-64 overflow-y-auto pr-1">
                @foreach($subjects as $sub)
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs flex justify-between items-center">
                        <span class="font-bold text-slate-800">{{ $sub->name }}</span>
                        <span class="font-mono text-[10px] text-slate-400 bg-slate-200 px-1.5 py-0.5 rounded">{{ $sub->code ?? 'N/A' }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Add Subject Form -->
            <form action="{{ route('school-admin.subjects.store') }}" method="POST" class="pt-3 border-t border-slate-100 space-y-2">
                @csrf
                <label class="block text-xs font-semibold text-slate-700 uppercase">New Subject</label>
                <input type="text" name="name" required placeholder="Subject Name (e.g. Physics)" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <input type="text" name="code" placeholder="Course Code (e.g. PHY101)" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-semibold py-2 rounded-xl text-xs transition">
                    Add to Catalogue
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
