@extends('layouts.app')

@section('content')
<div class="space-y-6">

    @if(session('account_credentials.school_id') === session('current_school_id'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
            <h2 class="font-bold">One-time staff login credentials</h2>
            <p class="mt-2">Username: <strong>{{ session('account_credentials.username') }}</strong></p>
            <p>Temporary password: <code class="font-mono">{{ \Illuminate\Support\Facades\Crypt::decryptString(session('account_credentials.encrypted_password')) }}</code></p>
            <p class="mt-2 text-xs">{{ session('account_credentials.whatsapp_status') }}</p>
            <p class="mt-1 text-xs">The employee must change this password before using the portal.</p>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Faculty & Staff Directory</h1>
            <p class="text-slate-500 text-sm">Manage teaching faculty, accountants, administrative staff, and login accounts</p>
        </div>
        <a href="{{ route('school-admin.employees.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition inline-flex items-center space-x-2">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span>Add Staff Profile</span>
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('school-admin.employees.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, code, email..."
                class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

            <select name="role" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                <option value="">All Roles</option>
                <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Teacher / Faculty</option>
                <option value="accountant" {{ request('role') === 'accountant' ? 'selected' : '' }}>Accountant</option>
                <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Administrative Staff</option>
            </select>

            <select name="status" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold py-2 px-4 rounded-xl transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('school-admin.employees.index') }}" class="text-xs text-slate-500 hover:text-slate-700 px-2">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Employees Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Emp #</th>
                        <th class="px-6 py-4">Full Name</th>
                        <th class="px-6 py-4">Job Title & Role</th>
                        <th class="px-6 py-4">Contact</th>
                        <th class="px-6 py-4">Portal Access</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-700">
                                {{ $emp->employee_number }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ $emp->name }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-800 font-medium">{{ $emp->job_title }}</div>
                                <span class="text-[10px] uppercase font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">{{ $emp->role }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div>{{ $emp->email ?? '-' }}</div>
                                <div class="text-slate-400">{{ $emp->phone ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($emp->user_id)
                                    <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-semibold flex items-center w-max space-x-1">
                                        <i class="fa-solid fa-check text-[10px]"></i>
                                        <span>User Linked</span>
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Profile Only</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $emp->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($emp->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">No staff members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $employees->links() }}
        </div>
    </div>

</div>
@endsection
