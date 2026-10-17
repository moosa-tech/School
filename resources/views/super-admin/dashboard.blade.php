@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Platform Control Hub</h1>
            <p class="text-slate-500 text-sm">Multi-School SaaS platform status, subscriptions, and tenant overview</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('super-admin.schools.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition flex items-center space-x-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add New School</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Schools -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Schools</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $schoolCounts['total'] }}</div>
                <div class="text-xs text-emerald-600 mt-1 flex items-center">
                    <span class="font-semibold">{{ $schoolCounts['active'] }} Active</span>
                    <span class="mx-1 text-slate-300">•</span>
                    <span class="text-slate-500">{{ $schoolCounts['pending'] }} Pending</span>
                </div>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 text-xl">
                <i class="fa-solid fa-school"></i>
            </div>
        </div>

        <!-- Subscriptions -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Subscriptions</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $subscriptionCounts['active'] }}</div>
                <div class="text-xs text-indigo-600 mt-1 flex items-center">
                    <span>{{ $subscriptionCounts['trial'] }} on Trial</span>
                    <span class="mx-1 text-slate-300">•</span>
                    <span class="text-rose-500">{{ $subscriptionCounts['expired'] }} Expired</span>
                </div>
            </div>
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 text-xl">
                <i class="fa-solid fa-credit-card"></i>
            </div>
        </div>

        <!-- Invitations -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Invitations Issued</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $invitationCounts['total'] }}</div>
                <div class="text-xs text-slate-500 mt-1">
                    <span class="text-emerald-600 font-semibold">{{ $invitationCounts['accepted'] }} Accepted</span>, {{ $invitationCounts['pending'] }} Pending
                </div>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 text-xl">
                <i class="fa-solid fa-envelope-circle-check"></i>
            </div>
        </div>

        <!-- Reconciled Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Reconciled Revenue</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">${{ number_format($revenue['paid'], 2) }}</div>
                <div class="text-xs text-slate-500 mt-1">
                    ${{ number_format($revenue['pending'], 2) }} Pending collection
                </div>
            </div>
            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600 text-xl">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>
    </div>

    <!-- Two Columns: Recent Schools & Audit Stream -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Schools Overview -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Registered School Tenants</h2>
                <a href="{{ route('super-admin.schools.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">View All</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentSchools as $school)
                    <div class="py-3 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 font-bold">
                                {{ substr($school->name, 0, 2) }}
                            </div>
                            <div>
                                <a href="{{ route('super-admin.schools.show', $school) }}" class="font-semibold text-sm text-slate-900 hover:text-indigo-600">
                                    {{ $school->name }}
                                </a>
                                <div class="text-xs text-slate-400">{{ $school->email ?? 'No email' }} • Plan: {{ $school->currentSubscription?->plan?->name ?? 'None' }}</div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $school->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($school->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ ucfirst($school->status) }}
                            </span>
                            <form action="{{ route('super-admin.schools.switch', $school) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg transition" title="Switch context to this school">
                                    Manage
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400 text-sm">No schools registered yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Security & Audit Stream -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Security & Operational Audit Stream</h2>
                <a href="{{ route('super-admin.audit-logs.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">All Logs</a>
            </div>

            <div class="space-y-3">
                @forelse($recentSecurityLogs as $log)
                    <div class="flex items-start space-x-3 text-xs p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="mt-0.5 text-indigo-600 font-bold">
                            <i class="fa-solid fa-shield text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-slate-800 capitalize">{{ str_replace('_', ' ', $log->action) }}</div>
                            <div class="text-slate-500 text-[11px]">Actor: {{ $log->actor?->name ?? 'System' }} • {{ $log->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="text-[10px] bg-slate-200 text-slate-700 px-2 py-0.5 rounded font-mono">
                            {{ $log->entity_type ?? 'Auth' }}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400 text-sm">No audit logs recorded yet.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
