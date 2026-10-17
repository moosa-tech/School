@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Student Admission & Enrollment</h1>
            <p class="text-slate-500 text-sm">Register student record, parent/guardian details, and initial class placement</p>
        </div>
        <a href="{{ route('school-admin.students.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Back to List</a>
    </div>

    <form action="{{ route('school-admin.students.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Student Personal Information -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                <i class="fa-solid fa-user-graduate text-indigo-600"></i>
                <span>1. Personal & Admission Information</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Admission Number *</label>
                    <input type="text" name="admission_number" value="{{ old('admission_number', 'ADM-' . rand(1000, 9999)) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required placeholder="e.g. Ali"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="e.g. Khan"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Gender *</label>
                    <select name="gender" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Admission Date *</label>
                    <input type="date" name="admission_date" value="{{ old('admission_date', now()->toDateString()) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Emergency Phone</label>
                    <input type="text" name="emergency_phone" value="{{ old('emergency_phone') }}" placeholder="0300 1234567"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Residential Address</label>
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="House #, Street, City"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>
        </div>

        <!-- 2. Academic Placement & Enrollment -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                <i class="fa-solid fa-shapes text-indigo-600"></i>
                <span>2. Academic Placement & Enrollment</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Academic Year *</label>
                    <select name="academic_year_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>{{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Class *</label>
                    <select name="class_id" id="classSelect" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Section *</label>
                    <select name="section_id" id="sectionSelect" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                        @foreach($classes as $c)
                            @foreach($c->sections as $sec)
                                <option value="{{ $sec->id }}" data-class="{{ $c->id }}">{{ $c->name }} - {{ $sec->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Roll Number</label>
                    <input type="text" name="roll_number" value="{{ old('roll_number') }}" placeholder="e.g. 15"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>
        </div>

        <!-- 3. Guardian Details -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-5">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                <i class="fa-solid fa-users text-indigo-600"></i>
                <span>3. Primary Guardian Contact</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Guardian Name *</label>
                    <input type="text" name="guardian_name" value="{{ old('guardian_name') }}" required placeholder="e.g. Tariq Mehmood"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Relationship *</label>
                    <select name="guardian_relation" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                        <option value="Father">Father</option>
                        <option value="Mother">Mother</option>
                        <option value="Guardian">Guardian</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Phone Number</label>
                    <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}" placeholder="0321 0000000"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Email</label>
                    <input type="email" name="guardian_email" value="{{ old('guardian_email') }}" placeholder="guardian@email.com"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>
            <label class="flex items-start gap-2 text-xs text-slate-600">
                <input type="checkbox" name="whatsapp_opt_in" value="yes" {{ old('whatsapp_opt_in') ? 'checked' : '' }} class="mt-0.5 rounded text-indigo-600">
                <span>The guardian has agreed to receive account credentials through WhatsApp at this number.</span>
            </label>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('school-admin.students.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md transition flex items-center space-x-2">
                <i class="fa-solid fa-check text-xs"></i>
                <span>Complete Admission</span>
            </button>
        </div>
    </form>

</div>
@endsection
