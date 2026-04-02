<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Verify the authenticated user has one of the allowed roles.
     * Usage in routes: ->middleware('role:admin') or ->middleware('role:admin,pharmacist')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Unauthorized. Insufficient permissions.',
            ], 403);
        }

        return $next($request);
    }
}
