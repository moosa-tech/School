@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="mb-2">
        <h1 class="text-2xl font-bold text-slate-900">Student Dashboard</h1>
    </div>

    @if(! $student)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-4 text-sm">
            This student login is not linked to a student record in the active school.
        </div>
    @endif

    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-indigo-900 rounded-2xl p-6 text-white shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Welcome, {{ $student->first_name ?? auth()->user()->name }}! 🎓</h1>
                <p class="text-blue-100 text-sm mt-1">Student Portal &middot; {{ now()->format('l, d M Y') }}</p>
                @if($student && $student->currentEnrollment)
                    <p class="text-blue-200 text-xs mt-1">
                        Class: <strong>{{ $student->currentEnrollment->class->name ?? '' }}</strong>
                        @if($student->currentEnrollment->section)
                            - Section <strong>{{ $student->currentEnrollment->section->name }}</strong>
                        @endif
                        &middot; Roll Number: <span class="bg-white/20 px-2 py-0.5 rounded font-mono font-bold">{{ $student->currentEnrollment->roll_number ?? 'N/A' }}</span>
                        &middot; Admission No: <span class="font-mono">{{ $student->admission_number }}</span>
                    </p>
                @endif
            </div>
            <div class="hidden sm:block">
                <i class="fa-solid fa-graduation-cap text-5xl text-blue-300 opacity-80"></i>
            </div>
        </div>
    </div>

    @if($student)
        @include('student.partials.navigation')
    @endif

    {{-- Stats Grid (Matching user's PHP project: Attendance %, Present, Absent, Pending Fees) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm text-center">
            <div class="text-2xl font-black text-indigo-600">{{ $attendanceStats['rate'] }}%</div>
            <div class="text-xs font-semibold text-slate-500 uppercase mt-1">Attendance Rate</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm text-center">
            <div class="text-2xl font-black text-emerald-600">{{ $attendanceStats['present'] }}</div>
            <div class="text-xs font-semibold text-slate-500 uppercase mt-1">Days Present</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm text-center">
            <div class="text-2xl font-black text-rose-600">{{ $attendanceStats['absent'] }}</div>
            <div class="text-xs font-semibold text-slate-500 uppercase mt-1">Days Absent</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm text-center">
            <div class="text-2xl font-black text-amber-600">Rs. {{ number_format($feeStats['pending'], 0) }}</div>
            <div class="text-xs font-semibold text-slate-500 uppercase mt-1">Fee Balance Due</div>
        </div>
    </div>

    {{-- Tabs or Sections: Attendance, Marks, Fees, Timetable --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- 1. My Attendance History --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 text-sm flex items-center">
                    <i class="fa-solid fa-calendar-check text-emerald-600 mr-2"></i> My Recent Attendance
                </h2>
                <span class="text-xs text-slate-500">{{ $attendanceStats['total'] }} recorded days</span>
            </div>
            <div class="overflow-x-auto max-h-72">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-100">
                        <tr>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4">Status</th>
                            <th class="py-2.5 px-4">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentAttendance as $att)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-4 font-medium text-slate-700">
                                    {{ $att->session?->attendance_date ? \Carbon\Carbon::parse($att->session->attendance_date)->format('d M Y') : $att->created_at->format('d M Y') }}
                                </td>
                                <td class="py-2.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold capitalize {{ $att->status === 'present' ? 'bg-emerald-100 text-emerald-800' : ($att->status === 'absent' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $att->status }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-slate-400">{{ $att->remarks ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-slate-400">
                                    No attendance records found yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 2. My Exam Marks --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 text-sm flex items-center">
                    <i class="fa-solid fa-square-poll-vertical text-indigo-600 mr-2"></i> My Exam Results
                </h2>
                <span class="text-xs text-slate-500">{{ $marks->count() }} results</span>
            </div>
            <div class="overflow-x-auto max-h-72">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-100">
                        <tr>
                            <th class="py-2.5 px-4">Subject</th>
                            <th class="py-2.5 px-4">Exam</th>
                            <th class="py-2.5 px-4 text-right">Marks</th>
                            <th class="py-2.5 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($marks as $m)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-4 font-semibold text-slate-800">{{ $m->examSubject?->classSubject?->subject?->name ?? 'N/A' }}</td>
                                <td class="py-2.5 px-4 text-slate-600">{{ $m->examSubject?->exam?->name ?? 'Exam' }}</td>
                                <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900">
                                    {{ $m->marks_obtained }} / {{ $m->examSubject?->max_marks ?? 100 }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 capitalize">
                                        {{ $m->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">
                                    No exam marks published yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 3. My Fees Ledger --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 text-sm flex items-center">
                    <i class="fa-solid fa-receipt text-amber-600 mr-2"></i> My Fee Invoices
                </h2>
                <div class="text-xs text-slate-500">
                    Paid: <strong class="text-emerald-600">Rs. {{ number_format($feeStats['paid'], 0) }}</strong>
                </div>
            </div>
            <div class="overflow-x-auto max-h-72">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-100">
                        <tr>
                            <th class="py-2.5 px-4">Invoice #</th>
                            <th class="py-2.5 px-4">Period</th>
                            <th class="py-2.5 px-4 text-right">Amount</th>
                            <th class="py-2.5 px-4 text-right">Paid</th>
                            <th class="py-2.5 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($feeInvoices as $inv)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-4 font-mono font-semibold text-slate-800">{{ $inv->invoice_number }}</td>
                                <td class="py-2.5 px-4 text-slate-600">{{ $inv->billing_period ?? $inv->issue_date?->format('M Y') }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-slate-900">Rs. {{ number_format($inv->total_amount, 0) }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-emerald-600">Rs. {{ number_format($inv->paid_amount, 0) }}</td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold capitalize {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($inv->status === 'partial' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    No fee invoices found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 4. My Homework & Timetable --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 text-sm flex items-center">
                    <i class="fa-solid fa-book-bookmark text-blue-600 mr-2"></i> Assigned Homework
                </h2>
                <span class="text-xs text-slate-500">{{ $homework->count() }} items</span>
            </div>
            <div class="p-4 space-y-3">
                @forelse($homework as $hw)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-start justify-between">
                        <div>
                            <div class="text-xs font-bold text-indigo-700 uppercase">{{ $hw->classSubject?->subject?->name ?? 'Subject' }}</div>
                            <div class="text-sm font-semibold text-slate-800 mt-0.5">{{ $hw->title }}</div>
                            @if($hw->description)
                                <div class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $hw->description }}</div>
                            @endif
                        </div>
                        <div class="text-right flex-shrink-0 ml-3">
                            <span class="text-[10px] font-semibold px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full">
                                Due: {{ \Carbon\Carbon::parse($hw->due_on)->format('d M') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-circle-check text-3xl text-emerald-400 mb-2 block"></i>
                        No pending homework!
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Announcements Notice Board --}}
    @if($announcements->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="font-bold text-slate-900 text-base mb-4 flex items-center">
                <i class="fa-solid fa-bullhorn text-amber-500 mr-2"></i> School Notice Board
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($announcements as $ann)
                    <div class="p-4 bg-amber-50/60 border border-amber-200/70 rounded-xl">
                        <div class="text-xs text-amber-700 font-bold uppercase tracking-wider">{{ $ann->created_at->format('d M Y') }}</div>
                        <div class="text-sm font-bold text-slate-900 mt-1">{{ $ann->title }}</div>
                        <div class="text-xs text-slate-700 mt-1">{{ $ann->content }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection