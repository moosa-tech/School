@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between no-print">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Student Progress Report Card</h1>
            <p class="text-xs text-slate-400">{{ $exam->name }} • {{ $student->full_name }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print Report Card</span>
            </button>
            <a href="{{ route('school-admin.students.show', $student) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700 px-2">Student Profile</a>
        </div>
    </div>

    <!-- Official Report Card Sheet -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-md p-8 sm:p-10 space-y-6">
        <!-- School Crest & Header -->
        <div class="text-center border-b border-slate-200 pb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-900 text-amber-400 rounded-xl mb-2 text-xl font-bold">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-900 uppercase tracking-wide">{{ $exam->school->name }}</h2>
            <p class="text-xs text-slate-500 font-medium">{{ $exam->school->address ?? 'Main Campus' }} • Official Academic Transcript</p>
            <div class="inline-block bg-indigo-50 text-indigo-800 text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full mt-2">
                {{ $exam->name }} ({{ $exam->academicYear->name }})
            </div>
        </div>

        <!-- Student Particulars -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 uppercase font-semibold">Student Name:</span>
                <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $student->full_name }}</div>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold">Admission #:</span>
                <div class="font-mono font-bold text-slate-900 mt-0.5">{{ $student->admission_number }}</div>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold">Current Class:</span>
                <div class="font-semibold text-slate-900 mt-0.5">{{ $student->currentEnrollment?->class?->name ?? 'N/A' }} ({{ $student->currentEnrollment?->section?->name ?? 'A' }})</div>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold">Roll Number:</span>
                <div class="font-bold text-slate-900 mt-0.5">{{ $student->currentEnrollment?->roll_number ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Subjects & Marks Table -->
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-100 uppercase text-slate-700 font-bold">
                <tr>
                    <th class="p-3">#</th>
                    <th class="p-3">Subject Name</th>
                    <th class="p-3 text-center">Max Marks</th>
                    <th class="p-3 text-center">Passing Marks</th>
                    <th class="p-3 text-center">Marks Obtained</th>
                    <th class="p-3 text-right">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($marks as $idx => $m)
                    @php
                        $pass = ($m->marks_obtained ?? 0) >= $m->examSubject->passing_marks;
                    @endphp
                    <tr>
                        <td class="p-3 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="p-3 font-semibold text-slate-800">{{ $m->examSubject->classSubject->subject->name }}</td>
                        <td class="p-3 text-center font-mono">{{ $m->examSubject->max_marks }}</td>
                        <td class="p-3 text-center font-mono">{{ $m->examSubject->passing_marks }}</td>
                        <td class="p-3 text-center font-mono font-bold text-sm {{ $pass ? 'text-indigo-700' : 'text-rose-600' }}">
                            {{ $m->marks_obtained ?? 'AB' }}
                        </td>
                        <td class="p-3 text-right">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $pass ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $pass ? 'Passed' : 'Failed' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-slate-200 text-xs font-bold">
                <tr class="bg-slate-50">
                    <td colspan="2" class="p-3 text-slate-900">Cumulative Summary:</td>
                    <td class="p-3 text-center font-mono">{{ $totalMax }}</td>
                    <td class="p-3 text-center">-</td>
                    <td class="p-3 text-center font-mono text-base text-indigo-700">{{ $totalObtained }}</td>
                    <td class="p-3 text-right font-bold text-emerald-700">{{ $percentage }}% (Grade {{ $grade }})</td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures & Official Badging -->
        <div class="grid grid-cols-3 gap-6 pt-16 border-t border-slate-200 text-xs text-center">
            <div>
                <div class="border-b border-slate-300 w-32 mx-auto mb-1"></div>
                <span class="text-slate-400">Class Incharge</span>
            </div>
            <div>
                <div class="border-b border-slate-300 w-32 mx-auto mb-1"></div>
                <span class="text-slate-400">Controller of Exams</span>
            </div>
            <div>
                <div class="border-b border-slate-300 w-32 mx-auto mb-1"></div>
                <span class="text-slate-400">Principal Stamp</span>
            </div>
        </div>
    </div>

</div>
@endsection
