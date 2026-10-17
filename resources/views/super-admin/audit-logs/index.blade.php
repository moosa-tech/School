@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Platform Security & Audit Trail</h1>
            <p class="text-slate-500 text-sm">Immutable log of security events, school registrations, role changes, and admin activities</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('super-admin.audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <select name="school_id" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                <option value="">All Schools / Global</option>
                @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                @endforeach
            </select>

            <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter action (e.g. login, school_created)..."
                class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">

            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium py-2 px-4 rounded-xl transition">
                    Search Logs
                </button>
                @if(request()->hasAny(['school_id', 'action', 'date_from']))
                    <a href="{{ route('super-admin.audit-logs.index') }}" class="text-xs text-slate-500 hover:text-slate-700 px-2">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Timestamp</th>
                        <th class="px-6 py-4">Actor</th>
                        <th class="px-6 py-4">School Scope</th>
                        <th class="px-6 py-4">Action Event</th>
                        <th class="px-6 py-4">Entity</th>
                        <th class="px-6 py-4">Metadata Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-xs">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-sans">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-6 py-4 font-sans font-semibold text-slate-900">
                                {{ $log->actor?->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4 font-sans">
                                {{ $log->school?->name ?? 'Global Platform' }}
                            </td>
                            <td class="px-6 py-4 font-sans">
                                <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-md font-semibold text-xs">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-sans">
                                {{ $log->entity_type ? "{$log->entity_type} #{$log->entity_id}" : '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 max-w-xs truncate" title="{{ json_encode($log->metadata) }}">
                                {{ $log->metadata ? json_encode($log->metadata) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400 font-sans">No audit events found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
