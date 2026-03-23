@extends('layouts.staff')
@section('title', 'Change Policy: ' . $organization->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('staff.organizations.show', $organization) }}" class="text-sm text-indigo-600 hover:underline">&larr; Back to {{ $organization->name }}</a>
</div>

<!-- Stats -->
<div class="grid grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow px-4 py-3"><dt class="text-xs text-gray-500">Total</dt><dd class="text-xl font-semibold">{{ $changeStats['total'] }}</dd></div>
    <div class="bg-white rounded-lg shadow px-4 py-3"><dt class="text-xs text-gray-500">Pending</dt><dd class="text-xl font-semibold text-yellow-600">{{ $changeStats['pending'] }}</dd></div>
    <div class="bg-white rounded-lg shadow px-4 py-3"><dt class="text-xs text-gray-500">Approved</dt><dd class="text-xl font-semibold text-green-600">{{ $changeStats['approved'] }}</dd></div>
    <div class="bg-white rounded-lg shadow px-4 py-3"><dt class="text-xs text-gray-500">Completed</dt><dd class="text-xl font-semibold text-blue-600">{{ $changeStats['completed'] }}</dd></div>
    <div class="bg-white rounded-lg shadow px-4 py-3"><dt class="text-xs text-gray-500">Failed</dt><dd class="text-xl font-semibold text-red-600">{{ $changeStats['failed'] }}</dd></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Policy Settings -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Change Policy Settings</h3>
        <form method="POST" action="{{ route('staff.changes.policy.update', $organization) }}" class="space-y-4">
            @csrf @method('PUT')

            <fieldset class="space-y-2">
                <legend class="text-sm font-medium text-gray-700">CAB Requirements</legend>
                <label class="flex items-center gap-2"><input type="checkbox" name="require_cab_for_standard" value="1" @checked($policy->require_cab_for_standard) class="rounded border-gray-300 text-indigo-600"><span class="text-sm">Require CAB for Standard changes</span></label>
                <label class="flex items-center gap-2"><input type="checkbox" name="require_cab_for_normal" value="1" @checked($policy->require_cab_for_normal) class="rounded border-gray-300 text-indigo-600"><span class="text-sm">Require CAB for Normal changes</span></label>
                <label class="flex items-center gap-2"><input type="checkbox" name="require_cab_for_emergency" value="1" @checked($policy->require_cab_for_emergency) class="rounded border-gray-300 text-indigo-600"><span class="text-sm">Require CAB for Emergency changes</span></label>
            </fieldset>

            <fieldset class="space-y-2">
                <legend class="text-sm font-medium text-gray-700">Required Documentation</legend>
                <label class="flex items-center gap-2"><input type="checkbox" name="require_implementation_plan" value="1" @checked($policy->require_implementation_plan) class="rounded border-gray-300 text-indigo-600"><span class="text-sm">Implementation Plan</span></label>
                <label class="flex items-center gap-2"><input type="checkbox" name="require_rollback_plan" value="1" @checked($policy->require_rollback_plan) class="rounded border-gray-300 text-indigo-600"><span class="text-sm">Rollback Plan</span></label>
                <label class="flex items-center gap-2"><input type="checkbox" name="require_test_plan" value="1" @checked($policy->require_test_plan) class="rounded border-gray-300 text-indigo-600"><span class="text-sm">Test Plan</span></label>
            </fieldset>

            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700">Min Lead Time (hours)</label><input type="number" name="min_lead_time_hours" value="{{ $policy->min_lead_time_hours }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
                <div><label class="block text-sm font-medium text-gray-700">Emergency Lead Time (hours)</label><input type="number" name="emergency_lead_time_hours" value="{{ $policy->emergency_lead_time_hours }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            </div>

            <fieldset class="space-y-2">
                <legend class="text-sm font-medium text-gray-700">Portal Access</legend>
                <label class="flex items-center gap-2"><input type="checkbox" name="allow_customer_submit" value="1" @checked($policy->allow_customer_submit) class="rounded border-gray-300 text-indigo-600"><span class="text-sm">Allow customer portal change submissions</span></label>
                <label class="flex items-center gap-2"><input type="checkbox" name="auto_approve_standard" value="1" @checked($policy->auto_approve_standard) class="rounded border-gray-300 text-indigo-600"><span class="text-sm">Auto-approve standard changes</span></label>
            </fieldset>

            <div>
                <label class="block text-sm font-medium text-gray-700">Change Window Notes</label>
                <textarea name="change_window_notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="e.g., Preferred maintenance window: Sat 2am-6am">{{ $policy->change_window_notes }}</textarea>
            </div>

            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Policy</button>
        </form>
    </div>

    <!-- CAB Members -->
    <div class="space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Change Advisory Board</h3>
            @if($cabMembers->count())
            <ul class="divide-y divide-gray-200 mb-4">
                @foreach($cabMembers as $member)
                <li class="flex items-center justify-between py-2">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $member->user?->name }}</p>
                        <p class="text-xs text-gray-500 capitalize">{{ $member->role }} &middot; {{ $member->user?->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('staff.changes.cab.destroy', [$organization, $member]) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
                    </form>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-sm text-gray-500 mb-4">No CAB members configured.</p>
            @endif

            <form method="POST" action="{{ route('staff.changes.cab.store', $organization) }}" class="flex gap-2">
                @csrf
                <select name="user_id" required class="block flex-1 rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">Select user</option>
                    @foreach($availableUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->organization?->name }})</option>
                    @endforeach
                </select>
                <select name="role" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="member">Member</option>
                    <option value="chair">Chair</option>
                    <option value="advisor">Advisor</option>
                </select>
                <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-700">Add</button>
            </form>
        </div>

        <!-- Blackout Periods -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Blackout Periods</h3>
            @if($blackouts->count())
            <ul class="divide-y divide-gray-200 mb-4">
                @foreach($blackouts as $blackout)
                <li class="flex items-center justify-between py-2">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $blackout->name }}</p>
                        <p class="text-xs text-gray-500">{{ $blackout->starts_at->format('M d, Y H:i') }} - {{ $blackout->ends_at->format('M d, Y H:i') }}</p>
                        @if($blackout->reason)<p class="text-xs text-gray-400">{{ $blackout->reason }}</p>@endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if($blackout->allow_emergency)<span class="text-xs text-green-600">Emergency OK</span>@endif
                        <form method="POST" action="{{ route('staff.changes.blackouts.destroy', [$organization, $blackout]) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
                        </form>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-sm text-gray-500 mb-4">No blackout periods configured.</p>
            @endif

            <form method="POST" action="{{ route('staff.changes.blackouts.store', $organization) }}" class="space-y-3">
                @csrf
                <div><input type="text" name="name" required placeholder="Blackout name" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
                <div><input type="text" name="reason" placeholder="Reason (optional)" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="datetime-local" name="starts_at" required class="rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <input type="datetime-local" name="ends_at" required class="rounded-md border-gray-300 text-sm px-3 py-2 border">
                </div>
                <label class="flex items-center gap-2"><input type="checkbox" name="allow_emergency" value="1" checked class="rounded border-gray-300 text-indigo-600"><span class="text-sm">Allow emergency changes</span></label>
                <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-700">Add Blackout</button>
            </form>
        </div>
    </div>
