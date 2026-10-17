@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">USB QR attendance scanner</h1>
            <p class="mt-1 text-sm text-slate-500">A USB scanner that types QR contents as keyboard input works without camera access.</p>
        </div>
        <a href="{{ route('school-admin.attendance.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700">Daily attendance register</a>
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        QR codes can be copied. A successful scan confirms only that a valid active card token was presented; it does not prove the student’s physical identity.
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
            <div>
                <h2 class="font-bold text-slate-900">Scan student card</h2>
                <p class="mt-1 text-sm text-slate-500">Connect the USB scanner and click the input. Test mode: paste or type a token with Enter.</p>
            </div>
            <form id="qr-scan-form" class="space-y-3">
                @csrf
                <label for="qr-token-input" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Scanner input</label>
                <input id="qr-token-input" name="token" type="text" autocomplete="off" autocapitalize="off" spellcheck="false" maxlength="64" autofocus
                    class="w-full rounded-xl border border-indigo-300 bg-indigo-50 px-4 py-4 font-mono text-lg tracking-wide text-slate-900 outline-none ring-indigo-200 focus:ring-4"
                    placeholder="Click here, then scan or type a token and press Enter">
                <div class="flex flex-wrap items-center gap-3">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input id="tab-suffix" type="checkbox" class="rounded border-slate-300">
                        My scanner sends Tab instead of Enter
                    </label>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Submit test scan</button>
                </div>
                <p class="text-xs text-slate-500">No webcam or browser camera permission is used. Leave the suffix setting unchecked for scanners configured to send Enter.</p>
            </form>
            <div id="scan-result" role="status" aria-live="polite" class="hidden rounded-xl border p-4"></div>

            <details class="border-t border-slate-100 pt-4">
                <summary class="cursor-pointer text-sm font-semibold text-indigo-700">Manual student search fallback</summary>
                <form id="manual-search-form" class="mt-4 space-y-3">
                    @csrf
                    <label for="manual-search" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Student name or admission number</label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input id="manual-search" name="search" type="search" maxlength="100" required class="min-w-0 flex-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm" placeholder="Search by admission number or name">
                        <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white">Find and record</button>
                    </div>
                    <p class="text-xs text-slate-500">The server checks the active enrollment, school and your assigned-class permissions before recording attendance.</p>
                </form>
            </details>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-bold text-slate-900">Today’s saved scans</h2>
            <p class="mt-1 text-xs text-slate-500">Records persist after refresh. Times use the school timezone.</p>
            <ul class="mt-4 space-y-3">
                @forelse($recentEvents as $event)
                    <li class="rounded-xl bg-slate-50 p-3 text-sm">
                        <p class="font-semibold text-slate-900">{{ $event->student?->full_name ?? 'Student unavailable' }}</p>
                        <p class="text-xs text-slate-500">{{ $event->student?->admission_number }} · {{ $event->scanned_at->copy()->timezone($school->timezone ?: 'Asia/Karachi')->format('g:i:s A') }}</p>
                        <p class="mt-1 text-xs font-semibold {{ $event->processing_status === 'pending_review' ? 'text-amber-700' : 'text-emerald-700' }}">
                            {{ $event->processing_status === 'pending_review' ? 'Entry awaiting attendance review' : ($event->processing_status === 'manual_attendance_exists' ? 'Existing manual entry preserved' : 'Present recorded') }}
                        </p>
                    </li>
                @empty
                    <li class="rounded-xl bg-slate-50 p-3 text-sm text-slate-500">No scans have been recorded today.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>

<script>
    (() => {
        const scanForm = document.getElementById('qr-scan-form');
        const manualForm = document.getElementById('manual-search-form');
        const input = document.getElementById('qr-token-input');
        const tabSuffix = document.getElementById('tab-suffix');
        const result = document.getElementById('scan-result');
        const csrfToken = scanForm.querySelector('input[name="_token"]').value;
        let requestPending = false;

        const refocus = () => window.setTimeout(() => input.focus(), 250);

        const showResult = (payload, ok) => {
            result.className = `rounded-xl border p-4 ${ok ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-rose-200 bg-rose-50 text-rose-900'}`;
            const heading = document.createElement('p');
            heading.className = 'font-bold';
            heading.textContent = payload.message || 'The scan could not be processed.';
            result.replaceChildren(heading);

            if (payload.name) {
                const details = document.createElement('p');
                details.className = 'mt-2 text-sm';
                details.textContent = `${payload.name} · ${payload.admission_number} · ${payload.class || 'No class'} / ${payload.section || 'No section'}`;
                result.append(details);
            }

            if (payload.attendance || payload.recorded_at) {
                const status = document.createElement('p');
                status.className = 'mt-1 text-sm';
                status.textContent = [payload.attendance, payload.recorded_at].filter(Boolean).join(' · ');
                result.append(status);
            }
        };

        const send = async (url, data) => {
            if (requestPending) {
                return;
            }
            requestPending = true;
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(data),
                });
                const payload = await response.json();
                showResult(payload, response.ok && ['success', 'duplicate', 'review_required'].includes(payload.status));
            } catch (error) {
                showResult({message: 'The request failed. Check the connection and try again.'}, false);
            } finally {
                requestPending = false;
                refocus();
            }
        };

        scanForm.addEventListener('submit', event => {
            event.preventDefault();
            const token = input.value.trim();
            input.value = '';
            if (!token) {
                showResult({message: 'Scan or enter a QR token first.'}, false);
                refocus();
                return;
            }
            send(@json(route('school-admin.attendance.qr-scan')), {token});
        });

        input.addEventListener('keydown', event => {
            if (event.key === 'Tab' && tabSuffix.checked && input.value.trim()) {
                event.preventDefault();
                scanForm.requestSubmit();
            }
        });

        manualForm.addEventListener('submit', event => {
            event.preventDefault();
            const search = document.getElementById('manual-search').value.trim();
            send(@json(route('school-admin.attendance.qr-manual')), {search});
            manualForm.reset();
        });

        window.addEventListener('pageshow', refocus);
    })();
</script>
@endsection
