@extends('layouts.app')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-6 flex items-center">
            <i class="fa-solid fa-lock mr-2 text-indigo-600"></i>
            Change Password
        </h2>
        @if(auth()->user()->must_change_password)
            <p class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                Your account is using a temporary password. Choose a new password to continue.
            </p>
        @endif
        <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
            @csrf
            <div>
                <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Current Password</label>
                <input id="current_password" type="password" name="current_password" autocomplete="current-password"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('current_password') border-rose-400 @enderror"
                    required>
                @error('current_password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                <input id="password" type="password" name="password" autocomplete="new-password"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('password') border-rose-400 @enderror"
                    required minlength="8">
                @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    required minlength="8">
            </div>
            <div class="pt-2">
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2.5 rounded-lg text-sm transition">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
