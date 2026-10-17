@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $school->name }} Dashboard</h1>
            <p class="text-slate-500 text-sm">
                Current Session: <span class="font-bold text-indigo-700">{{ $currentYear?->name ?? 'No active session' }}</span>
                • Tenant Currency: <span class="font-bold text-slate-700">{{ $school->currency }}</span>
            </p>
        </div>
        <div class="flex items-center space-x-3">
            @if(in_array(auth()->user()->schoolRole(), ['school_admin', 'principal']))
                <a href="{{ route('school-admin.students.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3.5 py-2.5 rounded-xl shadow-sm transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>New Student Admission</span>
                </a>
            @endif
            @if(in_array(auth()->user()->schoolRole(), ['school_admin', 'principal', 'accountant']))
                <a href="{{ route('school-admin.fees.invoices.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-2.5 rounded-xl shadow-sm transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Generate Invoices</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Quick Stats Cards (4) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Active Students -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Students</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($studentCount) }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ $classCount }} Classes • {{ $sectionCount }} Sections</div>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 text-xl">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>

        <!-- Teachers & Staff -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Teachers & Staff</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($employeeCount) }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ $teacherCount }} Faculty Instructors</div>
            </div>
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 text-xl">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Attendance</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $attendanceStats['present'] }} <span class="text-xs font-normal text-slate-400">Present</span></div>
                <div class="text-xs text-slate-500 mt-1">
                    <span class="text-rose-600 font-semibold">{{ $attendanceStats['absent'] }} Absent</span> • {{ $attendanceStats['late'] }} Late
                </div>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 text-xl">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
        </div>

        <!-- Fee Collection Month -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Fees Collected (Month)</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $school->currency }} {{ number_format($monthCollection, 0) }}</div>
                <div class="text-xs text-rose-500 font-medium mt-1">
                    {{ $school->currency }} {{ number_format($totalDues, 0) }} Total Dues
                </div>
            </div>
            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600 text-xl">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>
    </div>

    <!-- Attendance Action & Financial Highlights -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Attendance Breakdown -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Attendance Today</h2>
                <a href="{{ route('school-admin.attendance.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Roll Call</a>
            </div>

            <div class="grid grid-cols-2 gap-3 text-center">
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                    <div class="text-2xl font-bold text-emerald-800">{{ $attendanceStats['present'] }}</div>
                    <div class="text-[11px] text-emerald-600 uppercase font-semibold">Present</div>
                </div>
                <div class="p-3 bg-rose-50 rounded-xl border border-rose-100">
                    <div class="text-2xl font-bold text-rose-800">{{ $attendanceStats['absent'] }}</div>
                    <div class="text-[11px] text-rose-600 uppercase font-semibold">Absent</div>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-100">
                    <div class="text-2xl font-bold text-amber-800">{{ $attendanceStats['late'] }}</div>
                    <div class="text-[11px] text-amber-600 uppercase font-semibold">Late</div>
                </div>
                <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="text-2xl font-bold text-blue-800">{{ $attendanceStats['excused'] }}</div>
                    <div class="text-[11px] text-blue-600 uppercase font-semibold">Excused</div>
                </div>
            </div>

            <div class="pt-2 text-xs text-slate-500 flex items-center justify-between">
                <span>Unfinalized Sessions:</span>
                <span class="font-bold text-amber-600">{{ $attendanceStats['unfinalized_sessions'] }}</span>
            </div>
        </div>

        <!-- Recent Invoices -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Recent Fee Invoices</h2>
                <a href="{{ route('school-admin.fees.invoices') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">All Invoices</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentInvoices as $inv)
                    <div class="py-2.5 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-semibold text-slate-800">{{ $inv->student->full_name }}</div>
                            <div class="text-slate-400 font-mono text-[10px]">{{ $inv->invoice_number }} • {{ $inv->billing_period }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-slate-900">{{ $school->currency }} {{ number_format($inv->total_amount, 0) }}</div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($inv->status === 'partial' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ ucfirst($inv->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400 text-xs">No invoices generated yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Notices & Announcements -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Latest Notices</h2>
                <a href="{{ route('school-admin.announcements.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">All Notices</a>
            </div>

            <div class="space-y-3">
                @forelse($recentAnnouncements as $notice)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                        <div class="font-bold text-slate-900">{{ $notice->title }}</div>
                        <div class="text-slate-500 mt-1 line-clamp-2">{{ $notice->body }}</div>
                        <div class="mt-2 text-[10px] text-slate-400 flex items-center justify-between">
                            <span class="capitalize">Audience: {{ $notice->audience }}</span>
                            <span>{{ $notice->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400 text-xs">No notices posted.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
