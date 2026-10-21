@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Review Marks Import</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $examSubject->exam->name }} · {{ $examSubject->classSubject->class->name }} · {{ $examSubject->classSubject->subject->name }}</p>
        </div>
        <a href="{{ route('school-admin.exams.marks.import', $examSubject) }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel and return</a>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach(['total' => 'Spreadsheet Rows', 'valid' => 'Ready', 'conflicts' => 'Existing Marks', 'rejected' => 'Invalid'] as $key => $label)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <div class="text-2xl font-bold text-slate-900">{{ $analysis[$key] }}</div>
                <div class="text-xs font-semibold uppercase text-slate-500">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    @if($analysis['conflicts'] > 0)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Existing marks will not be overwritten automatically. You may explicitly authorize updating every valid conflicting row below.
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[70rem] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Row</th>
                        <th class="px-4 py-3">Admission Number</th>
                        <th class="px-4 py-3">Uploaded Name</th>
                        <th class="px-4 py-3">Matched Student</th>
                        <th class="px-4 py-3">Marks / Status</th>
                        <th class="px-4 py-3">Existing Marks</th>
                        <th class="px-4 py-3">Validation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($analysis['rows'] as $row)
                        <tr>
                            <td class="px-4 py-3">{{ $row['row_number'] }}</td>
                            <td class="px-4 py-3 font-mono">{{ $row['admission_number'] ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $row['uploaded_name'] ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $row['matched_name'] ?? 'Unmatched' }}</td>
                            <td class="px-4 py-3">{{ $row['obtained_marks'] ?? '—' }} / {{ ucfirst($row['status']) }}</td>
                            <td class="px-4 py-3">{{ $row['existing_marks'] ?? 'None' }}</td>
                            <td class="px-4 py-3">
                                @if($row['errors'] !== [])
                                    <ul class="list-inside list-disc text-rose-700">
                                        @foreach($row['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @elseif($row['has_conflict'])
                                    <span class="font-semibold text-amber-700">Conflict — explicit replacement required</span>
                                @else
                                    <span class="font-semibold text-emerald-700">Ready</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <form action="{{ route('school-admin.exams.marks.import.confirm', $examSubject) }}" method="POST" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <input type="hidden" name="import_token" value="{{ $token }}">
        @if($analysis['conflicts'] > 0)
            <label class="flex items-start gap-2 text-sm text-slate-700">
                <input type="checkbox" name="replace_existing" value="1" class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span>I explicitly authorize replacing existing marks for valid conflicting rows. Unchecked conflicts will be skipped.</span>
            </label>
        @endif
        <label class="flex items-start gap-2 text-sm font-semibold text-slate-800">
            <input type="checkbox" name="confirm_import" value="1" required class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <span>I reviewed this preview and confirm importing the valid rows.</span>
        </label>
        <button type="submit" @disabled($analysis['valid'] === 0 && $analysis['conflicts'] === 0) class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">Confirm Import</button>
    </form>
</div>
@endsection
