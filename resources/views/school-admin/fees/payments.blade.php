@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Payments & Receipts</h1>
            <p class="text-slate-500 text-sm">Audited record of collected payments and printable receipts</p>
        </div>
        <a href="{{ route('school-admin.fees.invoices') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
            View Invoices
        </a>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Receipt #</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Student</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Method</th>
                        <th class="px-6 py-4">Received By</th>
                        <th class="px-6 py-4 text-right">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($payments as $pay)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-4 font-mono font-bold text-slate-800">{{ $pay->receipt_number }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $pay->payment_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $pay->student->full_name }}</td>
                            <td class="px-6 py-4 font-bold text-emerald-700">${{ number_format($pay->amount, 2) }}</td>
                            <td class="px-6 py-4 uppercase font-semibold text-[10px] text-slate-700">{{ $pay->method }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $pay->receiver?->name ?? 'Admin' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('school-admin.fees.receipt', $pay) }}" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg font-bold transition">
                                    Print Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">No payment receipts recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $payments->links() }}
        </div>
    </div>

</div>
@endsection
