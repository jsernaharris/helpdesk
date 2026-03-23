<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMspStaff
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->isMspStaff()) {
            abort(403, 'Access denied. MSP staff only.');
        }

        return $next($request);
    }
}
