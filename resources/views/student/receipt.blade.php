@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Fee Receipt</h1>
            <p class="mt-1 text-sm text-slate-500">Receipt {{ $payment->receipt_number ?: '—' }}</p>
        </div>
        <button type="button" onclick="window.print()" class="no-print rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Print / Save PDF</button>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid grid-cols-1 gap-5 text-sm sm:grid-cols-2">
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Student</dt><dd class="mt-1 font-semibold text-slate-800">{{ $student->full_name }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Admission number</dt><dd class="mt-1 font-mono text-slate-800">{{ $student->admission_number }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Payment date</dt><dd class="mt-1 text-slate-800">{{ $payment->payment_date?->format('d M Y') }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Payment method</dt><dd class="mt-1 capitalize text-slate-800">{{ str_replace('_', ' ', $payment->method) }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Reference</dt><dd class="mt-1 text-slate-800">{{ $payment->reference ?: '—' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Payment status</dt><dd class="mt-1 capitalize text-slate-800">{{ $payment->status }}</dd></div>
        </dl>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-800">Applied to invoices</h2></div>
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Invoice</th><th class="px-5 py-3">Billing period</th><th class="px-5 py-3 text-right">Applied</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($payment->allocations as $allocation)
                    @if($allocation->invoice)
                        <tr><td class="px-5 py-3 font-mono">{{ $allocation->invoice->invoice_number }}</td><td class="px-5 py-3">{{ $allocation->invoice->billing_period ?: $allocation->invoice->issue_date?->format('M Y') }}</td><td class="px-5 py-3 text-right">Rs. {{ number_format((float) $allocation->amount_applied, 2) }}</td></tr>
                    @endif
                @empty
                    <tr><td colspan="3" class="px-5 py-6 text-center text-slate-500">No invoice allocations are recorded for this payment.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="border-t border-slate-200 bg-slate-50 font-bold"><tr><td colspan="2" class="px-5 py-4">Payment total</td><td class="px-5 py-4 text-right">Rs. {{ number_format((float) $payment->amount, 2) }}</td></tr></tfoot>
        </table>
    </section>
</div>
@endsection
