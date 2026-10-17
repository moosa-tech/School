@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Parent Dashboard</h1>
        <p class="text-sm text-slate-500">Your linked students and school notices.</p>
    </div>

    <section class="space-y-4">
        <h2 class="text-base font-bold text-slate-900">My Children</h2>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            @forelse($children as $child)
                @php
                    $outstandingBalance = $child->feeInvoices->sum(fn ($invoice) => max(0, (float) $invoice->total_amount - (float) $invoice->paid_amount));
                    $attendanceCount = $child->attendances->count();
                    $presentCount = $child->attendances->whereIn('status', ['present', 'late'])->count();
                @endphp
                <article class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">{{ $child->full_name }}</h3>
                            <p class="text-xs text-slate-500 mt-1">Admission {{ $child->admission_number }}</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $child->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($child->status) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-4">
                        <div>
                            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Class</div>
                            <div class="text-sm font-medium text-slate-800 mt-1">
                                {{ $child->currentEnrollment?->class?->name ?? 'Not assigned' }}
                                {{ $child->currentEnrollment?->section?->name ?? '' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Attendance</div>
                            <div class="text-sm font-medium text-slate-800 mt-1">
                                {{ $attendanceCount ? round(($presentCount / $attendanceCount) * 100) : 0 }}%
                            </div>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Outstanding Fees</div>
                            <div class="text-sm font-medium text-slate-800 mt-1">{{ number_format($outstandingBalance, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Payments Recorded</div>
                            <div class="text-sm font-medium text-slate-800 mt-1">{{ $child->feePayments->count() }}</div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="bg-white border border-slate-200 rounded-xl p-5 text-sm text-slate-500">
                    No student records are linked to this parent account for the current school.
                </div>
            @endforelse
        </div>
    </section>

    <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h2 class="text-base font-bold text-slate-900 mb-4">School Notices</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @forelse($announcements as $announcement)
                <article class="p-4 bg-slate-50 rounded-lg border border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-800">{{ $announcement->title }}</h3>
                    <p class="text-sm text-slate-600 mt-2">{{ $announcement->body }}</p>
                    <time class="block text-xs text-slate-400 mt-3">{{ $announcement->created_at->format('M j, Y') }}</time>
                </article>
            @empty
                <p class="text-sm text-slate-500">No notices are available.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection