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

                    IMAGE / TEXT → 3D AI PIPELINE

                </span>

                <h1 class="hero-title">

                    Create Stunning

                    <span>3D Models</span>

                    From an Image or a Prompt.

                </h1>

                <p class="hero-description">

                    Upload any product photo, or simply describe what you want in words —
                    Voluma automatically removes the background (for images), generates a
                    professional 3D model, previews it in your browser, and lets you
                    download it as a GLB file.

                </p>

                <div class="hero-buttons">

                    <a href="{{ route('register') }}" class="btn btn-primary">

                        Start Creating

                    </a>

                    <a href="#how" class="btn btn-secondary">

                        How It Works

                    </a>

                </div>

                <div class="hero-features">

                    <span>PNG / JPG</span>

                    <span>Text Prompt</span>

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

                            <h4>Upload Image or Write a Prompt</h4>

                            <p>Pick a product photo, or describe it in text</p>

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
                HOW IT WORKS
            </span>

            <h2>
                Three steps. About a minute.
            </h2>

            <p class="section-subtitle">
                No 3D knowledge needed. If you can upload a photo or type a sentence, you can make a model.
            </p>

        </div>

        <div class="how-steps-grid">

            <div class="how-step-card">
                <span class="how-step-number">01</span>
                <div class="how-step-icon">📤</div>
                <h3 class="how-step-title">Upload a photo or type a prompt</h3>
                <p class="how-step-desc">
                    Drop in a single image of your product, or simply describe it in words — a burger, a shoe, a bottle, anything.
                </p>
            </div>

            <div class="how-step-card">
                <span class="how-step-number">02</span>
                <div class="how-step-icon">✂️</div>
                <h3 class="how-step-title">We clean it up</h3>
                <p class="how-step-desc">
                    The background is stripped away automatically, so the model only captures your product.
                </p>
            </div>

            <div class="how-step-card">
                <span class="how-step-number">03</span>
                <div class="how-step-icon">🧊</div>
                <h3 class="how-step-title">Get your 3D model</h3>
                <p class="how-step-desc">
                    Spin it, view it from every angle, and download the file to use anywhere.
                </p>
            </div>

        </div>

    </div>

</section>


<section id="examples" class="examples-section">

    <div class="container">

        <div class="section-heading">

            <span class="section-tag">
                EXAMPLES
            </span>

            <h2>
                See what your images or words
                become.
            </h2>

            <p class="section-subtitle">
                Upload a single photo, or just describe your idea in text,
                and receive a downloadable 3D model in minutes.
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

            <!-- Example 2 (Text-to-3D) -->

            <div class="example-card">

                <div class="example-box example-box-text">

                    <span class="example-label">

                        Your Prompt

                    </span>

                    <p class="example-prompt-text">
                        "A cute cartoon fox sitting, orange fur, big eyes"
                    </p>

                </div>

                <div class="example-arrow">

                    →

                </div>

                <div class="example-box result">

                    <span class="example-label">

                        3D Model

                    </span>

                    <div class="example-image">

                        🦊

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

                    <li>✔ Personal gallery </li>

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

                    <li>✔ Works for both Image-to-3D and Text-to-3D</li>

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