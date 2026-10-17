@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Fee Invoices & Challans</h1>
            <p class="text-slate-500 text-sm">Issue monthly tuition challans, itemized invoices, and track outstanding dues</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('school-admin.fees.structures') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-3.5 py-2.5 rounded-xl transition">
                Fee Structures
            </a>
            <a href="{{ route('school-admin.fees.invoices.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition inline-flex items-center space-x-1.5">
                <i class="fa-solid fa-plus"></i>
                <span>Issue New Invoice</span>
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('school-admin.fees.invoices') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice #, student name..."
                class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">

            <select name="status" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>

            <input type="text" name="billing_period" value="{{ request('billing_period') }}" placeholder="Filter billing period (e.g. July 2026)..."
                class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold py-2 px-4 rounded-xl transition">
                    Filter Invoices
                </button>
                @if(request()->hasAny(['search', 'status', 'billing_period']))
                    <a href="{{ route('school-admin.fees.invoices') }}" class="text-xs text-slate-500 hover:text-slate-700 px-2">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Challan / Invoice #</th>
                        <th class="px-6 py-4">Student</th>
                        <th class="px-6 py-4">Billing Period</th>
                        <th class="px-6 py-4">Due Date</th>
                        <th class="px-6 py-4">Total Amount</th>
                        <th class="px-6 py-4">Paid Amount</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-mono font-bold text-indigo-700">
                                {{ $inv->invoice_number }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $inv->student->full_name }}</div>
                                <div class="text-[11px] text-slate-400">Adm #: {{ $inv->student->admission_number }}</div>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $inv->billing_period }}
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ $inv->due_date->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                ${{ number_format($inv->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-emerald-700">
                                ${{ number_format($inv->paid_amount, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full {{ $inv->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : ($inv->status === 'partial' ? 'bg-blue-50 text-blue-700' : ($inv->status === 'overdue' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700')) }}">
                                    {{ ucfirst($inv->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('school-admin.fees.invoices.show', $inv) }}" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg font-bold transition">
                                    Voucher
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">No invoices found matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $invoices->links() }}
        </div>
    </div>

</div>
@endsection
