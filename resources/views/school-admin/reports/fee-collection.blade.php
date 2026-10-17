@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center">
                <i class="fa-solid fa-money-bill-trend-up text-emerald-600 mr-2"></i> Fee Collection Report
            </h1>
            <p class="text-sm text-slate-500">Summary of all fee payments received across the school</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-file-csv"></i>
                <span>Export CSV</span>
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-semibold rounded-xl shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print</span>
            </button>
            <a href="{{ route('school-admin.reports.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                Back
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <form method="GET" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Payment Method</label>
                <select name="method" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cheque" {{ request('method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="online" {{ request('method') === 'online' ? 'selected' : '' }}>Online</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-filter mr-1"></i> Apply Filter
                </button>
            </div>
        </div>
    </form>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
            <div class="text-xs font-semibold uppercase text-slate-500">Total Collected</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($totalCollected, 2) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
            <div class="text-xs font-semibold uppercase text-slate-500">Total Transactions</div>
            <div class="text-2xl font-black text-indigo-600 mt-1">{{ $payments->total() }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
            <div class="text-xs font-semibold uppercase text-slate-500">Average Payment</div>
            <div class="text-2xl font-black text-slate-800 mt-1">
                {{ $payments->total() > 0 ? number_format($totalCollected / $payments->total(), 2) : '0.00' }}
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase">
                        <th class="py-3 px-4">Receipt #</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-semibold text-slate-800">{{ $payment->receipt_number }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $payment->payment_date?->format('d M Y') }}</td>
                            <td class="py-3 px-4 font-medium text-slate-900">{{ $payment->student->full_name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 font-bold text-emerald-600">{{ number_format($payment->amount, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize bg-slate-100 text-slate-700">
                                    {{ str_replace('_', ' ', $payment->method) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-mono text-xs">{{ $payment->reference ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400">
                                <i class="fa-solid fa-receipt text-3xl mb-2 block"></i>
                                No fee payments match the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
