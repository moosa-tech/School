@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">My Attendance History</h1>
        <p class="mt-1 text-sm text-slate-500">Daily records and monthly totals for {{ $student->full_name }}.</p>
    </div>

    @include('student.partials.navigation')

    <form method="GET" action="{{ route('student.attendance') }}" class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div>
            <label for="month" class="mb-1 block text-xs font-semibold uppercase text-slate-500">Month</label>
            <input id="month" type="month" name="month" value="{{ $month }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">View month</button>
    </form>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach(['total' => 'Recorded days', 'present' => 'Present', 'late' => 'Late', 'absent' => 'Absent', 'excused' => 'Excused'] as $field => $label)
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <div class="text-xl font-bold text-indigo-700">{{ $monthlyStats?->$field ?? 0 }}</div>
                <div class="mt-1 text-xs font-semibold uppercase text-slate-500">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-bold text-slate-800">{{ \Carbon\Carbon::createFromFormat('!Y-m', $month)->format('F Y') }} · Daily attendance</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Class</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Remarks</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendance as $record)
                        <tr>
                            <td class="px-5 py-3">{{ $record->session?->attendance_date?->format('d M Y') ?? 'Not recorded' }}</td>
                            <td class="px-5 py-3">{{ $record->session?->class?->name ?? '—' }}{{ $record->session?->section?->name ? ' · '.$record->session->section->name : '' }}</td>
                            <td class="px-5 py-3 capitalize">{{ $record->status }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $record->remarks ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No attendance records for this month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
