<div x-data="formBuilder()" class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Template Name</label>
            <input type="text" name="name" value="{{ old('name', $formTemplate->name ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <input type="text" name="description" value="{{ old('description', $formTemplate->description ?? '') }}" placeholder="Brief description of this form..." class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Organization</label>
                <select name="organization_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">All Organizations (Global)</option>
                    @foreach($organizations as $org)
                    <option value="{{ $org->id }}" @selected(old('organization_id', $formTemplate->organization_id ?? '') == $org->id)>{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Route to Queue</label>
                <select name="queue_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">— None —</option>
                    @foreach($queues as $orgQueues)
                    <optgroup label="{{ $orgQueues->first()->organization?->name ?? 'Unassigned' }}">
                        @foreach($orgQueues as $queue)
                        <option value="{{ $queue->id }}" @selected(old('queue_id', $formTemplate->queue_id ?? '') == $queue->id)>{{ $queue->name }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Tickets from this form land here. Must match the organization.</p>
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600" @checked(old('is_active', $formTemplate->is_active ?? true))>
                    Active
                </label>
            </div>
        </div>
    </div>

    <!-- Field Builder -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900">Form Fields</h3>
            <button type="button" @click="addField()" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Add Field</button>
        </div>

        <div x-show="fields.length === 0" class="text-center py-8 text-sm text-gray-500">
            No fields added yet. Click "Add Field" to start building your form.
        </div>

        <div class="space-y-3">
            <template x-for="(field, index) in fields" :key="field.id">
                <div class="border rounded-lg p-4 bg-gray-50 relative">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="moveUp(index)" :disabled="index === 0" class="text-gray-400 hover:text-gray-600 disabled:opacity-30" title="Move up">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                            </button>
                            <button type="button" @click="moveDown(index)" :disabled="index === fields.length - 1" class="text-gray-400 hover:text-gray-600 disabled:opacity-30" title="Move down">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                            <span class="text-xs font-medium text-gray-500" x-text="'Field ' + (index + 1)"></span>
                        </div>
                        <button type="button" @click="removeField(index)" class="text-red-400 hover:text-red-600" title="Remove field">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Label</label>
                            <input type="text" x-model="field.label" @input="field.name = slugify(field.label)" required placeholder="e.g. Asset Tag" class="block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Field Key</label>
                            <input type="text" x-model="field.name" required placeholder="e.g. asset_tag" class="block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border bg-gray-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                            <select x-model="field.type" class="block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border">
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="number">Number</option>
                                <option value="email">Email</option>
                                <option value="url">URL</option>
                                <option value="date">Date</option>
                                <option value="select">Dropdown</option>
                                <option value="checkbox">Checkboxes</option>
                                <option value="radio">Radio Buttons</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Placeholder</label>
                            <input type="text" x-model="field.placeholder" placeholder="Optional" class="block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border">
                        </div>
                    </div>

                    <div class="mt-3 flex items-center gap-4">
                        <label class="flex items-center gap-1.5 text-xs text-gray-600">
                            <input type="checkbox" x-model="field.required" class="rounded border-gray-300 text-indigo-600">
                            Required
                        </label>
                    </div>

                    <!-- Options for select/checkbox/radio -->
                    <div x-show="['select', 'checkbox', 'radio'].includes(field.type)" class="mt-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Options (one per line)</label>
                        <textarea x-model="field.optionsText" @input="field.options = field.optionsText.split('\n').filter(o => o.trim())" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3" class="block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border font-mono"></textarea>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Hidden field to submit JSON -->
    <input type="hidden" name="fields" :value="JSON.stringify(fields.map(f => ({name: f.name, label: f.label, type: f.type, required: f.required, placeholder: f.placeholder, options: f.options})))">

    <div class="flex justify-end gap-3">
        <a href="{{ route('staff.form-templates.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ isset($formTemplate) && $formTemplate->exists ? 'Update Template' : 'Create Template' }}</button>
    </div>
</div>

@push('scripts')
<script>
function formBuilder() {
    const existing = @json(old('fields') ? json_decode(old('fields'), true) : ($formTemplate->fields ?? []));
    let nextId = 1;

    return {
        fields: existing.map(f => ({
            ...f,
            id: nextId++,
            required: f.required || false,
            placeholder: f.placeholder || '',
            options: f.options || [],
            optionsText: (f.options || []).join('\n'),
        })),

        addField() {
            this.fields.push({
                id: nextId++,
                name: '',
                label: '',
                type: 'text',
                required: false,
                placeholder: '',
                options: [],
                optionsText: '',
            });
        },

        removeField(index) {
            this.fields.splice(index, 1);
        },

        moveUp(index) {
            if (index > 0) {
                [this.fields[index - 1], this.fields[index]] = [this.fields[index], this.fields[index - 1]];
            }
        },

        moveDown(index) {
            if (index < this.fields.length - 1) {
                [this.fields[index], this.fields[index + 1]] = [this.fields[index + 1], this.fields[index]];
            }
        },

        slugify(text) {
            return text.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
        },
    };
}
</script>
@endpush
