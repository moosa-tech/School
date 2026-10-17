<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolInvitation;
use App\Models\User;
use App\Support\SchoolAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class InvitationService
{
    /**
     * Issue an invitation to an admin or staff member.
     *
     * @return array{invitation: SchoolInvitation, plain_token: string, url: string}
     */
    public function issueInvitation(School $school, string $email, string $role = 'school_admin', ?User $invitedBy = null): array
    {
        if (! in_array($role, SchoolAccess::schoolRoles(), true)) {
            throw new InvalidArgumentException('The requested invitation role is not allowed.');
        }

        $email = strtolower(trim($email));
        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'An account already exists for this email address. Existing accounts cannot accept password-setting invitations.',
            ]);
        }

        SchoolInvitation::where('school_id', $school->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        $plainToken = Str::random(48);
        $tokenHash = hash('sha256', $plainToken);

        $invitation = SchoolInvitation::create([
            'school_id' => $school->id,
            'email' => $email,
            'role' => $role,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addDays(7),
            'invited_by' => $invitedBy?->id,
        ]);

        return [
            'invitation' => $invitation,
            'plain_token' => $plainToken,
            'url' => route('invitations.accept.form', ['token' => $plainToken]),
        ];
    }

    public function findValidInvitation(string $plainToken): ?SchoolInvitation
    {
        $hash = hash('sha256', $plainToken);

        $invitation = SchoolInvitation::where('token_hash', $hash)->first();
        if (! $invitation || ! $invitation->isValid()) {
            return null;
        }

        return $invitation;
    }

    public function acceptInvitation(SchoolInvitation $invitation, string $name, string $password): User
    {
        return DB::transaction(function () use ($invitation, $name, $password) {
            $locked = SchoolInvitation::query()
                ->whereKey($invitation->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || ! $locked->isValid()) {
                abort(404, 'This invitation link is invalid or expired.');
            }

            if (! in_array($locked->role, SchoolAccess::schoolRoles(), true)) {
                abort(404, 'This invitation link is invalid or expired.');
            }

            $user = User::where('email', $locked->email)->first();

            if ($user) {
                abort(404, 'This invitation link is invalid or expired.');
            }

            $user = User::create([
                'name' => $name,
                'email' => $locked->email,
                'password' => $password,
                'status' => 'active',
            ]);

            $user->schools()->syncWithoutDetaching([
                $locked->school_id => [
                    'role' => $locked->role,
                    'status' => 'active',
                    'joined_at' => now(),
                ],
            ]);

            $locked->update(['accepted_at' => now()]);

            return $user->fresh();
        });
    }
}
