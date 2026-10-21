@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Schools Directory</h1>
            <p class="text-slate-500 text-sm">Manage tenant schools, subscriptions, and administrative invitations</p>
        </div>
        <a href="{{ route('super-admin.schools.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition inline-flex items-center space-x-2">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Add School</span>
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('super-admin.schools.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, slug..."
                class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white">

            <select name="status" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>

            <select name="plan_id" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                <option value="">All Plans</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium py-2 px-4 rounded-xl transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'plan_id']))
                    <a href="{{ route('super-admin.schools.index') }}" class="text-xs text-slate-500 hover:text-slate-700 px-2 py-2">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Schools Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">School Details</th>
                        <th class="px-6 py-4">Slug / ID</th>
                        <th class="px-6 py-4">Subscription</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schools as $school)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $school->name }}</div>
                                <div class="text-xs text-slate-400">{{ $school->email }} • {{ $school->phone ?? 'No phone' }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-500">
                                {{ $school->slug }} (ID: {{ $school->id }})
                            </td>
                            <td class="px-6 py-4">
                                @if($sub = $school->currentSubscription)
                                    <span class="font-semibold text-slate-800">{{ $sub->plan->name ?? 'Custom' }}</span>
                                    <div class="text-[11px] text-slate-400">Status: {{ ucfirst($sub->status) }}</div>
                                @else
                                    <span class="text-xs text-slate-400">None</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $school->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($school->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                    {{ ucfirst($school->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <form action="{{ route('super-admin.schools.status', $school) }}" method="POST" class="inline"
                                    @if($school->status === 'active')
                                        onsubmit="return confirm('Deactivate this school? Its users will lose access; records and subscription history will be preserved.');"
                                    @endif>
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $school->status === 'active' ? 'suspended' : 'active' }}">
                                    <button type="submit" class="font-semibold px-3 py-1.5 rounded-lg text-xs transition {{ $school->status === 'active' ? 'bg-rose-50 hover:bg-rose-100 text-rose-700' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700' }}">
                                        {{ $school->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                @if(config('saas.support_access_enabled'))
                                    <form action="{{ route('super-admin.schools.switch', $school) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition">
                                            Support access
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('super-admin.schools.show', $school) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                    Details
                                </a>
                                <a href="{{ route('super-admin.schools.edit', $school) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">No schools matching filters found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $schools->links() }}
        </div>
    </div>

</div>
@endsection
