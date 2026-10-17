@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Teacher Daily Attendance</h1>
            <p class="text-sm text-slate-500">Teacher and student attendance are managed separately.</p>
        </div>
        <a href="{{ route('school-admin.staff-attendance.register') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700">Monthly Teacher Register</a>
    </div>

    <form method="GET" action="{{ route('school-admin.staff-attendance.index') }}" class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        <label for="attendance-date" class="text-xs font-semibold text-slate-600">Attendance date
            <input id="attendance-date" type="date" name="date" value="{{ $date }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 sm:w-56">
        </label>
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Load Teachers</button>
    </form>

    @if($employees->isNotEmpty())
        <form method="POST" action="{{ route('school-admin.staff-attendance.save') }}" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="font-bold text-slate-900">{{ $employees->count() }} Active Teachers</h2>
                    <p class="text-xs text-slate-500">Date: {{ \Carbon\Carbon::parse($date)->format('F j, Y') }} · Unmarked values must be selected before saving.</p>
                </div>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full min-w-[760px] text-left text-sm text-slate-700">
                    <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                        <tr><th class="px-3 py-3">Teacher code</th><th class="px-3 py-3">Teacher name</th><th class="px-3 py-3">Attendance</th><th class="px-3 py-3">Remarks</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($employees as $employee)
                            @php
                                $record = $existingRecords->get($employee->id);
                                $status = $record?->status === 'excused' ? 'leave' : $record?->status;
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap px-3 py-3 font-mono font-semibold">{{ $employee->employee_number }}</td>
                                <td class="whitespace-nowrap px-3 py-3 font-semibold">{{ $employee->name }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'leave' => 'Leave'] as $value => $label)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="attendance[{{ $employee->id }}][status]" value="{{ $value }}" @checked($status === $value) required class="peer sr-only">
                                                <span class="inline-block rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs text-slate-600 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <input type="text" name="attendance[{{ $employee->id }}][notes]" value="{{ $record?->notes }}" maxlength="1000" class="w-full min-w-40 rounded-lg border border-slate-200 px-2.5 py-2 text-xs" placeholder="Optional remarks">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-700 sm:w-auto">Save Teacher Attendance</button>
            </div>
        </form>
    @else
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">No active teacher employees are configured for this school.</div>
    @endif
</div>
@endsection
