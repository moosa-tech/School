@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Add Staff Member / Teacher</h1>
            <p class="text-slate-500 text-sm">Create an employee profile and optionally grant portal login access</p>
        </div>
        <a href="{{ route('school-admin.employees.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('school-admin.employees.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Employee # *</label>
                    <input type="text" name="employee_number" value="{{ old('employee_number', 'EMP-' . rand(100, 999)) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Sir Ahmed Khan"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Official Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="ahmed@school.edu"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0300 1234567"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Job Title *</label>
                    <input type="text" name="job_title" value="{{ old('job_title') }}" required placeholder="e.g. Senior Math Teacher"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">System Role *</label>
                    <select name="role" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                        <option value="principal">Principal</option>
                        <option value="teacher">Teacher (Class & Marks access)</option>
                        <option value="accountant">Accountant (Fee & Invoice access)</option>
                        <option value="staff">Administrative Staff</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Monthly Salary *</label>
                    <input type="number" step="0.01" name="salary" value="{{ old('salary', 45000) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Joining Date *</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', now()->toDateString()) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 space-y-3" x-data="{ access: false }">
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="accessCheck" name="give_system_access" value="1" x-model="access" class="rounded text-indigo-600 h-4 w-4">
                    <label for="accessCheck" class="text-sm font-semibold text-slate-800">Grant Portal Login Account</label>
                </div>

                <div x-show="access" x-cloak class="space-y-1">
                    <span class="text-[11px] text-slate-500">A random temporary password will be generated and shown once after account creation.</span>
                    <label class="flex items-start gap-2 text-xs text-slate-600">
                        <input type="checkbox" name="whatsapp_opt_in" value="yes" {{ old('whatsapp_opt_in') ? 'checked' : '' }} class="mt-0.5 rounded text-indigo-600">
                        <span>The employee has agreed to receive account credentials through WhatsApp at this number.</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('school-admin.employees.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md transition flex items-center space-x-2">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>Save Employee</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
