<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CabMember;
use App\Models\ChangeBlackoutPeriod;
use App\Models\ChangeCategory;
use App\Models\ChangePolicy;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class ChangePolicyController extends Controller
{
    public function show(Organization $organization)
    {
        $policy = $organization->getOrCreateChangePolicy();
        $categories = $organization->changeCategories()->orderBy('sort_order')->get();
        $cabMembers = $organization->cabMembers()->with('user')->orderBy('role')->get();
        $blackouts = $organization->changeBlackoutPeriods()->orderByDesc('starts_at')->get();

        // Get available MSP users for CAB
        $mspUsers = User::whereHas('organization', fn ($q) => $q->where('is_msp', true))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get org's own users for CAB
        $orgUsers = $organization->users()->where('is_active', true)->orderBy('name')->get();
        $availableUsers = $mspUsers->merge($orgUsers);

        $changeStats = [
            'total' => $organization->changeRequests()->count(),
            'pending' => $organization->changeRequests()->whereIn('status', ['submitted', 'under_review'])->count(),
            'approved' => $organization->changeRequests()->where('status', 'approved')->count(),
            'completed' => $organization->changeRequests()->where('status', 'completed')->count(),
            'failed' => $organization->changeRequests()->where('status', 'failed')->count(),
        ];

        return view('staff.changes.policy', compact(
            'organization', 'policy', 'categories', 'cabMembers',
            'blackouts', 'availableUsers', 'changeStats'
        ));
    }

    public function updatePolicy(Request $request, Organization $organization)
    {
        $request->validate([
            'require_cab_for_normal' => 'sometimes|boolean',
            'require_cab_for_standard' => 'sometimes|boolean',
            'require_cab_for_emergency' => 'sometimes|boolean',
            'min_lead_time_hours' => 'required|integer|min:0',
            'emergency_lead_time_hours' => 'required|integer|min:0',
            'require_rollback_plan' => 'sometimes|boolean',
            'require_test_plan' => 'sometimes|boolean',
            'require_implementation_plan' => 'sometimes|boolean',
            'allow_customer_submit' => 'sometimes|boolean',
            'auto_approve_standard' => 'sometimes|boolean',
            'change_window_notes' => 'nullable|string',
        ]);

        $policy = $organization->getOrCreateChangePolicy();
        $policy->update([
            'require_cab_for_normal' => $request->boolean('require_cab_for_normal'),
            'require_cab_for_standard' => $request->boolean('require_cab_for_standard'),
            'require_cab_for_emergency' => $request->boolean('require_cab_for_emergency'),
            'min_lead_time_hours' => $request->min_lead_time_hours,
            'emergency_lead_time_hours' => $request->emergency_lead_time_hours,
            'require_rollback_plan' => $request->boolean('require_rollback_plan'),
            'require_test_plan' => $request->boolean('require_test_plan'),
            'require_implementation_plan' => $request->boolean('require_implementation_plan'),
            'allow_customer_submit' => $request->boolean('allow_customer_submit'),
            'auto_approve_standard' => $request->boolean('auto_approve_standard'),
            'change_window_notes' => $request->change_window_notes,
        ]);

        return redirect()->route('staff.changes.policy', $organization)
            ->with('success', 'Change policy updated.');
    }

    public function storeCategory(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_type' => 'required|in:standard,normal,emergency',
            'default_risk_level' => 'required|in:low,medium,high,critical',
            'template_implementation_plan' => 'nullable|string',
            'template_rollback_plan' => 'nullable|string',
            'template_test_plan' => 'nullable|string',
            'cab_required' => 'sometimes|boolean',
        ]);

        ChangeCategory::create(array_merge(
            $request->only(['name', 'description', 'default_type', 'default_risk_level',
                'template_implementation_plan', 'template_rollback_plan', 'template_test_plan']),
            [
                'organization_id' => $organization->id,
                'cab_required' => $request->boolean('cab_required'),
            ]
        ));

        return redirect()->route('staff.changes.policy', $organization)
            ->with('success', 'Change category added.');
    }

    public function destroyCategory(Organization $organization, ChangeCategory $category)
    {
        if ($category->organization_id !== $organization->id) {
            abort(403);
        }
        $category->delete();

        return redirect()->route('staff.changes.policy', $organization)
            ->with('success', 'Change category removed.');
    }

    public function storeCabMember(Request $request, Organization $organization)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:chair,member,advisor',
        ]);

        CabMember::updateOrCreate(
            ['organization_id' => $organization->id, 'user_id' => $request->user_id],
            ['role' => $request->role, 'is_active' => true]
        );

        return redirect()->route('staff.changes.policy', $organization)
            ->with('success', 'CAB member added.');
    }

    public function destroyCabMember(Organization $organization, CabMember $member)
    {
        if ($member->organization_id !== $organization->id) {
            abort(403);
        }
        $member->delete();

        return redirect()->route('staff.changes.policy', $organization)
            ->with('success', 'CAB member removed.');
    }

    public function storeBlackout(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'reason' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'allow_emergency' => 'sometimes|boolean',
        ]);

        ChangeBlackoutPeriod::create([
            'organization_id' => $organization->id,
            'name' => $request->name,
            'reason' => $request->reason,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'allow_emergency' => $request->boolean('allow_emergency', true),
        ]);

        return redirect()->route('staff.changes.policy', $organization)
            ->with('success', 'Blackout period added.');
    }

    public function destroyBlackout(Organization $organization, ChangeBlackoutPeriod $blackout)
    {
        if ($blackout->organization_id !== $organization->id) {
            abort(403);
        }
        $blackout->delete();

        return redirect()->route('staff.changes.policy', $organization)
            ->with('success', 'Blackout period removed.');
    }
}
