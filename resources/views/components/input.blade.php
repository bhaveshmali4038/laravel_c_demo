@props(['name', 'label', 'type' => 'text', 'value' => null])
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }} @if ($attributes->get('required'))<span class="text-secondary" aria-hidden="true">*</span>@endif</label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
        @if (!in_array($type, ['password', 'file'])) value="{{ old($name, $value) }}" @endif
        @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
    @error($name)<div id="{{ $name }}-error" class="invalid-feedback">{{ $message }}</div>@enderror
</div>
