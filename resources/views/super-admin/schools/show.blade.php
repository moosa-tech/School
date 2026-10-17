@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-slate-900">{{ $school->name }}</h1>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $school->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($school->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                    {{ ucfirst($school->status) }}
                </span>
            </div>
            <p class="text-slate-500 text-sm mt-0.5">Slug: <span class="font-mono text-xs">{{ $school->slug }}</span> • Timezone: {{ $school->timezone }}</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('super-admin.schools.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                Back to Schools
            </a>
        </div>
    </div>

    <!-- School Overview Counts (No Personal Data Needed) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 text-center">
            <div class="text-3xl font-black text-indigo-600">{{ $overview['student_count'] ?? 0 }}</div>
            <div class="text-xs font-bold text-slate-500 uppercase mt-1">Total Students</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 text-center">
            <div class="text-3xl font-black text-emerald-600">{{ $overview['staff_count'] ?? 0 }}</div>
            <div class="text-xs font-bold text-slate-500 uppercase mt-1">Total Staff &amp; Faculty</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 text-center">
            <div class="text-3xl font-black text-blue-600">{{ $overview['teacher_count'] ?? 0 }}</div>
            <div class="text-xs font-bold text-slate-500 uppercase mt-1">Teachers</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 text-center">
            <div class="text-xl font-bold text-purple-600 truncate">{{ $school->currentSubscription?->plan?->name ?? 'None' }}</div>
            <div class="text-xs font-bold text-slate-500 uppercase mt-1">Active Plan</div>
        </div>
    </div>

    @if(session('initial_admin_password'))
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5 shadow-sm space-y-2">
            <div class="flex items-center space-x-2 text-amber-900 font-bold text-sm">
                <i class="fa-solid fa-key text-amber-600 text-base"></i>
                <span>Initial School Admin Login Credentials</span>
            </div>
            <p class="text-xs text-amber-800">
                These credentials are shown once. The admin should change the default password after logging in.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 text-xs">
                <div class="bg-white px-3 py-2 border border-amber-300 rounded-xl">
                    <div class="text-amber-700 font-semibold">Admin Email</div>
                    <div class="font-mono text-slate-800">{{ session('initial_admin_email') }}</div>
                </div>
                <div class="bg-white px-3 py-2 border border-amber-300 rounded-xl">
                    <div class="text-amber-700 font-semibold">Default Password</div>
                    <div class="font-mono text-slate-800">{{ session('initial_admin_password') }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- School Information Card -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Tenant Details</h2>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <dt class="font-semibold text-slate-400 uppercase">Contact Email</dt>
                    <dd class="text-slate-800 font-medium mt-1">{{ $school->email ?? 'Not provided' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-400 uppercase">Phone Number</dt>
                    <dd class="text-slate-800 font-medium mt-1">{{ $school->phone ?? 'Not provided' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-400 uppercase">Currency</dt>
                    <dd class="text-slate-800 font-medium mt-1">{{ $school->currency }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-400 uppercase">Registered Date</dt>
                    <dd class="text-slate-800 font-medium mt-1">{{ $school->created_at->format('M d, Y') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="font-semibold text-slate-400 uppercase">Campus Address</dt>
                    <dd class="text-slate-800 font-medium mt-1">{{ $school->address ?? 'Not provided' }}</dd>
                </div>
            </dl>

            <!-- Status Changer -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Change School Tenant Status</h3>
                <form action="{{ route('super-admin.schools.status', $school) }}" method="POST" class="flex items-center space-x-3">
                    @csrf
                    <select name="status" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800">
                        <option value="active" {{ $school->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ $school->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="suspended" {{ $school->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="archived" {{ $school->status === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                        Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Subscription Sidebar Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Subscription History</h2>

            @forelse($school->subscriptions as $sub)
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-1">
                    <div class="flex justify-between items-center font-bold text-slate-900">
                        <span>{{ $sub->plan->name ?? 'Custom Plan' }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">{{ ucfirst($sub->status) }}</span>
                    </div>
                    <div class="text-slate-500 text-[11px]">Valid: {{ $sub->starts_at?->format('M d') }} - {{ $sub->ends_at?->format('M d, Y') ?? 'Ongoing' }}</div>
                </div>
            @empty
                <div class="text-xs text-slate-400 py-4 text-center">No subscriptions recorded.</div>
            @endforelse
        </div>

    </div>

</div>
@endsection
