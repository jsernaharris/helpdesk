<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;

class ResolveTenant
{
    public function __construct(private TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $organization = $request->user()->organization;
            if ($organization) {
                $this->tenantContext->set($organization);
            }
        }

        // Allow MSP staff to switch org context
        if ($request->user()?->isMspStaff()) {
            $orgId = $request->header('X-Organization-Id') ?? $request->query('org');
            if ($orgId) {
                $org = Organization::find($orgId);
                if ($org) {
                    $this->tenantContext->set($org);
                }
            }
        }

        return $next($request);
    }
}
