@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Account Settings</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">My Profile</h1>
        <p class="mt-1 text-sm text-slate-500">Manage your personal details and account security.</p>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-slate-800">Personal Information</h2>
            <p class="mt-1 text-sm text-slate-500">Only your own name, email address, and profile photo can be changed here.</p>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-4 border-b border-slate-100 pb-6 sm:flex-row sm:items-center">
                @if($user->avatar_path)
                    <img src="{{ route('profile.photo') }}" alt="Profile photo" class="h-20 w-20 rounded-full object-cover ring-2 ring-slate-100">
                @else
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-50 text-2xl font-semibold text-indigo-700" aria-hidden="true">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <label for="photo" class="mb-1 block text-sm font-medium text-slate-700">Profile Photo</label>
                    <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
                        class="block w-full max-w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                    <p class="mt-1 text-xs text-slate-500">JPG, PNG, or WebP, up to 2 MB.</p>
                    @error('photo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 @error('name') border-rose-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 @error('email') border-rose-400 @enderror">
                    @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="rounded-lg bg-slate-50 p-4">
                <h3 class="text-sm font-semibold text-slate-700">Access & Memberships</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @if($user->isSuperAdmin())
                        <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-800">Super Admin</span>
                    @endif
                    @foreach($schools as $school)
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-800">
                            {{ $school->name }} · {{ \Illuminate\Support\Str::headline($school->pivot->role) }}
                        </span>
                    @endforeach
                    @if(! $user->isSuperAdmin() && $schools->isEmpty())
                        <span class="text-sm text-slate-500">No active school memberships.</span>
                    @endif
                </div>
                <p class="mt-3 text-xs text-slate-500">Roles, school memberships, account status, and permissions are managed by your administrator.</p>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('profile.password') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <i class="fa-solid fa-lock text-slate-500"></i>
                    Change Password
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Profile
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
