@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">My Fee History</h1>
        <p class="mt-1 text-sm text-slate-500">Invoices, balances, payments, and available receipts.</p>
    </div>

    @include('student.partials.navigation')

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs font-semibold uppercase text-slate-500">Invoiced</div><div class="mt-2 text-xl font-bold text-slate-800">Rs. {{ number_format((float) ($totals->total ?? 0), 2) }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs font-semibold uppercase text-slate-500">Paid</div><div class="mt-2 text-xl font-bold text-emerald-700">Rs. {{ number_format((float) ($totals->paid ?? 0), 2) }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs font-semibold uppercase text-slate-500">Balance</div><div class="mt-2 text-xl font-bold text-amber-700">Rs. {{ number_format(max(0, (float) ($totals->total ?? 0) - (float) ($totals->paid ?? 0)), 2) }}</div></div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-800">Invoices</h2></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr><th class="px-5 py-3">Invoice</th><th class="px-5 py-3">Period</th><th class="px-5 py-3 text-right">Amount</th><th class="px-5 py-3 text-right">Paid</th><th class="px-5 py-3 text-right">Balance</th><th class="px-5 py-3">Status / payments</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $invoice)
                        <tr class="align-top">
                            <td class="px-5 py-3 font-mono font-semibold">{{ $invoice->invoice_number }}</td>
                            <td class="px-5 py-3">{{ $invoice->billing_period ?: $invoice->issue_date?->format('M Y') }}</td>
                            <td class="px-5 py-3 text-right">Rs. {{ number_format((float) $invoice->total_amount, 2) }}</td>
                            <td class="px-5 py-3 text-right text-emerald-700">Rs. {{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            <td class="px-5 py-3 text-right">Rs. {{ number_format($invoice->outstandingBalance(), 2) }}</td>
                            <td class="px-5 py-3">
                                <span class="capitalize">{{ $invoice->status }}</span>
                                @foreach($invoice->allocations as $allocation)
                                    @if($allocation->payment)
                                        <div class="mt-2 text-xs text-slate-500">
                                            {{ $allocation->payment->payment_date?->format('d M Y') }} · Rs. {{ number_format((float) $allocation->amount_applied, 2) }}
                                            @if($allocation->payment->status === 'completed')
                                                · <a class="font-semibold text-indigo-700 hover:underline" href="{{ route('student.fees.receipt', $allocation->payment) }}">Receipt {{ $allocation->payment->receipt_number }}</a>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">No fee invoices are recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">{{ $invoices->links() }}</div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-800">Payment history</h2></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Receipt</th><th class="px-5 py-3">Method</th><th class="px-5 py-3 text-right">Amount</th><th class="px-5 py-3">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $payment)
                        <tr>
                            <td class="px-5 py-3">{{ $payment->payment_date?->format('d M Y') }}</td>
                            <td class="px-5 py-3 font-mono">{{ $payment->receipt_number ?: '—' }}</td>
                            <td class="px-5 py-3 capitalize">{{ str_replace('_', ' ', $payment->method) }}</td>
                            <td class="px-5 py-3 text-right">Rs. {{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="px-5 py-3 capitalize">
                                {{ $payment->status }}
                                @if($payment->status === 'completed')
                                    · <a class="font-semibold text-indigo-700 hover:underline" href="{{ route('student.fees.receipt', $payment) }}">View receipt</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No fee payments are recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">{{ $payments->links() }}</div>
    </section>
</div>
@endsection
