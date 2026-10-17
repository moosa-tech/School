@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between no-print">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Payment Receipt #{{ $payment->receipt_number }}</h1>
            <p class="text-xs text-slate-400">Date: {{ $payment->payment_date->format('Y-m-d') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print Receipt</span>
            </button>
            <a href="{{ route('school-admin.fees.payments') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700 px-2">Back</a>
        </div>
    </div>

    <!-- Printable Receipt Box -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-md p-8 space-y-6">
        <div class="flex justify-between items-start border-b border-slate-200 pb-4">
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $payment->school->name }}</div>
                <h2 class="text-xl font-extrabold text-slate-900 mt-1">Official Payment Receipt</h2>
                <div class="text-xs text-slate-500 mt-0.5">Campus: {{ $payment->school->address ?? 'Main Campus' }}</div>
            </div>
            <div class="text-right">
                <div class="text-sm font-mono font-bold text-indigo-700">{{ $payment->receipt_number }}</div>
                <div class="text-xs text-slate-400 mt-1">Date: {{ $payment->payment_date->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 uppercase font-semibold">Received From:</span>
                <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $payment->student->full_name }}</div>
                <div class="text-slate-500 font-mono">Adm #: {{ $payment->student->admission_number }}</div>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold">Guardian / Contact:</span>
                <div class="font-medium text-slate-800 mt-0.5">{{ $payment->student->primaryGuardian()?->name ?? 'N/A' }}</div>
                <div class="text-slate-500">{{ $payment->student->primaryGuardian()?->phone ?? '-' }}</div>
            </div>
        </div>

        <div class="space-y-3">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Payment Breakdown & Allocations</h3>
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-600 uppercase font-bold">
                    <tr>
                        <th class="p-3">Invoice Number</th>
                        <th class="p-3">Period</th>
                        <th class="p-3 text-right">Amount Allocated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($payment->allocations as $alloc)
                        <tr>
                            <td class="p-3 font-mono font-bold text-slate-800">{{ $alloc->invoice->invoice_number }}</td>
                            <td class="p-3 text-slate-600">{{ $alloc->invoice->billing_period }}</td>
                            <td class="p-3 text-right font-mono font-bold text-emerald-700">${{ number_format($alloc->amount_applied, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-slate-200 text-xs font-bold">
                    <tr>
                        <td colspan="2" class="p-3 text-right text-slate-800">Total Received Amount:</td>
                        <td class="p-3 text-right font-mono text-base text-emerald-700">${{ number_format($payment->amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="text-xs text-slate-500 bg-slate-50 p-3 rounded-lg flex items-center justify-between">
            <span>Payment Method: <strong class="uppercase text-slate-800">{{ $payment->method }}</strong></span>
            <span>Ref: <strong class="font-mono text-slate-800">{{ $payment->reference ?? 'CASH' }}</strong></span>
            <span>Cashier: <strong class="text-slate-800">{{ $payment->receiver?->name ?? 'Admin' }}</strong></span>
        </div>

        <div class="pt-8 border-t border-slate-200 text-right text-xs">
            <div class="border-b border-slate-300 w-44 ml-auto mb-1"></div>
            <span class="text-slate-400">Authorized Cashier Stamp & Signature</span>
        </div>
    </div>

</div>
@endsection