</div>

<!-- Change Categories -->
<div class="mt-6 bg-white shadow rounded-lg p-6">
    <h3 class="font-semibold text-gray-900 mb-4">Change Categories / Templates</h3>
    @if($categories->count())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @foreach($categories as $cat)
        <div class="border rounded-lg p-4">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900">{{ $cat->name }}</h4>
                    <p class="text-xs text-gray-500 mt-1">{{ ucfirst($cat->default_type) }} &middot; {{ ucfirst($cat->default_risk_level) }} risk &middot; CAB: {{ $cat->cab_required ? 'Yes' : 'No' }}</p>
                    @if($cat->description)<p class="text-xs text-gray-400 mt-1">{{ $cat->description }}</p>@endif
                </div>
                <form method="POST" action="{{ route('staff.changes.categories.destroy', [$organization, $cat]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('staff.changes.categories.store', $organization) }}" class="border-t pt-4 space-y-3">
        @csrf
        <p class="text-sm font-medium text-gray-700">Add Category</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div><input type="text" name="name" required placeholder="Category name" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            <div><select name="default_type" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"><option value="standard">Standard</option><option value="normal" selected>Normal</option><option value="emergency">Emergency</option></select></div>
            <div><select name="default_risk_level" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="critical">Critical</option></select></div>
            <label class="flex items-center gap-2"><input type="checkbox" name="cab_required" value="1" checked class="rounded border-gray-300 text-indigo-600"><span class="text-sm">CAB Required</span></label>
        </div>
        <div><input type="text" name="description" placeholder="Description (optional)" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div><textarea name="template_implementation_plan" rows="2" placeholder="Template implementation plan (optional)" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea></div>
        <div><textarea name="template_rollback_plan" rows="2" placeholder="Template rollback plan (optional)" class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea></div>
        <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-700">Add Category</button>
    </form>
</div>
@endsection
