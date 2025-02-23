@props(['label', 'name', 'value' => null, 'required' => false, 'rows' => 3])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <textarea name="{{ $name }}" 
              id="{{ $name }}" 
              rows="{{ $rows }}"
              {{ $required ? 'required' : '' }}
              {{ $attributes->merge(['class' => 'form-control ' . ($errors->has($name) ? 'is-invalid' : '')]) }}>{{ old($name, $value) }}</textarea>

    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
