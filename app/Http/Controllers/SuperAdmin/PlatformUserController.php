<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PlatformUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->where('is_super_admin', true)
            ->orderBy('name')
            ->get();

        return view('super-admin.platform-users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'is_super_admin' => ['prohibited'],
        ]);

        $user = new User;
        $user->fill([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $user->forceFill(['is_super_admin' => true])->save();

        AuditLogService::log('platform_user_created', 'User', $user->id, [
            'email' => $user->email,
        ]);

        return back()->with('status', 'Platform administrator created.');
    }
}
