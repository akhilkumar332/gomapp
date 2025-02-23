@props(['type' => 'submit', 'variant' => 'primary'])

<button type="{{ $type }}" 
        {{ $attributes->merge([
            'class' => 'btn btn-' . $variant . ' ' . ($attributes->get('class') ?? '')
        ]) }}>
    @if($slot->isEmpty())
        {{ ucfirst($type) }}
    @else
        {{ $slot }}
    @endif
</button>
