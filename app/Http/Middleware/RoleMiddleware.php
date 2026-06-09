<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($roles === []) {
            return $next($request);
        }

        $role = $user->role->value ?? (int) $user->role;
        $allowed = array_map('intval', $roles);

        if (! in_array($role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
