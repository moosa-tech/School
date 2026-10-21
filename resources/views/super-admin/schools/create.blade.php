@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Onboard New School</h1>
            <p class="text-slate-500 text-sm">Add a school tenant and invite its initial school administrator</p>
        </div>
        <a href="{{ route('super-admin.schools.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('super-admin.schools.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">School Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    placeholder="e.g. Cambridge Model High School"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="slug" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tenant Slug (Optional)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                        placeholder="e.g. cambridge-model"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
                    <span class="text-[11px] text-slate-400">Leave blank to generate automatically</span>
                </div>

                <div>
                    <label for="admin_email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Initial School Admin Email *</label>
                    <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required
                        placeholder="admin@cambridge.edu"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
                    <span class="text-[11px] text-slate-400">A one-time invitation link will let the administrator set a password.</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Contact Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                        placeholder="+92 300 1234567"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
                </div>

                <div>
                    <label for="currency" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Operating Currency *</label>
                    <select id="currency" name="currency" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                        <option value="PKR" selected>PKR (Pakistani Rupee)</option>
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="timezone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Timezone *</label>
                    <select id="timezone" name="timezone" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                        <option value="Asia/Karachi" selected>Asia/Karachi (GMT+5)</option>
                        <option value="UTC">UTC (GMT+0)</option>
                        <option value="America/New_York">America/New_York (EST)</option>
                        <option value="Europe/London">Europe/London (GMT)</option>
                    </select>
                </div>

                <div>
                    <label for="plan_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Subscription Plan *</label>
                    <select id="plan_id" name="plan_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} (${{ $plan->price }}/{{ $plan->billing_interval }}) - Up to {{ $plan->student_limit }} students</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="address" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Campus Address</label>
                <textarea id="address" name="address" rows="2"
                    placeholder="Sector F-8/4, Islamabad, Pakistan"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">{{ old('address') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">School Activation *</label>
                    <select id="status" name="status" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                        <option value="pending" @selected(old('status', 'pending') === 'pending')>Pending approval (inactive)</option>
                        <option value="active" @selected(old('status') === 'active')>Active</option>
                    </select>
                    <span class="text-[11px] text-slate-400">School activation is independent of subscription/payment status.</span>
                </div>
                <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <input type="checkbox" id="is_trial" name="is_trial" value="1" @checked(old('is_trial', '1')) class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                    <label for="is_trial" class="ml-2 text-sm text-slate-700 font-medium">Start a 14-day evaluation trial</label>
                </div>
            </div>

            <fieldset class="space-y-3 rounded-2xl border border-slate-200 p-4">
                <legend class="px-1 text-xs font-bold uppercase tracking-wider text-slate-700">Portal Access</legend>
                <p class="text-xs text-slate-500">The School Admin Portal is required. Other portals are enabled only when selected.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($portals as $portal => $label)
                        <label class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-sm text-slate-700">
                            <input type="checkbox" name="enabled_portals[]" value="{{ $portal }}"
                                @checked(in_array($portal, old('enabled_portals', ['school_admin']), true))
                                @disabled($portal === 'school_admin')
                                class="rounded text-indigo-600 focus:ring-indigo-500">
                            @if($portal === 'school_admin')
                                <input type="hidden" name="enabled_portals[]" value="school_admin">
                            @endif
                            <span>{{ $label }}{{ $portal === 'school_admin' ? ' (required)' : '' }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="flex items-center space-x-2 pt-1">
                <p class="text-xs text-slate-500">New schools start pending by default; selecting Active is an explicit activation decision.</p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('super-admin.schools.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Create School</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
