<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\SchoolAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolMembership
{
    /**
     * Enforce tenant membership and bind session('current_school_id').
     */
    public function handle(Request $request, Closure $next, ?string $roleRequired = null): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin() && ! SchoolAccess::supportAccessEnabled()) {
            abort(403, 'Super admin access to school operations is disabled.');
        }

        $requestedSchoolId = $request->route('school_id') ?? $request->query('school_id');

        if ($requestedSchoolId) {
            $requestedSchoolId = (int) $requestedSchoolId;

            if ($user->isSuperAdmin() && SchoolAccess::supportAccessEnabled()) {
                session(['current_school_id' => $requestedSchoolId]);
            } else {
                $isMember = $user->schools()
                    ->where('schools.id', $requestedSchoolId)
                    ->wherePivot('status', 'active')
                    ->exists();

                if (! $isMember) {
                    abort(404);
                }

                session(['current_school_id' => $requestedSchoolId]);
            }
        }

        if (! session()->has('current_school_id')) {
            $firstSchool = $user->schools()->wherePivot('status', 'active')->first();
            if ($firstSchool) {
                session(['current_school_id' => $firstSchool->id]);
            } elseif ($user->isSuperAdmin() && SchoolAccess::supportAccessEnabled()) {
                abort(403, 'No school context selected.');
            } else {
                return redirect()->route('login')->with('error', 'You do not belong to any active school.');
            }
        }

        $currentSchoolId = (int) session('current_school_id');

        if (! $user->isSuperAdmin()) {
            $isMember = $user->schools()
                ->where('schools.id', $currentSchoolId)
                ->wherePivot('status', 'active')
                ->exists();

            if (! $isMember) {
                session()->forget('current_school_id');
                abort(403, 'You do not belong to any active school.');
            }
        }

        if ($roleRequired && ! ($user->isSuperAdmin() && SchoolAccess::supportAccessEnabled())) {
            $userRole = $user->schoolRole($currentSchoolId);
            $allowedRoles = explode('|', $roleRequired);

            if (! in_array($userRole, $allowedRoles, true)) {
                abort(403);
            }
        }

        if ($currentSchoolId) {
            $school = School::find($currentSchoolId);
            if (! $school || $school->status === 'archived') {
                abort(403, 'This school is not available.');
            }
        }

        return $next($request);
    }
}
