@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Student Register Report</h1>
            <p class="text-slate-500 text-sm">Official enrollment roll with guardian particulars and placement</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-file-csv"></i>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('school-admin.reports.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700 px-2">Back</a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('school-admin.reports.student-register') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <select name="class_id" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <option value="">All Classes</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>

            <select name="status" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="transferred" {{ request('status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                <option value="withdrawn" {{ request('status') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
            </select>

            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-xl text-xs transition">
                Filter Register
            </button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4">Adm #</th>
                        <th class="p-4">Student Name</th>
                        <th class="p-4">Gender</th>
                        <th class="p-4">Current Class</th>
                        <th class="p-4">Section</th>
                        <th class="p-4">Guardian Name</th>
                        <th class="p-4">Phone</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $st)
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-4 font-mono font-bold text-slate-800">{{ $st->admission_number }}</td>
                            <td class="p-4 font-semibold text-slate-900">{{ $st->full_name }}</td>
                            <td class="p-4 capitalize">{{ $st->gender }}</td>
                            <td class="p-4">{{ $st->currentEnrollment?->class?->name ?? 'N/A' }}</td>
                            <td class="p-4">{{ $st->currentEnrollment?->section?->name ?? 'N/A' }}</td>
                            <td class="p-4">{{ $st->primaryGuardian()?->name ?? 'N/A' }}</td>
                            <td class="p-4">{{ $st->primaryGuardian()?->phone ?? '-' }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $st->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $st->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="p-6 text-center text-slate-400">No student records found.</td></tr>
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
