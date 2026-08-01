@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'auth-errors']) }}>
        <ul style="margin:0; padding-left: 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif