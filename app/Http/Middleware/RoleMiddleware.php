<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    // Check if user has required role
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Check if user is logged in
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ApiResponse::error('Unauthenticated.', 401);
            }
            return redirect()->route('login');
        }

        // Check if user role matches allowed roles
        if (!empty($roles) && !in_array($user->role, $roles)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ApiResponse::error('Unauthorized access. Required role not met.', 403);
            }
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
