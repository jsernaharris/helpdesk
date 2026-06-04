{{-- Grouped permission checkboxes. Expects $permissionGroups (array: group => [names]) and $assigned (array of checked names). --}}
<div>
    <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-medium text-gray-700">Permissions</label>
        <div class="text-xs text-gray-500" x-data>
            <button type="button" class="text-indigo-600 hover:underline" @click="$root.querySelectorAll('input[name=\'permissions[]\']').forEach(c => c.checked = true)">Select all</button>
            <span class="mx-1">·</span>
            <button type="button" class="text-indigo-600 hover:underline" @click="$root.querySelectorAll('input[name=\'permissions[]\']').forEach(c => c.checked = false)">Clear</button>
        </div>
    </div>
    @error('permissions')<p class="mb-2 text-xs text-red-600">{{ $message }}</p>@enderror
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($permissionGroups as $group => $permissions)
        <div class="border rounded-md p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">{{ str_replace('_', ' ', $group) }}</p>
            <div class="space-y-1.5">
                @foreach($permissions as $permission)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, $assigned)) class="rounded border-gray-300 text-indigo-600">
                    <span class="text-sm text-gray-700">{{ str_replace($group . '.', '', $permission) }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
