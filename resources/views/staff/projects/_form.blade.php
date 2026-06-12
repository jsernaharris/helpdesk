@php($p = $project ?? null)
@php($selectedMembers = old('members', isset($project) ? $project->members->pluck('id')->all() : []))

@if($errors->any())
<div class="rounded-md bg-red-50 p-4 mb-4">
    <ul class="list-disc list-inside text-sm text-red-800">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="bg-white shadow rounded-lg p-6 space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Project Name</label>
            <input type="text" name="name" value="{{ old('name', $p->name ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Organization</label>
            <select name="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                <option value="">— Select —</option>
                @foreach($organizations as $org)
                <option value="{{ $org->id }}" @selected(old('organization_id', $p->organization_id ?? '') == $org->id)>{{ $org->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('description', $p->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                @foreach(['planned','active','on_hold','completed','cancelled'] as $s)
                <option value="{{ $s }}" @selected(old('status', $p->status ?? 'planned') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Start Date</label>
            <input type="date" name="start_date" value="{{ old('start_date', optional($p?->start_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Due Date</label>
            <input type="date" name="due_date" value="{{ old('due_date', optional($p?->due_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Assigned Team Members</label>
        <select name="members[]" multiple size="6" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            @foreach($technicians as $tech)
            <option value="{{ $tech->id }}" @selected(in_array($tech->id, $selectedMembers))>{{ $tech->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple.</p>
    </div>

    <div class="flex justify-end gap-3 border-t pt-5">
        <a href="{{ route('staff.projects.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ $p ? 'Save Changes' : 'Create Project' }}</button>
    </div>
</div>
