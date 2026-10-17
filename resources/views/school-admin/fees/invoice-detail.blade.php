@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Fee Invoice #{{ $invoice->invoice_number }}</h1>
            <p class="text-slate-500 text-sm">Status: <strong class="uppercase text-indigo-700">{{ $invoice->status }}</strong> • Due: {{ $invoice->due_date->format('M d, Y') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition flex items-center space-x-1.5 shadow-sm">
                <i class="fa-solid fa-print"></i>
                <span>Print Voucher</span>
            </button>
            <a href="{{ route('school-admin.fees.invoices') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700 px-3">Back</a>
        </div>
    </div>

    <!-- Payment & Adjustment Panels (Hidden on Print) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 no-print">

        <!-- Receive Payment Box -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                <i class="fa-solid fa-cash-register text-emerald-600"></i>
                <span>Receive Payment</span>
            </h2>

            @if($invoice->outstandingBalance() > 0)
                <form action="{{ route('school-admin.fees.invoices.pay', $invoice) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Amount ($) *</label>
                            <input type="number" step="0.01" name="amount" value="{{ $invoice->outstandingBalance() }}" max="{{ $invoice->outstandingBalance() }}" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-emerald-800">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Payment Date *</label>
                            <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Method *</label>
                            <select name="method" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="card">Debit/Credit Card</option>
                                <option value="online">Online Gateway</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Reference / Slip #</label>
                            <input type="text" name="reference" placeholder="e.g. TR-98214"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-xl text-xs shadow-sm transition">
                        Confirm Payment & Generate Receipt
                    </button>
                </form>
            @else
                <div class="p-4 bg-emerald-50 text-emerald-800 rounded-xl text-xs font-semibold text-center">
                    <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i>
                    This invoice is fully paid.
                </div>
            @endif
        </div>

        <!-- Apply Adjustment Box -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                <i class="fa-solid fa-tags text-indigo-600"></i>
                <span>Discount / Adjustment</span>
            </h2>

            <form action="{{ route('school-admin.fees.adjustments.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="student_id" value="{{ $invoice->student_id }}">
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Type *</label>
                        <select name="type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                            <option value="discount">Scholarship / Discount</option>
                            <option value="waiver">Administrative Waiver</option>
                            <option value="write_off">Bad Debt Write-off</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Adjustment Amount ($) *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="500.00"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                    </div>
                    <div class="col-span-2">
                        <label class="block font-semibold text-slate-600 mb-1">Reason / Justification *</label>
                        <input type="text" name="reason" required placeholder="e.g. Sibling discount approved by Principal"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-semibold py-2 rounded-xl text-xs transition">
                    Apply Audited Adjustment
                </button>
            </form>
        </div>

    </div>

    <!-- Printable Invoice Challan / Voucher Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-md p-8 sm:p-10 space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-start border-b border-slate-200 pb-6">
            <div>
                <div class="text-xs uppercase tracking-wider text-slate-400 font-bold">{{ $invoice->school->name }}</div>
                <h2 class="text-2xl font-bold text-slate-900 mt-1">Fee Challan Voucher</h2>
                <div class="text-xs text-slate-500 mt-1">Billing Period: <strong>{{ $invoice->billing_period }}</strong></div>
            </div>
            <div class="text-right">
                <div class="font-mono text-base font-bold text-indigo-700">{{ $invoice->invoice_number }}</div>
                <div class="text-xs text-slate-400 mt-1">Issue Date: {{ $invoice->issue_date->format('Y-m-d') }}</div>
                <div class="text-xs text-rose-600 font-semibold">Due Date: {{ $invoice->due_date->format('Y-m-d') }}</div>
            </div>
        </div>

        <!-- Student Particulars -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs bg-slate-50 p-4 rounded-xl border border-slate-100">
            <div>
                <span class="text-slate-400 uppercase font-semibold">Student Name:</span>
                <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $invoice->student->full_name }}</div>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold">Admission #:</span>
                <div class="font-mono font-bold text-slate-800 mt-0.5">{{ $invoice->student->admission_number }}</div>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold">Father / Guardian:</span>
                <div class="font-medium text-slate-800 mt-0.5">{{ $invoice->student->primaryGuardian()?->name ?? 'N/A' }}</div>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold">Contact:</span>
                <div class="font-medium text-slate-800 mt-0.5">{{ $invoice->student->primaryGuardian()?->phone ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Line Items Table -->
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-100 uppercase text-slate-600 font-bold">
                <tr>
                    <th class="p-3">#</th>
                    <th class="p-3">Fee Category & Description</th>
                    <th class="p-3 text-right">Amount ($)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($invoice->items as $idx => $item)
                    <tr>
                        <td class="p-3 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="p-3 font-medium text-slate-800">
                            {{ $item->description }}
                            @if($item->category)
                                <span class="text-[10px] text-slate-400 font-normal">({{ $item->category->name }})</span>
                            @endif
                        </td>
                        <td class="p-3 text-right font-mono font-bold text-slate-900">${{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-slate-200 text-xs font-semibold">
                <tr>
                    <td colspan="2" class="p-2 text-right text-slate-500">Subtotal:</td>
                    <td class="p-2 text-right font-mono">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->discount_total > 0)
                    <tr>
                        <td colspan="2" class="p-2 text-right text-emerald-600">Discounts & Waivers:</td>
                        <td class="p-2 text-right font-mono text-emerald-600">-${{ number_format($invoice->discount_total, 2) }}</td>
                    </tr>
                @endif
                @if($invoice->fine_total > 0)
                    <tr>
                        <td colspan="2" class="p-2 text-right text-rose-600">Late Fee Fine:</td>
                        <td class="p-2 text-right font-mono text-rose-600">+${{ number_format($invoice->fine_total, 2) }}</td>
                    </tr>
                @endif
                <tr class="text-sm font-bold border-t border-slate-200">
                    <td colspan="2" class="p-3 text-right text-slate-900">Total Billed:</td>
                    <td class="p-3 text-right font-mono text-indigo-700">${{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="p-2 text-right text-emerald-700 font-bold">Total Paid:</td>
                    <td class="p-2 text-right font-mono text-emerald-700 font-bold">${{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                <tr class="text-sm font-extrabold bg-slate-50 border-t border-slate-200">
                    <td colspan="2" class="p-3 text-right text-rose-600">Outstanding Balance:</td>
                    <td class="p-3 text-right font-mono text-rose-600">${{ number_format($invoice->outstandingBalance(), 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Payment Allocation History -->
        @if($invoice->allocations->isNotEmpty())
            <div class="border-t border-slate-100 pt-4">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Allocated Payment Receipts</div>
                <div class="space-y-1.5 text-xs">
                    @foreach($invoice->allocations as $alloc)
                        <div class="flex justify-between items-center bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                            <div>
                                <strong class="font-mono text-slate-800">{{ $alloc->payment->receipt_number }}</strong>
                                <span class="text-slate-400 text-[11px]">paid on {{ $alloc->payment->payment_date->format('Y-m-d') }} via {{ strtoupper($alloc->payment->method) }}</span>
                            </div>
                            <span class="font-bold text-emerald-700 font-mono">+${{ number_format($alloc->amount_applied, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-8 pt-12 border-t border-slate-200 text-xs text-center">
            <div>
                <div class="border-b border-slate-300 w-48 mx-auto mb-1"></div>
                <span class="text-slate-400">Accountant Signature</span>
            </div>
            <div>
                <div class="border-b border-slate-300 w-48 mx-auto mb-1"></div>
                <span class="text-slate-400">Depositor Signature</span>
            </div>
        </div>
    </div>

</div>
@endsection
