<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationService $invitationService
    ) {}

    /**
     * Show invitation acceptance screen.
     */
    public function showAcceptForm(string $token): View
    {
        $invitation = $this->invitationService->findValidInvitation($token);

        if (! $invitation) {
            abort(404, 'This invitation link is invalid, expired, or has already been used.');
        }

        return view('auth.accept-invitation', [
            'invitation' => $invitation,
            'token' => $token,
            'school' => $invitation->school,
        ]);
    }

    /**
     * Process invitation acceptance and set password.
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->invitationService->findValidInvitation($token);

        if (! $invitation) {
            abort(404, 'This invitation link is invalid or expired.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['prohibited'],
            'is_super_admin' => ['prohibited'],
        ]);

        $user = $this->invitationService->acceptInvitation($invitation, $request->name, $request->password);

        AuditLogService::log('invitation_accepted', 'SchoolInvitation', $invitation->id, [
            'school_id' => $invitation->school_id,
            'email' => $invitation->email,
            'role' => $invitation->role,
        ], $invitation->school_id);

        // Auto login
        Auth::login($user);
        $request->session()->regenerate();
        session(['current_school_id' => $invitation->school_id]);

        $role = $invitation->role;

        return match ($role) {
            'teacher' => redirect()->route('teacher.dashboard')->with('status', 'Welcome! Your account has been set up.'),
            'parent' => redirect()->route('parent.dashboard')->with('status', 'Welcome! Your account has been set up.'),
            'student' => redirect()->route('student.dashboard')->with('status', 'Welcome! Your account has been set up.'),
            default => redirect()->route('school-admin.dashboard')->with('status', 'Welcome! Your school account has been set up.'),
        };
    }
}
