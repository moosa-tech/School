<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->user()?->must_change_password
            && ! in_array($request->route()?->getName(), [
                'profile.password',
                'profile.password.update',
                'logout',
            ], true)
        ) {
            return redirect()->route('profile.password')
                ->with('status', 'Set a new password before continuing to your portal.');
        }

        return $next($request);
    }
}
