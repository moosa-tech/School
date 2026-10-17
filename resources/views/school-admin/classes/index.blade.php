@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Classes & Sections</h1>
            <p class="text-slate-500 text-sm">Organize academic grade levels and their section capacities</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Classes & Sections Display -->
        <div class="lg:col-span-2 space-y-4">
            @forelse($classes as $c)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-sm">
                                <i class="fa-solid fa-shapes"></i>
                            </span>
                            <h2 class="text-base font-bold text-slate-900">{{ $c->name }}</h2>
                        </div>
                        <span class="text-xs text-slate-400">Sort Order: {{ $c->sort_order }}</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @forelse($c->sections as $sec)
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                                <div class="font-bold text-slate-800">{{ $sec->name }}</div>
                                <div class="text-slate-400 mt-1">Capacity: <strong>{{ $sec->capacity }}</strong> seats</div>
                            </div>
                        @empty
                            <div class="col-span-3 text-xs text-slate-400">No sections added to this class.</div>
                        @endforelse
                    </div>

                    <!-- Quick Add Section to this Class -->
                    <form action="{{ route('school-admin.sections.store') }}" method="POST" class="pt-2 border-t border-slate-100 flex items-center space-x-2 text-xs">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $c->id }}">
                        <input type="text" name="name" required placeholder="Section Name (e.g. Section B)" class="flex-1 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                        <input type="number" name="capacity" value="40" required class="w-20 px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-center">
                        <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                            + Add Section
                        </button>
                    </form>
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs">
                    No classes registered yet. Create one on the right.
                </div>
            @endforelse
        </div>

        <!-- Add Class Form -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Create New Class</h2>

            <form action="{{ route('school-admin.classes.store') }}" method="POST" class="space-y-3">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Class Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Grade 1 / Class 1" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="1" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                </div>

                <p class="text-[11px] text-slate-400">A default 'Section A' will automatically be created.</p>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-xl text-xs transition">
                    Create Class
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
