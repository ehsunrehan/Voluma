<header class="site-header">
    <div class="container">
        <nav class="navbar">

            <!-- Logo with subtle red/cyan offset -->
            <a href="{{ route('home') }}" class="logo">
                <span class="logo-text logo-red">V</span>
                <span class="logo-text logo-cyan">o</span>
                <span class="logo-text">luma</span>
            </a>

            <button type="button" class="nav-toggle" id="navToggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="nav-links" id="navLinks">

    @guest
                <li>
            <a href="#how" class="nav-pill">
                How It Works
            </a>
        </li>

        <li>
            <a href="#examples" class="nav-pill">
                Examples
            </a>
        </li>

        <li>
            <a href="#credits" class="nav-pill">
                Credits
            </a>
        </li>
    @endguest

    @auth
        <li>
            <a href="{{ route('dashboard') }}">
                Image-to-3D
            </a>
        </li>

        <li>
            <a href="{{ route('text.dashboard') }}">
                Text-to-3D
            </a>
        </li>

        <li>
            <a href="{{ route('gallery') }}">Gallery</a>
            
        </li>

        <li>
            <a href="{{ route('convert.index') }}">Convert</a>

        </li>

    @endauth

</ul>

            <div class="nav-actions">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-ghost">Log In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Start Free</a>
                @endguest

                @auth
                    <span class="credits-badge">
                        <span id="navbarCreditCount">{{ $credits }}</span> Credits
                        </span>

                    <div class="user-dropdown" id="userDropdown">
                        <button class="user-btn">
                            {{ Auth::user()->name }}
                            <svg width="12" height="12" viewBox="0 0 12 12" class="dropdown-arrow">
                                <path d="M6 8L1 3h10z" fill="currentColor"/>
                            </svg>
                        </button>
                        <ul class="dropdown-menu" id="dropdownMenu">

    <li>
        <a href="{{ route('dashboard') }}">
            Image-to-3D
        </a>
    </li>

    <li>
        <a href="{{ route('text.dashboard') }}">
            Text-to-3D
        </a>
    </li>

    <li>
        <a href="{{ route('gallery') }}">
            Gallery
        </a>
    </li>

    <li>
        <a href="{{ route('profile') }}">
            Profile
        </a>
    </li>

    <li>
        <hr>
    </li>

    <li>

        <form method="POST" action="{{ route('logout') }}">

            @csrf

            <button type="submit" class="dropdown-logout">

                Logout

            </button>

        </form>

    </li>

</ul>
                    </div>
                @endauth
            </div>

        </nav>
    </div>
</header>