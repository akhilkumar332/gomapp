@props(['action', 'method' => 'POST', 'hasFiles' => false])

<form action="{{ $action }}" method="{{ $method === 'GET' ? 'GET' : 'POST' }}" {!! $hasFiles ? 'enctype="multipart/form-data"' : '' !!} {{ $attributes }}>
    @unless($method === 'GET')
        @csrf
        @if(!in_array($method, ['GET', 'POST']))
            @method($method)
        @endif
    @endunless

    <div class="card">
        @if(isset($header))
            <div class="card-header">
                {{ $header }}
            </div>
        @endif

        <div class="card-body">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="card-footer">
                {{ $footer }}
            </div>
        @endif
    </div>
</form>
