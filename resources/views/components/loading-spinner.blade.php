@props(['size' => '24px', 'color' => 'var(--primary-color)'])

<div {{ $attributes->merge(['class' => 'loading-spinner']) }}>
    <div class="spinner" style="width: {{ $size }}; height: {{ $size }}"></div>
</div>

<style>
.loading-spinner {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.loading-spinner .spinner {
    border: 2px solid rgba(0, 0, 0, 0.1);
    border-left-color: {{ $color }};
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
