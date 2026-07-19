<header class="site-header">
    <div class="container">
        <nav class="navbar">

            <!-- Logo with subtle red/cyan offset -->
            <a href="{{ route('home') }}" class="logo">
                <span class="logo-text logo-red">V</span>
                <span class="logo-text logo-cyan">o</span>
                <span class="logo-text">luma</span>
            </a>

            <ul class="nav-links">
                <li><a href="#how">How It Works</a></li>
                <li><a href="#examples">Examples</a></li>
                <li><a href="#credits">Credits</a></li>
            </ul>

            <div class="nav-actions">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-ghost">Log In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Start Free</a>
                @endguest

                @auth
                    <span class="credits-badge">
                        <span class="credits-icon">⚡</span>
                        {{ Auth::user()->credits ?? 50 }}
                    </span>
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
                    <div class="user-dropdown" id="userDropdown">
                        <button class="user-btn">
                            {{ Auth::user()->name }}
                            <svg width="12" height="12" viewBox="0 0 12 12" class="dropdown-arrow">
                                <path d="M6 8L1 3h10z" fill="currentColor"/>
                            </svg>
                        </button>
                        <ul class="dropdown-menu" id="dropdownMenu">
                            <li><a href="{{ route('profile') }}">Profile</a></li>
                            <li><a href="{{ route('history') }}">History</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-logout">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>

        </nav>
    </div>
</header>