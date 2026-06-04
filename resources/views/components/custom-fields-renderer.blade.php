@props(['fields' => [], 'values' => [], 'disabled' => false])

@foreach($fields as $field)
<div>
    <label class="block text-sm font-medium text-gray-700">
        {{ $field['label'] }}
        @if($field['required'] ?? false)
        <span class="text-red-500">*</span>
        @endif
    </label>

    @php
        $fieldName = 'custom_fields[' . $field['name'] . ']';
        $fieldValue = $values[$field['name']] ?? old('custom_fields.' . $field['name'], '');
    @endphp

    @if($field['type'] === 'textarea')
        <textarea
            name="{{ $fieldName }}"
            rows="3"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            {{ ($field['required'] ?? false) ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border {{ $disabled ? 'bg-gray-50' : '' }}"
        >{{ $fieldValue }}</textarea>

    @elseif($field['type'] === 'select')
        <select
            name="{{ $fieldName }}"
            {{ ($field['required'] ?? false) ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border {{ $disabled ? 'bg-gray-50' : '' }}"
        >
            <option value="">{{ $field['placeholder'] ?? 'Select...' }}</option>
            @foreach($field['options'] ?? [] as $option)
            <option value="{{ $option }}" @selected($fieldValue === $option)>{{ $option }}</option>
            @endforeach
        </select>

    @elseif($field['type'] === 'checkbox')
        @php $checkboxValues = is_array($fieldValue) ? $fieldValue : []; @endphp
        <div class="mt-1 space-y-1">
            @foreach($field['options'] ?? [] as $option)
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input
                    type="checkbox"
                    name="{{ $fieldName }}[]"
                    value="{{ $option }}"
                    {{ in_array($option, $checkboxValues) ? 'checked' : '' }}
                    {{ $disabled ? 'disabled' : '' }}
                    class="rounded border-gray-300 text-indigo-600"
                >
                {{ $option }}
            </label>
            @endforeach
        </div>

    @elseif($field['type'] === 'radio')
        <div class="mt-1 space-y-1">
            @foreach($field['options'] ?? [] as $option)
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input
                    type="radio"
                    name="{{ $fieldName }}"
                    value="{{ $option }}"
                    {{ $fieldValue === $option ? 'checked' : '' }}
                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                    {{ $disabled ? 'disabled' : '' }}
                    class="border-gray-300 text-indigo-600"
                >
                {{ $option }}
            </label>
            @endforeach
        </div>

    @else
        <input
            type="{{ $field['type'] }}"
            name="{{ $fieldName }}"
            value="{{ $fieldValue }}"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            {{ ($field['required'] ?? false) ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border {{ $disabled ? 'bg-gray-50' : '' }}"
        >
    @endif
</div>
@endforeach
