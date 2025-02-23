@props(['headers' => [], 'striped' => true, 'hover' => true, 'responsive' => true])

<div {{ $responsive ? 'class=table-responsive' : '' }}>
    <table {{ $attributes->merge(['class' => 'table ' . 
        ($striped ? 'table-striped ' : '') . 
        ($hover ? 'table-hover ' : '')
    ]) }}>
        @if(count($headers) > 0)
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th {!! is_array($header) ? $header['attributes'] ?? '' : '' !!}>
                            {{ is_array($header) ? $header['text'] : $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

@pushOnce('styles')
<style>
.table {
    --bs-table-striped-bg: rgba(71, 35, 217, 0.02);
    margin-bottom: 0;
}

.table > :not(caption) > * > * {
    padding: 1rem;
    background-color: transparent;
    border-bottom-width: 1px;
    box-shadow: inset 0 0 0 9999px var(--bs-table-accent-bg);
}

.table > thead {
    background-color: #f8f9fa;
}

.table > thead > tr > th {
    border-bottom: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: #6c757d;
}

.table > tbody > tr {
    transition: all 0.2s;
}

.table-hover > tbody > tr:hover {
    --bs-table-accent-bg: rgba(71, 35, 217, 0.04);
}

.table .avatar {
    width: 2rem;
    height: 2rem;
}

.table .avatar img {
    border-radius: 50%;
}

.table .badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.5em 1em;
}

.table .btn-icon {
    width: 2rem;
    height: 2rem;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    color: #6c757d;
    background-color: transparent;
    border: none;
    transition: all 0.2s;
}

.table .btn-icon:hover {
    background-color: rgba(71, 35, 217, 0.1);
    color: var(--first-color);
}

.table .dropdown-menu {
    padding: 0.5rem;
    min-width: 10rem;
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
}

.table .dropdown-item {
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.table .dropdown-item i {
    font-size: 1.25rem;
}

.table .dropdown-item:hover {
    background-color: rgba(71, 35, 217, 0.1);
    color: var(--first-color);
}

/* Responsive styles */
@media (max-width: 768px) {
    .table-responsive {
        border: none;
    }
    
    .table > :not(caption) > * > * {
        padding: 0.75rem;
        font-size: 0.875rem;
    }
    
    .table > thead > tr > th {
        font-size: 0.75rem;
    }
}
</style>
@endPushOnce
