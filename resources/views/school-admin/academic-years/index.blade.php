@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Academic Years & Sessions</h1>
            <p class="text-slate-500 text-sm">Manage academic calendar sessions with single-active-year enforcement</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Years List -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Configured Sessions</h2>

            <div class="divide-y divide-slate-100">
                @forelse($years as $yr)
                    <div class="py-4 flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-slate-900">{{ $yr->name }}</span>
                                @if($yr->is_current)
                                    <span class="bg-emerald-50 text-emerald-700 font-bold px-2.5 py-0.5 rounded-full text-[10px] uppercase">Active Session</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5">
                                {{ $yr->starts_on->format('M d, Y') }} — {{ $yr->ends_on->format('M d, Y') }}
                            </div>
                        </div>

                        <div>
                            @if(!$yr->is_current)
                                <form action="{{ route('school-admin.academic-years.set-current', $yr) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                        Make Current
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400 text-xs">No academic years configured yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Add Year Form -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">New Session</h2>

            <form action="{{ route('school-admin.academic-years.store') }}" method="POST" class="space-y-3">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Session Name *</label>
                    <input type="text" name="name" required placeholder="e.g. 2026 - 2027" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Starts On *</label>
                    <input type="date" name="starts_on" required value="{{ now()->toDateString() }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Ends On *</label>
                    <input type="date" name="ends_on" required value="{{ now()->addYear()->toDateString() }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                </div>

                <div class="flex items-center space-x-2 pt-2">
                    <input type="checkbox" id="is_current" name="is_current" value="1" class="rounded text-indigo-600 h-4 w-4">
                    <label for="is_current" class="text-xs text-slate-700 font-medium">Set as Current Session</label>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-xl text-xs shadow-sm transition">
                    Save Session
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
