@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Student Directory & Admissions</h1>
            <p class="text-slate-500 text-sm">Comprehensive student profiles, academic enrollments, and ledger tracking</p>
        </div>
        <a href="{{ route('school-admin.students.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition inline-flex items-center space-x-2">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span>Admit New Student</span>
        </a>
    </div>

    <!-- Filters Box -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
        <form method="GET" action="{{ route('school-admin.students.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search admission no, student name, guardian phone..."
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white">
            </div>

            <div>
                <select name="class_id" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="transferred" {{ request('status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                    <option value="withdrawn" {{ request('status') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                    <option value="graduated" {{ request('status') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold py-2 px-4 rounded-xl transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'class_id', 'status']))
                    <a href="{{ route('school-admin.students.index') }}" class="text-xs text-slate-500 hover:text-slate-700 px-2">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Adm #</th>
                        <th class="px-6 py-4">Student Name</th>
                        <th class="px-6 py-4">Current Class & Section</th>
                        <th class="px-6 py-4">Guardian Contact</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Profile</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-indigo-700">
                                {{ $student->admission_number }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('school-admin.students.show', $student) }}" class="font-bold text-slate-900 hover:text-indigo-600">
                                    {{ $student->full_name }}
                                </a>
                                <div class="text-[11px] text-slate-400">Gender: {{ ucfirst($student->gender) }} • Adm: {{ $student->admission_date?->format('M Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($enr = $student->currentEnrollment)
                                    <span class="font-semibold text-slate-800">{{ $enr->class->name ?? 'N/A' }}</span>
                                    <span class="text-xs text-slate-500">({{ $enr->section->name ?? 'N/A' }})</span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Not currently enrolled</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-800 font-medium">{{ $student->primaryGuardian()?->name ?? 'None' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $student->primaryGuardian()?->phone ?? $student->emergency_phone ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $student->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($student->status === 'graduated' ? 'bg-purple-50 text-purple-700' : 'bg-slate-100 text-slate-700') }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('school-admin.students.show', $student) }}" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition inline-flex items-center space-x-1">
                                    <span>Open Profile</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">No students found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $students->links() }}
        </div>
    </div>

</div>
@endsection
