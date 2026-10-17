@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Fee Structures & Categories</h1>
            <p class="text-slate-500 text-sm">Configure standard fee tariff rates by academic year and class</p>
        </div>
        <a href="{{ route('school-admin.fees.invoices') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
            View Invoices
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- 1. Fee Categories Panel -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Fee Categories</h2>

            <div class="space-y-2">
                @foreach($categories as $cat)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs flex justify-between items-center">
                        <div>
                            <span class="font-bold text-slate-800">{{ $cat->name }}</span>
                            <div class="text-[11px] text-slate-400">{{ $cat->description ?? 'Standard Category' }}</div>
                        </div>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                @endforeach
            </div>

            <!-- Create Category Form -->
            <form action="{{ route('school-admin.fees.categories.store') }}" method="POST" class="pt-3 border-t border-slate-100 space-y-2">
                @csrf
                <label class="block text-xs font-semibold text-slate-700 uppercase">New Category</label>
                <input type="text" name="name" required placeholder="e.g. Sports Fee" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <input type="text" name="description" placeholder="Optional notes..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-semibold py-2 rounded-xl text-xs transition">
                    Add Category
                </button>
            </form>
        </div>

        <!-- 2. Class Fee Tariff Structures Panel -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Active Tariff Rates by Class</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 uppercase font-semibold text-slate-500">
                        <tr>
                            <th class="p-3">Class</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Frequency</th>
                            <th class="p-3">Due Day</th>
                            <th class="p-3 text-right">Standard Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($structures as $st)
                            <tr>
                                <td class="p-3 font-semibold text-slate-900">{{ $st->class->name }}</td>
                                <td class="p-3">{{ $st->category->name }}</td>
                                <td class="p-3 capitalize">{{ $st->frequency }}</td>
                                <td class="p-3 text-slate-500">{{ $st->due_day }}th of month</td>
                                <td class="p-3 text-right font-mono font-bold text-indigo-700 text-sm">${{ number_format($st->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-center text-slate-400">No fee structures configured yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Create Structure Form -->
            <form action="{{ route('school-admin.fees.structures.store') }}" method="POST" class="pt-4 border-t border-slate-100 space-y-3">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $currentYear?->id }}">

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Class</label>
                        <select name="class_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Fee Category</label>
                        <select name="fee_category_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Frequency</label>
                        <select name="frequency" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                            <option value="monthly">Monthly</option>
                            <option value="annual">Annual</option>
                            <option value="one_time">One-Time</option>
                            <option value="term">Per Term</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Amount ($)</label>
                        <input type="number" step="0.01" name="amount" required placeholder="5000.00" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center space-x-2 text-xs">
                        <label class="font-semibold text-slate-600">Default Due Day of Month:</label>
                        <input type="number" name="due_day" value="10" min="1" max="28" class="w-16 px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-center">
                    </div>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-xl text-xs transition">
                        Add Fee Tariff
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
