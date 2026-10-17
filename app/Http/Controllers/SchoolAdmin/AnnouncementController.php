<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $schoolId = session('current_school_id');
        $announcements = Announcement::forSchool($schoolId)->with('creator')->latest()->paginate(25);

        return view('school-admin.announcements.index', compact('announcements'));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'audience' => ['required', 'in:all,teachers,students,parents,staff'],
        ]);

        $announcement = Announcement::create([
            'school_id' => $schoolId,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'audience' => $validated['audience'],
            'publish_at' => now(),
            'created_by' => auth()->id(),
            'status' => 'published',
        ]);

        AuditLogService::log('announcement_posted', 'Announcement', $announcement->id, ['title' => $announcement->title], $schoolId);

        return back()->with('status', 'Announcement published.');
    }
}
