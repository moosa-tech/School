@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Weekly Class Timetable</h1>
            <p class="text-slate-500 text-sm">Schedule classroom lectures, teacher slots, and room allocations with collision detection</p>
        </div>
    </div>

    <!-- Filter Section Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('school-admin.timetable.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Academic Year</label>
                <select name="academic_year_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ $selectedYearId == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Class & Section</label>
                <select name="section_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    @foreach($classes as $c)
                        @foreach($c->sections as $s)
                            <option value="{{ $s->id }}" {{ $selectedSectionId == $s->id ? 'selected' : '' }}>{{ $c->name }} - {{ $s->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl text-xs transition">
                    View Timetable
                </button>
            </div>
        </form>
    </div>

    <!-- Weekly Timetable Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach($weekdays as $day)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-indigo-700 border-b border-slate-100 pb-2 flex items-center justify-between">
                    <span>{{ $day }}</span>
                    <span class="text-[10px] text-slate-400">{{ isset($entries[$day]) ? $entries[$day]->count() : 0 }} slots</span>
                </div>

                <div class="space-y-2">
                    @if(isset($entries[$day]) && $entries[$day]->isNotEmpty())
                        @foreach($entries[$day] as $slot)
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-1">
                                <div class="font-bold text-slate-900">{{ $slot->classSubject->subject->name }}</div>
                                <div class="text-[11px] text-indigo-700 font-semibold">{{ $slot->classSubject->teacher?->name ?? 'Faculty' }}</div>
                                <div class="text-[10px] text-slate-400 flex justify-between">
                                    <span>{{ substr($slot->starts_at, 0, 5) }} - {{ substr($slot->ends_at, 0, 5) }}</span>
                                    <span>Room: {{ $slot->room_id ?? '-' }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="py-6 text-center text-slate-300 text-[11px]">No slots</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add Slot Form -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-900 mb-3">Add Class Lecture Slot</h2>
        <form action="{{ route('school-admin.timetable.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-6 gap-3 text-xs">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
            <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Weekday *</label>
                <select name="weekday" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    @foreach($weekdays as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Subject Offering *</label>
                <select name="class_subject_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                    @foreach($classSubjects as $cs)
                        <option value="{{ $cs->id }}">{{ $cs->class->name }} - {{ $cs->subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Start Time *</label>
                <input type="time" name="starts_at" required value="08:30" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">End Time *</label>
                <input type="time" name="ends_at" required value="09:15" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Room / Lab</label>
                <input type="text" name="room_id" placeholder="e.g. Room 102" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-xl transition">
                    + Add Slot
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
