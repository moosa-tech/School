@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ mode: 'single' }">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Issue Fee Invoices</h1>
            <p class="text-slate-500 text-sm">Generate single customized invoice or run batch invoice generation for a whole class</p>
        </div>
        <a href="{{ route('school-admin.fees.invoices') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Back to Invoices</a>
    </div>

    <!-- Toggle Mode -->
    <div class="inline-flex p-1 bg-slate-200/80 rounded-xl text-xs font-bold text-slate-700">
        <button @click="mode = 'single'" :class="mode === 'single' ? 'bg-white shadow text-indigo-700' : 'hover:text-slate-900'" class="px-4 py-2 rounded-lg transition">
            Single Student Invoice
        </button>
        <button @click="mode = 'batch'" :class="mode === 'batch' ? 'bg-white shadow text-indigo-700' : 'hover:text-slate-900'" class="px-4 py-2 rounded-lg transition">
            Batch Class Generation
        </button>
    </div>

    <!-- 1. Single Student Invoice Form -->
    <div x-show="mode === 'single'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('school-admin.fees.invoices.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Select Student *</label>
                    <select name="student_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                        @foreach($students as $s)
                            <option value="{{ $s->id }}">{{ $s->admission_number }} - {{ $s->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Academic Session *</label>
                    <select name="academic_year_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                        <option value="{{ $currentYear?->id }}">{{ $currentYear?->name ?? 'Current Year' }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Billing Period *</label>
                    <input type="text" name="billing_period" value="{{ date('F Y') }}" required placeholder="e.g. October 2026"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Issue Date *</label>
                    <input type="date" name="issue_date" value="{{ now()->toDateString() }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Due Date *</label>
                    <input type="date" name="due_date" value="{{ now()->addDays(10)->toDateString() }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <!-- Itemized Line Items -->
            <div class="space-y-3 pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Itemized Breakdown</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Category</label>
                        <select name="items[0][category_id]" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Description</label>
                        <input type="text" name="items[0][description]" value="Monthly Tuition Fee" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Amount ($)</label>
                        <input type="number" step="0.01" name="items[0][amount]" value="5000.00" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <select name="items[1][category_id]" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                            <option value="">None / Optional Item</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="text" name="items[1][description]" placeholder="e.g. Computer Lab Fee" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                    </div>
                    <div>
                        <input type="number" step="0.01" name="items[1][amount]" placeholder="0.00" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                    </div>
                </div>
            </div>

            <!-- Discounts & Fines -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Discount / Scholarship ($)</label>
                    <input type="number" step="0.01" name="discount_total" value="0.00"
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Late Fine ($)</label>
                    <input type="number" step="0.01" name="fine_total" value="0.00"
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-right">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md transition">
                    Generate Single Invoice
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Batch Invoice Form -->
    <div x-show="mode === 'batch'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('school-admin.fees.invoices.batch') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-xs text-indigo-900">
                <i class="fa-solid fa-wand-magic-sparkles mr-1 text-indigo-600"></i>
                Batch Invoice Generation will generate invoices for all students actively enrolled in the chosen class based on that class's configured <strong>Fee Structure</strong>. Duplicate invoices for the same period will automatically be skipped.
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Class *</label>
                    <select name="class_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Academic Session *</label>
                    <select name="academic_year_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                        <option value="{{ $currentYear?->id }}">{{ $currentYear?->name ?? 'Current Year' }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Billing Period *</label>
                    <input type="text" name="billing_period" value="{{ date('F Y') }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Issue Date *</label>
                    <input type="date" name="issue_date" value="{{ now()->toDateString() }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Due Date *</label>
                    <input type="date" name="due_date" value="{{ now()->addDays(10)->toDateString() }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-right">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md transition">
                    Run Batch Generation
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
