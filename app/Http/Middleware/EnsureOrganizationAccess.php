<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOrganizationAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        // MSP staff can access any organization
        if ($user->isMspStaff()) {
            return $next($request);
        }

        // Check route-bound organization
        $organization = $request->route('organization');
        if ($organization && $organization->id !== $user->organization_id) {
            abort(403, 'You do not have access to this organization.');
        }

        return $next($request);
    }
}
