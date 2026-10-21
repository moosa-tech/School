<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\SchoolAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if ($currentSchoolId) {
            $school = School::find($currentSchoolId);

            if (! $school) {
                session()->forget('current_school_id');
                abort(403, 'This school is not available.');
            }

            if (! $user->isSuperAdmin()) {
                $userRole = $user->schoolRole($currentSchoolId);
                $message = match (true) {
                    $school->status !== 'active' => 'Your school account is currently inactive. Please contact your school administrator.',
                    ! SchoolAccess::roleCanAccessSchool($school, $userRole) => 'The portal for your role is currently disabled for this school.',
                    default => null,
                };

                if ($message !== null) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    if ($request->expectsJson()) {
                        return response()->json(['message' => $message], Response::HTTP_FORBIDDEN);
                    }

                    return redirect()->route('login')->withErrors(['school' => $message]);
                }
            }

            if ($user->isSuperAdmin() && $school->status === 'archived') {
                abort(403, 'This school is not available.');
            }
        }

        if ($roleRequired && ! ($user->isSuperAdmin() && SchoolAccess::supportAccessEnabled())) {
            $userRole = $user->schoolRole($currentSchoolId);
            $allowedRoles = explode('|', $roleRequired);

            if (! in_array($userRole, $allowedRoles, true)) {
                abort(403);
            }
        }

        return $next($request);
    }
}
