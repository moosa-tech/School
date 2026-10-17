<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Guardian;
use Illuminate\View\View;

class ParentDashboardController extends Controller
{
    public function index(): View
    {
        $schoolId = session('current_school_id');
        $user = auth()->user();

        // Find linked guardian record for this user
        $guardian = Guardian::forSchool($schoolId)->where('user_id', $user->id)->first()
            ?? Guardian::forSchool($schoolId)->where('email', $user->email)->first();

        $children = collect();
        if ($guardian) {
            $children = $guardian->students()
                ->where('students.school_id', $schoolId)
                ->wherePivot('school_id', $schoolId)
                ->with([
                    'currentEnrollment.class',
                    'currentEnrollment.section',
                    'attendances.session',
                    'feeInvoices',
                    'feePayments',
                    'marks.examSubject.exam',
                    'marks.examSubject.classSubject.subject',
                ])
                ->get();
        }

        $announcements = Announcement::forSchool($schoolId)
            ->whereIn('audience', ['all', 'parents'])
            ->where('status', 'published')
            ->latest()
            ->take(5)
            ->get();

        return view('parent.dashboard', compact('guardian', 'children', 'announcements'));
    }
}
