<button {{ $attributes->merge(['type' => 'submit', 'class' => 'auth-submit-btn']) }}>
    {{ $slot }}
</button>