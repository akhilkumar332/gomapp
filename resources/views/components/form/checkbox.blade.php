@props(['label', 'name', 'checked' => false])

<div class="mb-3">
    <div class="form-check">
        <input type="checkbox" 
               name="{{ $name }}" 
               id="{{ $name }}" 
               value="1"
               {{ old($name, $checked) ? 'checked' : '' }}
               {{ $attributes->merge(['class' => 'form-check-input ' . ($errors->has($name) ? 'is-invalid' : '')]) }}>
        
        <label class="form-check-label" for="{{ $name }}">
            {{ $label }}
        </label>

        @error($name)
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>
