<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowedRoles = collect($roles)
            ->flatMap(fn (string $role) => explode(',', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->all();

        $user = $request->user();

        if (!$user || !$this->userHasAllowedRole($user, $allowedRoles)) {
            return response()->json(['message' => 'Forbidden — insufficient role.'], 403);
        }

        return $next($request);
    }

    protected function userHasAllowedRole(object $user, array $allowedRoles): bool
    {
        foreach ($allowedRoles as $role) {
            if ($user->hasRole($role) || $user->role === $role) {
                return true;
            }
        }

        return false;
    }
}
