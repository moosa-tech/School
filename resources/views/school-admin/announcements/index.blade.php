@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">School Notices & Announcements</h1>
            <p class="text-slate-500 text-sm">Publish broadcast communications by targeted audience</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Announcements Feed -->
        <div class="lg:col-span-2 space-y-3">
            @forelse($announcements as $ann)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-2">
                    <div class="flex justify-between items-start">
                        <h2 class="text-base font-bold text-slate-900">{{ $ann->title }}</h2>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-indigo-50 text-indigo-700">
                            Audience: {{ $ann->audience }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">{{ $ann->body }}</p>

                    <div class="text-[11px] text-slate-400 border-t border-slate-100 pt-2 flex justify-between">
                        <span>Posted by: {{ $ann->creator?->name ?? 'Administration' }}</span>
                        <span>{{ $ann->created_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs">
                    No announcements published.
                </div>
            @endforelse

            <div class="p-2">
                {{ $announcements->links() }}
            </div>
        </div>

        <!-- Create Announcement Form -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Post Announcement</h2>

            <form action="{{ route('school-admin.announcements.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf

                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Notice Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Winter Break Schedule" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                </div>

                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Target Audience *</label>
                    <select name="audience" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                        <option value="all">All School Community</option>
                        <option value="teachers">Teaching Staff Only</option>
                        <option value="parents">Parents Only</option>
                        <option value="students">Students Only</option>
                        <option value="staff">Administrative Staff</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Message Body *</label>
                    <textarea name="body" rows="4" required placeholder="Enter announcement text..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl"></textarea>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl text-xs transition">
                    Broadcast Notice
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
