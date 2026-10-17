@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Subscription Plans</h1>
            <p class="text-slate-500 text-sm">Define tier pricing, limits on students, and billing frequencies</p>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $plan->name }}</h2>
                        <span class="text-xs text-slate-400 capitalize">{{ $plan->billing_interval }} billing</span>
                    </div>
                    <span class="text-xs bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full font-bold">
                        {{ $plan->subscriptions_count }} Schools
                    </span>
                </div>

                <div class="text-3xl font-extrabold text-slate-900">
                    ${{ number_format($plan->price, 0) }}
                    <span class="text-xs font-normal text-slate-400">/{{ $plan->billing_interval }}</span>
                </div>

                <ul class="text-xs text-slate-600 space-y-2 border-t border-slate-100 pt-3">
                    <li class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-emerald-500"></i>
                        <span>Up to <strong>{{ number_format($plan->student_limit) }}</strong> students</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-emerald-500"></i>
                        <span>Up to <strong>{{ number_format($plan->staff_limit) }}</strong> staff members</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i class="fa-solid fa-check text-emerald-500"></i>
                        <span>Full Fee, Attendance & Exam modules</span>
                    </li>
                </ul>
            </div>
        @endforeach
    </div>

    <!-- Create Plan Form -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-900 mb-4">Create New Subscription Plan</h2>
        <form action="{{ route('super-admin.plans.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Plan Name</label>
                <input type="text" name="name" required placeholder="e.g. Enterprise Tier" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Price</label>
                <input type="number" step="0.01" name="price" required placeholder="199.00" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Currency</label>
                <input type="text" name="currency" value="USD" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Billing Interval</label>
                <select name="billing_interval" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Student Limit</label>
                <input type="number" name="student_limit" value="1000" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Staff Limit</label>
                <input type="number" name="staff_limit" value="100" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm">
            </div>
            <div class="sm:col-span-3 text-right">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition">
                    Save Plan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
