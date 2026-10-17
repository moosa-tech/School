<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $schools = $user->schools()->wherePivot('status', 'active')->get();

        return view('profile.show', compact('user', 'schools'));
    }

    public function showPhoto(Request $request): StreamedResponse
    {
        $avatarPath = $request->user()->avatar_path;
        $disk = Storage::disk('local');
        abort_unless($avatarPath && $disk->exists($avatarPath), 404);

        $contentType = match (strtolower(pathinfo($avatarPath, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => null,
        };
        abort_unless($contentType, 404);

        $stream = $disk->readStream($avatarPath);
        abort_unless(is_resource($stream), 404);

        return response()->stream(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $contentType,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=2048,max_height=2048'],
        ]);

        $user->name = $validated['name'];

        if ($user->email !== $validated['email']) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
        }

        $previousAvatar = null;

        if ($request->hasFile('photo')) {
            $previousAvatar = $user->avatar_path;
            $avatarPath = Storage::disk('local')->putFile('profile-photos', $request->file('photo'), ['throw' => true]);

            if (! is_string($avatarPath)) {
                throw new RuntimeException('The profile photo could not be stored.');
            }

            $user->avatar_path = $avatarPath;
        }

        $user->save();

        if ($previousAvatar && ! Storage::disk('local')->delete($previousAvatar)) {
            Log::warning('Unable to remove a replaced profile photo.', [
                'user_id' => $user->id,
            ]);
        }

        return redirect()->route('profile.edit')->with('status', 'Profile updated successfully.');
    }

    public function showChangePassword(): View
    {
        return view('profile.change-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();
        $user->password = $request->string('password')->toString();
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('profile.password')->with('status', 'Password updated successfully.');
    }
}
