<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::withCount(['users', 'tickets'])
            ->orderBy('name')
            ->paginate(25);

        return view('staff.organizations.index', compact('organizations'));
    }

    public function create()
    {
        return view('staff.organizations.create');
    }

    public function store(StoreOrganizationRequest $request)
    {
        $organization = Organization::create($request->validated());

        return redirect()->route('staff.organizations.show', $organization)
            ->with('success', 'Organization created successfully.');
    }

    public function show(Organization $organization)
    {
        $organization->load(['users', 'slaPlans']);
        $recentTickets = $organization->tickets()
            ->with(['requester', 'assignedTo'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('staff.organizations.show', compact('organization', 'recentTickets'));
    }

    public function edit(Organization $organization)
    {
        return view('staff.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations,slug,' . $organization->id,
            'domain' => 'nullable|string|max:255',
            'email_domain' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $organization->update($request->all());

        return redirect()->route('staff.organizations.show', $organization)
            ->with('success', 'Organization updated successfully.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()->route('staff.organizations.index')
            ->with('success', 'Organization deleted.');
    }
}
