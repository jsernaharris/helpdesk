<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Models\Organization;
use App\Models\OrganizationDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $organization = DB::transaction(function () use ($request) {
            $organization = Organization::create($request->validated());
            $this->syncDomains($organization, $request->input('email_domains'));

            return $organization;
        });

        return redirect()->route('staff.organizations.show', $organization)
            ->with('success', 'Organization created successfully.');
    }

    public function show(Organization $organization)
    {
        $organization->load(['users', 'slaPlans', 'domains', 'queues' => fn ($q) => $q->orderBy('sort_order')->orderBy('name')]);
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
            'email_domains' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($request, $organization) {
            $organization->update($request->all());
            $this->syncDomains($organization, $request->input('email_domains'));
        });

        return redirect()->route('staff.organizations.show', $organization)
            ->with('success', 'Organization updated successfully.');
    }

    /**
     * Reconcile an organization's email domains from a newline/comma-separated list.
     * A domain may belong to only one organization (enforced here and by a unique index).
     */
    private function syncDomains(Organization $organization, ?string $raw): void
    {
        $domains = collect(preg_split('/[\r\n,]+/', (string) $raw))
            ->map(fn ($d) => strtolower(trim($d)))
            ->filter()
            ->unique()
            ->values();

        $conflicts = OrganizationDomain::whereIn('domain', $domains)
            ->where('organization_id', '!=', $organization->id)
            ->pluck('domain');

        if ($conflicts->isNotEmpty()) {
            throw ValidationException::withMessages([
                'email_domains' => 'Already assigned to another organization: ' . $conflicts->implode(', '),
            ]);
        }

        $organization->domains()->whereNotIn('domain', $domains)->delete();
        $existing = $organization->domains()->pluck('domain');
        foreach ($domains->diff($existing) as $domain) {
            $organization->domains()->create(['domain' => $domain]);
        }
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()->route('staff.organizations.index')
            ->with('success', 'Organization deleted.');
    }
}
