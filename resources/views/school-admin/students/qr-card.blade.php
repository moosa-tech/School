@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3 no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Student QR ID card</h1>
            <p class="text-sm text-slate-500">Reprints use the same active code unless it is replaced or revoked.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="window.print()" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Print card</button>
            <a href="{{ route('school-admin.students.show', $student) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">Back to student</a>
        </div>
    </div>

    <article class="mx-auto flex min-h-[360px] max-w-xl items-center gap-6 rounded-3xl border-2 border-indigo-900 bg-white p-7 shadow-xl print:min-h-[300px] print:shadow-none">
        <div class="min-w-0 flex-1 space-y-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-700">Student identification</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900">{{ $school->name }}</h2>
            </div>
            <div class="space-y-2 text-sm">
                <p class="text-xl font-bold text-slate-900">{{ $student->full_name }}</p>
                <p class="text-slate-600">Admission: <strong class="font-mono text-slate-900">{{ $student->admission_number }}</strong></p>
                <p class="text-slate-600">Class: <strong class="text-slate-900">{{ $student->currentEnrollment?->class?->name ?? 'Not enrolled' }}</strong></p>
                <p class="text-slate-600">Section: <strong class="text-slate-900">{{ $student->currentEnrollment?->section?->name ?? '—' }}</strong></p>
            </div>
            <p class="text-xs text-slate-500">If found, please return this card to the school office.</p>
        </div>
        @if($student->photo_path)
            <img src="{{ asset('storage/'.$student->photo_path) }}" alt="Student photo" class="h-20 w-20 rounded-xl object-cover">
        @endif
        <div class="shrink-0 text-center">
            {!! $qrSvg !!}
            <p class="mt-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Scan at school</p>
        </div>
    </article>
</div>
<style>
    @media print {
        body { background: white !important; }
        article { break-inside: avoid; }
    }
</style>
@endsection
