@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">My Student Profile</h1>
        <p class="mt-1 text-sm text-slate-500">Your profile and current school placement.</p>
    </div>

    @include('student.partials.navigation')

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="border-b border-slate-100 pb-3 text-base font-bold text-slate-900">Personal information</h2>
        <dl class="mt-5 grid grid-cols-1 gap-5 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Full name</dt><dd class="mt-1 font-semibold text-slate-800">{{ $student->full_name }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Admission number</dt><dd class="mt-1 font-mono font-semibold text-slate-800">{{ $student->admission_number }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Status</dt><dd class="mt-1 capitalize text-slate-800">{{ $student->status }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Date of birth</dt><dd class="mt-1 text-slate-800">{{ $student->date_of_birth?->format('d M Y') ?? 'Not recorded' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Gender</dt><dd class="mt-1 capitalize text-slate-800">{{ $student->gender }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Admission date</dt><dd class="mt-1 text-slate-800">{{ $student->admission_date?->format('d M Y') ?? 'Not recorded' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Address</dt><dd class="mt-1 text-slate-800">{{ $student->address ?: 'Not recorded' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-400">Emergency phone</dt><dd class="mt-1 text-slate-800">{{ $student->emergency_phone ?: 'Not recorded' }}</dd></div>
        </dl>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="border-b border-slate-100 pb-3 text-base font-bold text-slate-900">Class and section</h2>
        @if($student->currentEnrollment)
            <dl class="mt-5 grid grid-cols-1 gap-5 text-sm sm:grid-cols-2 lg:grid-cols-4">
                <div><dt class="text-xs font-semibold uppercase text-slate-400">Academic year</dt><dd class="mt-1 text-slate-800">{{ $student->currentEnrollment->academicYear?->name ?? 'Not recorded' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase text-slate-400">Class</dt><dd class="mt-1 text-slate-800">{{ $student->currentEnrollment->class?->name ?? 'Not recorded' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase text-slate-400">Section</dt><dd class="mt-1 text-slate-800">{{ $student->currentEnrollment->section?->name ?? 'Not recorded' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase text-slate-400">Roll number</dt><dd class="mt-1 font-mono text-slate-800">{{ $student->currentEnrollment->roll_number ?? 'Not recorded' }}</dd></div>
            </dl>
        @else
            <p class="mt-4 text-sm text-slate-500">No current class enrollment is recorded.</p>
        @endif
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="border-b border-slate-100 pb-3 text-base font-bold text-slate-900">Parent / guardian contacts</h2>
        <div class="mt-4 divide-y divide-slate-100">
            @forelse($guardians as $guardian)
                <div class="py-3">
                    <p class="font-semibold text-slate-800">{{ $guardian->name }} <span class="text-xs font-normal text-slate-500">({{ $guardian->relationship }})</span></p>
                    <p class="mt-1 text-sm text-slate-600">{{ $guardian->phone ?: 'No phone recorded' }} <span class="mx-1 text-slate-300">·</span> {{ $guardian->email ?: 'No email recorded' }}</p>
                </div>
            @empty
                <p class="pt-4 text-sm text-slate-500">No guardian contacts are linked to your profile.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
