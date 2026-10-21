@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Import Marks from Excel</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $examSubject->exam->academicYear->name }} · {{ $examSubject->exam->name }} ·
                {{ $examSubject->classSubject->class->name }} · {{ $examSubject->classSubject->subject->name }}
            </p>
            <p class="mt-1 text-xs text-slate-500">Maximum {{ number_format($examSubject->max_marks, 2) }} marks. Imports are limited to 5,000 rows.</p>
        </div>
        <a href="{{ route('school-admin.exams.marks.entry', $examSubject) }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Back to Marks</a>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @if($examSubject->exam->status === 'locked')
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            This exam is locked. Downloading a template is available, but marks cannot be imported.
        </div>
    @endif

    @if($report)
        <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <h2 class="text-base font-bold text-slate-900">Import Report</h2>
                <p class="mt-1 text-sm text-slate-500">The database transaction completed successfully.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach(['created' => 'Created', 'updated' => 'Updated', 'skipped' => 'Skipped', 'rejected' => 'Rejected'] as $key => $label)
                    <div class="rounded-xl bg-slate-50 p-4 text-center">
                        <div class="text-2xl font-bold text-slate-900">{{ $report['result'][$key] }}</div>
                        <div class="text-xs font-semibold uppercase text-slate-500">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
            @if($report['result']['errors'] !== [])
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-amber-800">Some rows were skipped or rejected. Download the row numbers and reasons.</p>
                    <form action="{{ route('school-admin.exams.marks.import.errors', $examSubject) }}" method="POST">
                        @csrf
                        <input type="hidden" name="import_token" value="{{ $reportToken }}">
                        <button type="submit" class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">Download Error Report (CSV)</button>
                    </form>
                </div>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full min-w-[36rem] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                            <tr><th class="px-4 py-3">Row</th><th class="px-4 py-3">Admission Number</th><th class="px-4 py-3">Reason</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($report['result']['errors'] as $error)
                                <tr>
                                    <td class="px-4 py-3">{{ $error['row_number'] }}</td>
                                    <td class="px-4 py-3 font-mono">{{ $error['admission_number'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-rose-700">{{ $error['reason'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <h2 class="text-base font-bold text-slate-900">1. Download a Marks Template</h2>
                <p class="mt-1 text-sm text-slate-500">The file includes active students enrolled in this class and academic year. Match students by admission number, not by name.</p>
            </div>
            <form action="{{ route('school-admin.exams.marks.template', $examSubject) }}" method="GET" class="space-y-3">
                <label class="block text-xs font-semibold uppercase text-slate-600" for="template_section">Section (optional)</label>
                <select id="template_section" name="section_id" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                    <option value="">All sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
                <label class="block text-xs font-semibold uppercase text-slate-600" for="template_format">Format</label>
                <select id="template_format" name="format" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                    <option value="xlsx">Excel (.xlsx)</option>
                    <option value="csv">CSV (.csv)</option>
                </select>
                <button type="submit" class="w-full rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Download Template</button>
            </form>
            <p class="text-xs text-slate-500">Required columns: admission_number, student_name, section, obtained_marks, status.</p>
        </div>

        <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <h2 class="text-base font-bold text-slate-900">2. Upload and Preview</h2>
                <p class="mt-1 text-sm text-slate-500">Valid rows, duplicate identifiers, unmatched students, mark limits, and existing marks are checked before anything is saved.</p>
            </div>
            <form action="{{ route('school-admin.exams.marks.import.preview', $examSubject) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase text-slate-600" for="import_section">Section (optional)</label>
                    <select id="import_section" name="section_id" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                        <option value="">All sections</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" @selected(old('section_id') == $section->id)>{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase text-slate-600" for="file">Marks spreadsheet (.xlsx or .csv)</label>
                    <input id="file" type="file" name="file" accept=".xlsx,.csv" required @disabled($examSubject->exam->status === 'locked') class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-slate-500">Maximum file size: 10 MB. Maximum data rows: 5,000.</p>
                </div>
                <button type="submit" @disabled($examSubject->exam->status === 'locked') class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">Validate and Preview</button>
            </form>
            <a href="{{ route('school-admin.exams.marks.export', $examSubject) }}" class="block text-center text-sm font-semibold text-indigo-700 underline">Export current marks (XLSX)</a>
        </div>
    </section>
</div>
@endsection
