<div {{ $attributes->merge(['class' => 'form-group my-3']) }}>
    <x-forms.label
        :fieldId="$fieldId"
        :fieldLabel="$fieldLabel"
        :fieldRequired="$fieldRequired"
        :popover="$popover">
    </x-forms.label>

    <input
        type="email"
        autocomplete="off"
        class="form-control height-35 f-14"
        placeholder="{{ $fieldPlaceholder }}"
        value="{{ $fieldValue }}"
        name="{{ $fieldName }}"
        id="{{ $fieldId }}">
    @if ($fieldHelp)
        <small id="{{ $fieldId }}Help" class="form-text text-muted">{{ $fieldHelp }}</small>
    @endif
</div>
