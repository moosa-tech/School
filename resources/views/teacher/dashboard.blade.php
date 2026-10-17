@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="mb-2">
        <h1 class="text-2xl font-bold text-slate-900">Teacher Dashboard</h1>
    </div>

    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Welcome, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-emerald-100 mt-1">Teacher Portal &middot; {{ now()->format('l, d M Y') }}</p>
                @if($employee)
                    <p class="text-emerald-200 text-sm mt-0.5">
                        {{ $employee->job_title ?? 'Teacher' }}
                        @if($currentYear)
                            &middot; {{ $currentYear->name }}
                        @endif
                    </p>
                @endif
            </div>
            <div class="hidden md:block">
                <i class="fa-solid fa-chalkboard-teacher text-5xl text-emerald-300 opacity-70"></i>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 text-center">
            <div class="text-2xl font-bold text-indigo-600">
                {{ $assignments->pluck('classSubject.class_id')->unique()->count() }}
            </div>
            <div class="text-xs text-slate-500 mt-1">My Classes</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 text-center">
            <div class="text-2xl font-bold text-emerald-600">
                {{ $assignments->count() }}
            </div>
            <div class="text-xs text-slate-500 mt-1">Subjects Assigned</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 text-center">
            <div class="text-2xl font-bold text-amber-600">{{ $recentHomework->count() }}</div>
            <div class="text-xs text-slate-500 mt-1">Homework Assigned</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 text-center">
            <div class="text-2xl font-bold text-rose-600">
                {{ $currentYear ? $currentYear->name : '—' }}
            </div>
            <div class="text-xs text-slate-500 mt-1">Academic Year</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- My Assigned Classes/Subjects --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-800 flex items-center">
                    <i class="fa-solid fa-chalkboard mr-2 text-indigo-500"></i> My Classes &amp; Subjects
                </h2>
                <a href="{{ route('school-admin.exams.index') }}" class="text-xs text-indigo-600 hover:underline">Exams</a>
            </div>
            <div class="p-5">
                @forelse($assignments as $assignment)
                    <div class="flex items-center space-x-3 py-2 border-b border-slate-50 last:border-0">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-book-open text-indigo-600 text-xs"></i>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-slate-800">
                                {{ $assignment->classSubject->subject->name ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $assignment->classSubject->class->name ?? '' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400">
                        <i class="fa-solid fa-chalkboard text-3xl mb-2 block"></i>
                        <p class="text-sm">No class assignments yet</p>
                        <p class="text-xs mt-1">Contact your administrator to get assigned to classes.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800 flex items-center">
                    <i class="fa-solid fa-bolt mr-2 text-amber-500"></i> Quick Actions
                </h2>
            </div>
            <div class="p-5 grid grid-cols-2 gap-3">
                <a href="{{ route('school-admin.attendance.index') }}"
                   class="flex flex-col items-center p-4 bg-emerald-50 hover:bg-emerald-100 rounded-xl border border-emerald-200 transition text-center">
                    <i class="fa-solid fa-clipboard-user text-2xl text-emerald-600 mb-2"></i>
                    <span class="text-sm font-medium text-emerald-800">Take Attendance</span>
                </a>
                <a href="{{ route('school-admin.exams.index') }}"
                   class="flex flex-col items-center p-4 bg-indigo-50 hover:bg-indigo-100 rounded-xl border border-indigo-200 transition text-center">
                    <i class="fa-solid fa-file-pen text-2xl text-indigo-600 mb-2"></i>
                    <span class="text-sm font-medium text-indigo-800">Enter Marks</span>
                </a>
                <a href="{{ route('school-admin.homework.index') }}"
                   class="flex flex-col items-center p-4 bg-amber-50 hover:bg-amber-100 rounded-xl border border-amber-200 transition text-center">
                    <i class="fa-solid fa-book-bookmark text-2xl text-amber-600 mb-2"></i>
                    <span class="text-sm font-medium text-amber-800">Assign Homework</span>
                </a>
                <a href="{{ route('profile.password') }}"
                   class="flex flex-col items-center p-4 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-200 transition text-center">
                    <i class="fa-solid fa-key text-2xl text-slate-600 mb-2"></i>
                    <span class="text-sm font-medium text-slate-800">Change Password</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Recent Homework --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800 flex items-center">
                <i class="fa-solid fa-book-bookmark mr-2 text-amber-500"></i> Recent Homework
            </h2>
            <a href="{{ route('school-admin.homework.index') }}" class="text-xs text-indigo-600 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Subject</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Class</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Title</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentHomework as $hw)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $hw->classSubject->subject->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $hw->classSubject->class->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $hw->title }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ \Carbon\Carbon::parse($hw->due_on)->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-book-bookmark text-3xl mb-2 block text-slate-300"></i>
                                No homework assigned yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection