@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">My Results</h1>
        <p class="mt-1 text-sm text-slate-500">Only published or locked exam results are shown.</p>
    </div>

    @include('student.partials.navigation')

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Exam</th><th class="px-5 py-3">Subject</th><th class="px-5 py-3">Exam dates</th><th class="px-5 py-3 text-right">Marks</th><th class="px-5 py-3">Result status</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($marks as $mark)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $mark->examSubject?->exam?->name ?? 'Exam' }}</td>
                            <td class="px-5 py-3">{{ $mark->examSubject?->classSubject?->subject?->name ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $mark->examSubject?->exam?->starts_on?->format('d M Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-right font-mono">{{ $mark->marks_obtained }} / {{ $mark->examSubject?->max_marks ?? '—' }}</td>
                            <td class="px-5 py-3 capitalize">{{ $mark->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No published results are available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">{{ $marks->links() }}</div>
    </section>
</div>
@endsection
