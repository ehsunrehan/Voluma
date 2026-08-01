<x-guest-layout>
    <div class="auth-logo">
        <a href="{{ url('/') }}" style="font-family: var(--font-heading); font-size: 28px; font-weight: 700; text-decoration: none;">
            <span style="color: var(--coral);">V</span><span style="color: var(--text-white)  ">o</span><span style="color: var(--cyan);   ;">luma</span>
        </a>
    </div>

    <div class="auth-card">
        @if ($errors->any())
            <div class="auth-errors">
                <ul style="margin:0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h1 class="auth-heading">Register</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="auth-field">
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder=" ">
                <label for="name">Name</label>
            </div>

            <div class="auth-field">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder=" ">
                <label for="email">Email</label>
            </div>

            <div class="auth-field">
                <input id="password" type="password" name="password" required autocomplete="off" placeholder=" ">
                <label for="password">Password</label>
            </div>

            <div class="auth-field">
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="off" placeholder=" ">
                <label for="password_confirmation">Confirm Password</label>
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="auth-terms-row">
                    <input type="checkbox" name="terms" id="terms" required>
                    <div class="auth-terms-text">
                        {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'">'.__('Terms of Service').'</a>',
                                'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'">'.__('Privacy Policy').'</a>',
                        ]) !!}
                    </div>
                </div>
            @endif

            <div class="auth-actions">
                <a class="auth-link" href="{{ route('login') }}">Already registered?</a>
                <button type="submit" class="auth-submit-btn">Register</button>
            </div>
        </form>
    </div>
</x-guest-layout>