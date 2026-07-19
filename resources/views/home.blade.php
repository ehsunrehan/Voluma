@extends('layouts.app')

@section('title', 'Voluma — 3D Model Generator')

@section('content')

@guest




<!-- HERO -->
<section class="hero">

    <div class="container">

        <div class="hero-grid">

            <!-- LEFT -->

            <div class="hero-content">

                <span class="hero-badge">

                    IMAGE → 3D AI PIPELINE

                </span>

                <h1 class="hero-title">

                    Create Stunning

                    <span>3D Models</span>

                    From a Single Image.

                </h1>

                <p class="hero-description">

                    Upload any product photo and let Voluma automatically remove
                    the background, generate a professional 3D model, preview it
                    in your browser and download it as a GLB file.

                </p>

                <div class="hero-buttons">

                    <a href="{{ route('register') }}" class="btn btn-primary">

                        Start Creating

                    </a>

                    <a href="#features" class="btn btn-secondary">

                        How It Works

                    </a>

                </div>

                <div class="hero-features">

                    <span>PNG / JPG</span>

                    <span>remove.bg</span>

                    <span>Tripo AI</span>

                    <span>GLB Export</span>

                </div>

            </div>

            <!-- RIGHT -->

            <div class="hero-preview">

                <div class="pipeline-card">

                    <div class="pipeline-item">

                        <div class="pipeline-icon"></div>

                        <div>

                            <h4>Upload Image</h4>

                            <p>Select any product photo</p>

                        </div>

                    </div>

                    <div class="pipeline-line"></div>

                    <div class="pipeline-item">

                        <div class="pipeline-icon coral"></div>

                        <div>

                            <h4>Background Removed</h4>

                            <p>Powered by remove.bg</p>

                        </div>

                    </div>

                    <div class="pipeline-line"></div>

                    <div class="pipeline-item">

                        <div class="pipeline-icon cyan"></div>

                        <div>

                            <h4>Generate 3D Model</h4>

                            <p>Processed with Tripo AI</p>

                        </div>

                    </div>

                    <div class="pipeline-line"></div>

                    <div class="pipeline-item">

                        <div class="pipeline-icon success"></div>

                        <div>

                            <h4>Download GLB</h4>

                            <p>Ready for games & AR</p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



<section id="how" class="how-section">

    <div class="container">

        <div class="section-heading">

            <span class="section-tag">
                FLAT IN, DIMENSIONAL OUT
            </span>

            <h2>
                From a plain photo to something
                <br>
                you can rotate.
            </h2>

        </div>

        <div class="how-grid">

            <div class="how-card">

                <span class="how-label">
                    Your photo
                </span>

                <div class="how-image">

                    📷

                </div>

            </div>

            <div class="how-arrow">

                →

            </div>

            <div class="how-card active">

                <span class="how-label">
                    Your 3D model
                </span>

                <div class="how-image">

                    🧊

                </div>

            </div>

        </div>

    </div>

</section>




<section id="examples" class="examples-section">

    <div class="container">

        <div class="section-heading">

            <span class="section-tag">
                AI GENERATED EXAMPLES
            </span>

            <h2>
                See what your images
                become.
            </h2>

            <p class="section-subtitle">
                Upload a single photo and receive a downloadable
                3D model in minutes.
            </p>

        </div>

        <div class="examples-grid">

            <!-- Example 1 -->

            <div class="example-card">

                <div class="example-box">

                    <span class="example-label">
                        Your Photo
                    </span>

                    <div class="example-image">

                        🍔

                    </div>

                </div>

                <div class="example-arrow">

                    →

                </div>

                <div class="example-box result">

                    <span class="example-label">

                        3D Model

                    </span>

                    <div class="example-image">

                        🧊

                    </div>

                </div>

            </div>

            <!-- Example 2 -->

            <div class="example-card">

                <div class="example-box">

                    <span class="example-label">

                        Your Photo

                    </span>

                    <div class="example-image">

                        🧴

                    </div>

                </div>

                <div class="example-arrow">

                    →

                </div>

                <div class="example-box result">

                    <span class="example-label">

                        3D Model

                    </span>

                    <div class="example-image">

                        🧊

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>





<section id="credits" class="credits-section">

    <div class="container">

        <div class="section-heading">

            <span class="section-tag">
                CREDITS
            </span>

            <h2>
                Simple, honest, and free to try.
            </h2>

            <p class="section-subtitle">

                Every new account receives free credits to start creating
                professional 3D models instantly.

            </p>

        </div>

        <div class="credits-grid">

            <!-- LEFT CARD -->

            <div class="credit-card featured">

                <div class="credit-badge">
                    Included with every account
                </div>

                <h3>
                    Free Account
                </h3>

                <div class="credit-number">

                    <span>50</span>

                    credits

                </div>

                <ul>

                    <li>✔ 50 Credits after registration</li>

                    <li>✔ Create 5 complete 3D models</li>

                    <li>✔ Automatic background removal</li>

                    <li>✔ Personal gallery & history</li>

                </ul>

            </div>

            <!-- RIGHT CARD -->

            <div class="credit-card">

                <h3>
                    Per Model
                </h3>

                <div class="credit-number">

                    <span>10</span>

                    credits

                </div>

                <ul>

                    <li>✔ Credits deducted only after success</li>

                    <li>✔ Download GLB anytime</li>

                    <li>✔ Renew any model anytime</li>

                    <li>✔ Live credit balance in dashboard</li>

                </ul>

            </div>

        </div>

    </div>

</section>





<!-- CTA -->
<section style="padding:80px 0 100px;">
    <div class="container">
        <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius-xl); padding:60px; text-align:center; position:relative; overflow:hidden;">
            <div style="position:absolute; top:-50%; right:-20%; width:400px; height:400px; background:radial-gradient(circle, var(--coral-glow), transparent 60%); filter:blur(60px); pointer-events:none;"></div>
            <h2 style="font-size:38px; margin-bottom:12px; position:relative;">Ready to create your first 3D model?</h2>
            <p style="color:var(--text-secondary); font-size:17px; margin-bottom:28px; position:relative;">Sign up now and get 50 credits free — no credit card needed.</p>
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg" style="position:relative;">
                Start Free — 50 Credits
            </a>
        </div>
    </div>
</section>

@endguest




@endsection