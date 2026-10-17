<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolInvitation;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $schoolCounts = [
            'total' => School::count(),
            'active' => School::where('status', 'active')->count(),
            'pending' => School::where('status', 'pending')->count(),
            'suspended' => School::where('status', 'suspended')->count(),
        ];

        $subscriptionCounts = [
            'active' => Subscription::where('status', 'active')->count(),
            'trial' => Subscription::where('status', 'trial')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
            'past_due' => Subscription::where('status', 'past_due')->count(),
        ];

        $invitationCounts = [
            'total' => SchoolInvitation::count(),
            'accepted' => SchoolInvitation::whereNotNull('accepted_at')->count(),
            'pending' => SchoolInvitation::whereNull('accepted_at')->whereNull('revoked_at')->where('expires_at', '>', now())->count(),
            'expired' => SchoolInvitation::whereNull('accepted_at')->where('expires_at', '<=', now())->count(),
        ];

        $revenue = [
            'paid' => SubscriptionPayment::where('status', 'completed')->sum('amount'),
            'pending' => SubscriptionPayment::where('status', 'pending')->sum('amount'),
        ];

        $aggregateStudents = Student::withoutGlobalScope('school')->count();

        $recentSchools = School::with('currentSubscription.plan')->latest()->take(5)->get();
        $recentSecurityLogs = AuditLog::with('actor')->latest('created_at')->take(8)->get();
        $plans = Plan::where('is_active', true)->get();

        return view('super-admin.dashboard', compact(
            'schoolCounts',
            'subscriptionCounts',
            'invitationCounts',
            'revenue',
            'aggregateStudents',
            'recentSchools',
            'recentSecurityLogs',
            'plans'
        ));
    }
}
