@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 mr-2"></i> Outstanding Dues Report
            </h1>
            <p class="text-sm text-slate-500">Unpaid and partially paid fee invoices requiring collection</p>
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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Invoice Status</label>
                <select name="status" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                    <option value="">All Outstanding (Pending / Partial / Overdue)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="py-2 px-6 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-filter mr-1"></i> Apply Filter
                </button>
            </div>
        </div>
    </form>

    <!-- Total Due Banner -->
    <div class="bg-gradient-to-r from-rose-500 to-amber-600 rounded-2xl p-6 text-white shadow-md flex items-center justify-between">
        <div>
            <div class="text-xs uppercase font-bold tracking-wider text-rose-100">Total Outstanding Balance</div>
            <div class="text-3xl font-black mt-1">{{ number_format($totalOutstanding, 2) }}</div>
            <div class="text-xs text-rose-100 mt-1">{{ $invoices->total() }} unpaid / partial invoices currently recorded</div>
        </div>
        <div class="hidden sm:block text-5xl text-rose-200/60">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase">
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Class</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4 text-right">Paid</th>
                        <th class="py-3 px-4 text-right">Outstanding</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-semibold text-slate-800">{{ $invoice->invoice_number }}</td>
                            <td class="py-3 px-4 font-medium text-slate-900">{{ $invoice->student->full_name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $invoice->student->currentEnrollment?->class?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $invoice->due_date?->format('d M Y') }}</td>
                            <td class="py-3 px-4 text-right text-slate-700">{{ number_format($invoice->total_amount, 2) }}</td>
                            <td class="py-3 px-4 text-right text-emerald-600">{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-rose-600">{{ number_format($invoice->outstandingBalance(), 2) }}</td>
                            <td class="py-3 px-4 text-center">
                                @php
                                    $badge = match($invoice->status) {
                                        'paid' => 'bg-emerald-100 text-emerald-800',
                                        'partial' => 'bg-amber-100 text-amber-800',
                                        'overdue' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-slate-100 text-slate-800',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize {{ $badge }}">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('school-admin.fees.invoices.show', $invoice->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-xs">
                                    Collect Fee &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-10 text-slate-400">
                                <i class="fa-solid fa-circle-check text-4xl text-emerald-400 mb-2 block"></i>
                                Excellent! No outstanding fee dues found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
