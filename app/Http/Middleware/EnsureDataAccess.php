<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDataAccess
{
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $user = $request->user();

        abort_unless($user && ($action === 'manage' ? $user->canManageData() : $user->canViewData()), 403);

        return $next($request);
    }
}
