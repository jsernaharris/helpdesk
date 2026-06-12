<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Queue;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $organization->queues()->create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('staff.organizations.show', $organization)
            ->with('success', 'Queue added.');
    }

    public function destroy(Organization $organization, Queue $queue)
    {
        if ($queue->organization_id !== $organization->id) {
            abort(403);
        }

        $queue->delete();

        return redirect()->route('staff.organizations.show', $organization)
            ->with('success', 'Queue removed.');
    }
}
