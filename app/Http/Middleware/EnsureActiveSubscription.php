<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    /**
     * Suspended schools keep data but cannot operate. Expired subscriptions stay read-only.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $schoolId = session('current_school_id');
        if (! $schoolId) {
            return $next($request);
        }

        $school = School::with('currentSubscription')->find($schoolId);
        if (! $school) {
            abort(404);
        }

        if ($school->status === 'suspended') {
            abort(403, 'This school has been suspended. Records are preserved.');
        }

        $subscription = $school->currentSubscription;
        if ($subscription?->status === 'suspended') {
            abort(403, 'School subscription is suspended. Contact the platform administrator.');
        }

        $canModify = $school->status === 'active'
            && $subscription !== null
            && in_array($subscription->status, ['active', 'trial'], true)
            && (! $subscription->starts_at || ! $subscription->starts_at->isFuture())
            && (! $subscription->ends_at || $subscription->ends_at->isFuture())
            && ($subscription->status !== 'trial'
                || ($subscription->trial_ends_at !== null && $subscription->trial_ends_at->isFuture()));

        if (! $canModify && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return back()->with('error', 'Your school subscription does not permit changes. Read-only access is available; contact the platform administrator.');
        }

        return $next($request);
    }
}
