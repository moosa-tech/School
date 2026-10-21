@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit School</h1>
            <p class="text-sm text-slate-500">{{ $school->name }} · Subscription is managed separately.</p>
        </div>
        <a href="{{ route('super-admin.schools.show', $school) }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</a>
    </div>

    <form action="{{ route('super-admin.schools.update', $school) }}" method="POST" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        @csrf
        @method('PATCH')

        <div>
            <label for="name" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-700">School Name *</label>
            <input id="name" name="name" value="{{ old('name', $school->name) }}" required maxlength="150" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm">
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="email" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-700">Contact Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $school->email) }}" maxlength="150" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label for="phone" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-700">Contact Phone</label>
                <input id="phone" name="phone" value="{{ old('phone', $school->phone) }}" maxlength="50" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label for="currency" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-700">Currency *</label>
                <input id="currency" name="currency" value="{{ old('currency', $school->currency) }}" required maxlength="10" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label for="timezone" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-700">Timezone *</label>
                <input id="timezone" name="timezone" value="{{ old('timezone', $school->timezone) }}" required maxlength="50" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm">
            </div>
        </div>

        <div>
            <label for="address" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-700">Campus Address</label>
            <textarea id="address" name="address" rows="3" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm">{{ old('address', $school->address) }}</textarea>
        </div>

        <div>
            <label for="status" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-700">School Activation *</label>
            <select id="status" name="status" required class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm">
                @foreach(['active' => 'Active', 'pending' => 'Pending approval', 'suspended' => 'Inactive (suspended)', 'archived' => 'Archived'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $school->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Activation is separate from subscription payment and expiry.</p>
        </div>

        <fieldset class="space-y-3 rounded-2xl border border-slate-200 p-4">
            <legend class="px-1 text-xs font-bold uppercase tracking-wider text-slate-700">Portal Access</legend>
            <p class="text-xs text-slate-500">School Admin access is required. Disabling a student portal does not remove student records or school administration features.</p>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach($portals as $portal => $label)
                    <label class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-sm text-slate-700">
                        <input type="checkbox" name="enabled_portals[]" value="{{ $portal }}"
                            @checked(in_array($portal, old('enabled_portals', $school->enabled_portals ?? array_keys($portals)), true))
                            @disabled($portal === 'school_admin')
                            class="rounded text-indigo-600 focus:ring-indigo-500">
                        @if($portal === 'school_admin')
                            <input type="hidden" name="enabled_portals[]" value="school_admin">
                        @endif
                        {{ $label }}{{ $portal === 'school_admin' ? ' (required)' : '' }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-4 sm:flex-row">
            <a href="{{ route('super-admin.schools.show', $school) }}" class="rounded-xl px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</a>
            <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Save School Configuration</button>
        </div>
    </form>
</div>
@endsection
