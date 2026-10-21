<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\SchoolAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the common unified login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectAfterAuth(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Authenticate with email and password, then redirect according to the assigned role.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower(trim($request->email));
        $throttleKey = 'login:'.$email.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        if (! Auth::attempt([
            'email' => $email,
            'password' => $request->password,
            'status' => 'active',
        ])) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->withInput($request->only('email'));
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        AuditLogService::log('login_success', 'User', $user->id, [
            'ip' => $request->ip(),
        ]);

        return $this->redirectAfterAuth($user);
    }

    /**
     * Log out user.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            AuditLogService::log('logout', 'User', $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }

    /**
     * Determine redirect route based on roles and schools.
     */
    protected function redirectAfterAuth(User $user): RedirectResponse
    {
        if ($user->isSuperAdmin()) {
            if ($user->must_change_password) {
                return redirect()->route('profile.password');
            }

            return redirect()->route('super-admin.dashboard');
        }

        $activeMemberships = $user->schools()
            ->wherePivot('status', 'active')
            ->orderBy('schools.id')
            ->get();

        $school = $activeMemberships->first(
            fn (School $school): bool => SchoolAccess::roleCanAccessSchool($school, $school->pivot->role)
        );

        if ($school) {
            session(['current_school_id' => $school->id]);
            $role = $user->schoolRole($school->id);

            if ($user->must_change_password) {
                return redirect()->route('profile.password');
            }

            return match ($role) {
                'teacher' => redirect()->route('teacher.dashboard'),
                'student' => redirect()->route('student.dashboard'),
                'parent' => redirect()->route('parent.dashboard'),
                'school_admin', 'principal', 'accountant', 'staff' => redirect()->route('school-admin.dashboard'),
                default => $this->rejectUnassignedUser(),
            };
        }

        if ($activeMemberships->contains(fn (School $school): bool => $school->status !== 'active')) {
            return $this->rejectUnassignedUser(
                'Your school account is currently inactive. Please contact your school administrator.'
            );
        }

        if ($activeMemberships->isNotEmpty()) {
            return $this->rejectUnassignedUser(
                'The portal for your role is currently disabled for this school.'
            );
        }

        return $this->rejectUnassignedUser();
    }

    private function rejectUnassignedUser(string $message = 'This account is not assigned to an active school role.'): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
