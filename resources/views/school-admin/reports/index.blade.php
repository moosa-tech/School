@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Reports & Analytics Hub</h1>
            <p class="text-slate-500 text-sm">Tenant-scoped operational reports, ledger breakdowns, and CSV data exports</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- 1. Student Register -->
        <a href="{{ route('school-admin.reports.student-register') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 hover:border-indigo-300 transition group space-y-3">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-700 rounded-xl flex items-center justify-center text-xl group-hover:bg-indigo-600 group-hover:text-white transition">
                <i class="fa-solid fa-address-book"></i>
            </div>
            <h2 class="text-base font-bold text-slate-900">Student Register</h2>
            <p class="text-xs text-slate-500 leading-relaxed">
                Full directory of enrolled students with guardian details, class/section breakdowns, and CSV export.
            </p>
            <div class="text-xs font-bold text-indigo-600 flex items-center space-x-1">
                <span>Generate Report</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </div>
        </a>

        <!-- 2. Fee Collection Report -->
        <a href="{{ route('school-admin.reports.fee-collection') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 hover:border-emerald-300 transition group space-y-3">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-700 rounded-xl flex items-center justify-center text-xl group-hover:bg-emerald-600 group-hover:text-white transition">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <h2 class="text-base font-bold text-slate-900">Fee Collection Report</h2>
            <p class="text-xs text-slate-500 leading-relaxed">
                Audited cash-in receipts categorized by payment method, cashier, and date range with export.
            </p>
            <div class="text-xs font-bold text-emerald-600 flex items-center space-x-1">
                <span>Generate Report</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </div>
        </a>

        <!-- 3. Outstanding Dues Report -->
        <a href="{{ route('school-admin.reports.outstanding-dues') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 hover:border-rose-300 transition group space-y-3">
            <div class="w-12 h-12 bg-rose-50 text-rose-700 rounded-xl flex items-center justify-center text-xl group-hover:bg-rose-600 group-hover:text-white transition">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <h2 class="text-base font-bold text-slate-900">Outstanding Dues & Defaulters</h2>
            <p class="text-xs text-slate-500 leading-relaxed">
                As-of aging balances, overdue invoices, and student defaulters list with contact info for follow-up.
            </p>
            <div class="text-xs font-bold text-rose-600 flex items-center space-x-1">
                <span>Generate Report</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </div>
        </a>

    </div>

</div>
@endsection
