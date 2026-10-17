@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ tab: 'details' }">

    @if(session('account_credentials.school_id') === session('current_school_id'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
            <h2 class="font-bold">One-time student login credentials</h2>
            <p class="mt-2">Username: <strong>{{ session('account_credentials.username') }}</strong></p>
            <p>Temporary password: <code class="font-mono">{{ \Illuminate\Support\Facades\Crypt::decryptString(session('account_credentials.encrypted_password')) }}</code></p>
            <p class="mt-2 text-xs">{{ session('account_credentials.whatsapp_status') }}</p>
            <p class="mt-1 text-xs">The student must change this password before using the portal.</p>
        </div>
    @endif

    <!-- Profile Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-2xl bg-indigo-900 text-white flex items-center justify-center text-2xl font-bold shadow-md">
                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name ?? '', 0, 1) }}
            </div>
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $student->full_name }}</h1>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $student->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                        {{ ucfirst($student->status) }}
                    </span>
                </div>
                <div class="text-xs text-slate-400 mt-1 flex flex-wrap items-center gap-x-3">
                    <span>Admission #: <strong class="text-slate-700 font-mono">{{ $student->admission_number }}</strong></span>
                    <span>•</span>
                    <span>Class: <strong class="text-indigo-600">{{ $student->currentEnrollment?->class?->name ?? 'None' }} ({{ $student->currentEnrollment?->section?->name ?? '-' }})</strong></span>
                    <span>•</span>
                    <span>Roll: <strong class="text-slate-700">{{ $student->currentEnrollment?->roll_number ?? 'N/A' }}</strong></span>
                    <span>•</span>
                    <span>Attendance Rate: <strong class="text-emerald-600">{{ $attendancePercentage }}%</strong></span>
                </div>
            </div>
        </div>

        <!-- Status Change Modal/Form -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('school-admin.students.qr-card', $student) }}" class="rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white">Student QR ID card</a>
            <form action="{{ route('school-admin.students.status', $student) }}" method="POST" class="flex items-center space-x-2">
                @csrf
                <select name="status" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700">
                    <option value="active" {{ $student->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="transferred" {{ $student->status === 'transferred' ? 'selected' : '' }}>Transferred</option>
                    <option value="withdrawn" {{ $student->status === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                    <option value="graduated" {{ $student->status === 'graduated' ? 'selected' : '' }}>Graduated</option>
                </select>
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold px-3 py-2 rounded-xl transition">
                    Update Status
                </button>
            </form>
            @if($student->hasActiveQrToken())
                <form action="{{ route('school-admin.students.qr-token.rotate', $student) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">Replace QR</button>
                </form>
                <form action="{{ route('school-admin.students.qr-token.revoke', $student) }}" method="POST" onsubmit="return confirm('Revoke this student QR code? Printed copies will stop working.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">Revoke QR</button>
                </form>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs (7 tabs as required by blueprint) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-1.5 flex flex-wrap gap-1 text-xs font-semibold text-slate-500">
        <button @click="tab = 'details'" :class="tab === 'details' ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-50 hover:text-slate-700'" class="px-4 py-2.5 rounded-xl transition">
            <i class="fa-solid fa-id-card mr-1.5"></i> Details
        </button>
        <button @click="tab = 'guardians'" :class="tab === 'guardians' ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-50 hover:text-slate-700'" class="px-4 py-2.5 rounded-xl transition">
            <i class="fa-solid fa-users mr-1.5"></i> Guardians ({{ $student->guardians->count() }})
        </button>
        <button @click="tab = 'enrollment'" :class="tab === 'enrollment' ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-50 hover:text-slate-700'" class="px-4 py-2.5 rounded-xl transition">
            <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> Enrollment History
        </button>
        <button @click="tab = 'attendance'" :class="tab === 'attendance' ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-50 hover:text-slate-700'" class="px-4 py-2.5 rounded-xl transition">
            <i class="fa-solid fa-calendar-check mr-1.5"></i> Attendance ({{ $student->attendances->count() }})
        </button>
        <button @click="tab = 'fees'" :class="tab === 'fees' ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-50 hover:text-slate-700'" class="px-4 py-2.5 rounded-xl transition">
            <i class="fa-solid fa-file-invoice-dollar mr-1.5"></i> Fee Ledger
        </button>
        <button @click="tab = 'exams'" :class="tab === 'exams' ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-50 hover:text-slate-700'" class="px-4 py-2.5 rounded-xl transition">
            <i class="fa-solid fa-square-poll-vertical mr-1.5"></i> Exams & Marks
        </button>
        <button @click="tab = 'documents'" :class="tab === 'documents' ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-50 hover:text-slate-700'" class="px-4 py-2.5 rounded-xl transition">
            <i class="fa-solid fa-folder-closed mr-1.5"></i> Documents
        </button>
    </div>

    <!-- Tab 1: Details -->
    <div x-show="tab === 'details'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-4">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Demographics & Student Profile</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs">
            <div>
                <dt class="font-semibold text-slate-400 uppercase">First Name</dt>
                <dd class="text-slate-800 text-sm font-semibold mt-1">{{ $student->first_name }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-slate-400 uppercase">Last Name</dt>
                <dd class="text-slate-800 text-sm font-semibold mt-1">{{ $student->last_name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-slate-400 uppercase">Gender</dt>
                <dd class="text-slate-800 text-sm font-semibold mt-1 capitalize">{{ $student->gender }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-slate-400 uppercase">Date of Birth</dt>
                <dd class="text-slate-800 text-sm font-semibold mt-1">{{ $student->date_of_birth?->format('M d, Y') ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-slate-400 uppercase">Admission Date</dt>
                <dd class="text-slate-800 text-sm font-semibold mt-1">{{ $student->admission_date?->format('M d, Y') ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-slate-400 uppercase">Emergency Phone</dt>
                <dd class="text-slate-800 text-sm font-semibold mt-1">{{ $student->emergency_phone ?? 'N/A' }}</dd>
            </div>
            <div class="sm:col-span-3">
                <dt class="font-semibold text-slate-400 uppercase">Residential Address</dt>
                <dd class="text-slate-800 text-sm font-semibold mt-1">{{ $student->address ?? 'N/A' }}</dd>
            </div>
        </dl>
    </div>

    <!-- Tab 2: Guardians -->
    <div x-show="tab === 'guardians'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Linked Parents & Guardians</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse($student->guardians as $g)
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-2">
                    <div class="flex justify-between items-center font-bold text-slate-900 text-sm">
                        <span>{{ $g->name }}</span>
                        <span class="bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded text-[10px]">{{ $g->relationship }}</span>
                    </div>
                    <div class="text-slate-600">Phone: <strong>{{ $g->phone ?? '-' }}</strong></div>
                    <div class="text-slate-600">Email: <strong>{{ $g->email ?? '-' }}</strong></div>
                    <div class="text-slate-500 text-[11px]">{{ $g->address }}</div>
                </div>
            @empty
                <div class="text-slate-400 text-xs py-4 text-center sm:col-span-2">No guardian linked.</div>
            @endforelse
        </div>
    </div>

    <!-- Tab 3: Enrollment History -->
    <div x-show="tab === 'enrollment'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Historical Class Placements (Preserved Across Years)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase font-semibold text-slate-500">
                    <tr>
                        <th class="p-3">Academic Session</th>
                        <th class="p-3">Class & Section</th>
                        <th class="p-3">Roll #</th>
                        <th class="p-3">Placement Date</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($student->enrollments as $enr)
                        <tr>
                            <td class="p-3 font-semibold text-slate-900">{{ $enr->academicYear->name }}</td>
                            <td class="p-3">{{ $enr->class->name }} ({{ $enr->section->name }})</td>
                            <td class="p-3 font-mono">{{ $enr->roll_number ?? '-' }}</td>
                            <td class="p-3 text-slate-400">{{ $enr->enrolled_at->format('Y-m-d') }}</td>
                            <td class="p-3">
                                <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded capitalize font-medium">{{ $enr->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-3 text-center text-slate-400">No enrollment records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 4: Attendance -->
    <div x-show="tab === 'attendance'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h2 class="text-base font-bold text-slate-900">Attendance Log</h2>
            <span class="text-xs bg-emerald-50 text-emerald-800 px-3 py-1 rounded-full font-bold">Calculation: {{ $attendancePercentage }}% Eligible Sessions</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase font-semibold text-slate-500">
                    <tr>
                        <th class="p-3">Date</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Marked At</th>
                        <th class="p-3">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($student->attendances as $att)
                        <tr>
                            <td class="p-3 font-semibold text-slate-800">{{ $att->session->attendance_date?->format('Y-m-d') }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $att->status === 'present' ? 'bg-emerald-100 text-emerald-800' : ($att->status === 'absent' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $att->status }}
                                </span>
                            </td>
                            <td class="p-3 text-slate-400">{{ $att->marked_at?->format('H:i') }}</td>
                            <td class="p-3 text-slate-500">{{ $att->remarks ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-3 text-center text-slate-400">No attendance marked yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 5: Fee Ledger -->
    <div x-show="tab === 'fees'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Student Fee Invoices & Payment Ledger</h2>

        <div class="space-y-3">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Invoices Issued</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 uppercase font-semibold text-slate-500">
                        <tr>
                            <th class="p-3">Invoice #</th>
                            <th class="p-3">Period</th>
                            <th class="p-3">Due Date</th>
                            <th class="p-3">Total Amount</th>
                            <th class="p-3">Paid Amount</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($student->feeInvoices as $inv)
                            <tr>
                                <td class="p-3 font-mono font-bold text-indigo-700">{{ $inv->invoice_number }}</td>
                                <td class="p-3 font-medium text-slate-800">{{ $inv->billing_period }}</td>
                                <td class="p-3">{{ $inv->due_date->format('Y-m-d') }}</td>
                                <td class="p-3 font-bold text-slate-900">${{ number_format($inv->total_amount, 2) }}</td>
                                <td class="p-3 font-semibold text-emerald-700">${{ number_format($inv->paid_amount, 2) }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                                <td class="p-3 text-right">
                                    <a href="{{ route('school-admin.fees.invoices.show', $inv) }}" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded text-[11px] font-semibold">
                                        Voucher
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-3 text-center text-slate-400">No invoices on ledger.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 pt-4 border-t border-slate-100">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Payment Receipts</h3>
            <div class="divide-y divide-slate-100">
                @forelse($student->feePayments as $pay)
                    <div class="py-2.5 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-mono font-bold text-slate-800">{{ $pay->receipt_number }}</div>
                            <div class="text-[11px] text-slate-400">{{ $pay->payment_date->format('Y-m-d') }} • Method: {{ strtoupper($pay->method) }}</div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="font-bold text-emerald-600 text-sm">${{ number_format($pay->amount, 2) }}</span>
                            <a href="{{ route('school-admin.fees.receipt', $pay) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Receipt</a>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 py-2">No payment transactions recorded.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Tab 6: Exams & Marks -->
    <div x-show="tab === 'exams'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Exams & Academic Marks</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase font-semibold text-slate-500">
                    <tr>
                        <th class="p-3">Exam Term</th>
                        <th class="p-3">Subject</th>
                        <th class="p-3">Max Marks</th>
                        <th class="p-3">Marks Obtained</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($student->marks as $m)
                        <tr>
                            <td class="p-3 font-semibold text-slate-900">{{ $m->examSubject->exam->name }}</td>
                            <td class="p-3">{{ $m->examSubject->classSubject->subject->name }}</td>
                            <td class="p-3 font-mono">{{ $m->examSubject->max_marks }}</td>
                            <td class="p-3 font-bold text-indigo-700 text-sm">{{ $m->marks_obtained ?? '-' }}</td>
                            <td class="p-3">
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] uppercase font-semibold">{{ $m->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-3 text-center text-slate-400">No exam marks entered for this student.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 7: Documents -->
    <div x-show="tab === 'documents'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Private Student Documents</h2>
        <div class="p-4 bg-slate-50 rounded-xl text-xs text-slate-500 text-center">
            Private document vault: Files stored securely off-public web root with verified token authentication.
        </div>
    </div>

</div>
@endsection
