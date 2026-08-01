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

        @session('status')
            <div class="auth-status">{{ $value }}</div>
        @endsession

        <h1 class="auth-heading">Login</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="auth-field">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder=" ">
                <label for="email">Email</label>
            </div>

            <div class="auth-field">
                <input id="password" type="password" name="password" required autocomplete="off" placeholder=" ">
                <label for="password">Password</label>
            </div>

            <div class="auth-checkbox-row">
                <input type="checkbox" id="remember_me" name="remember">
                <label for="remember_me">Remember me</label>
            </div>

            <div class="auth-actions">
                @if (Route::has('password.request'))
                    <a class="auth-link" href="{{ route('password.request') }}">Forgot your password?</a>
                @endif

                <button type="submit" class="auth-submit-btn">Log in</button>
            </div>
        </form>
    </div>
</x-guest-layout>