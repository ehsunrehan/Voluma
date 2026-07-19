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





<!-- FEATURES (Simple Grid) -->
<section id="features" style="padding:80px 0; border-top:1px solid var(--border);">
    <div class="container">
        <div style="text-align:center; max-width:600px; margin:0 auto 60px;">
            <span style="display:inline-block; padding:4px 16px; background:var(--coral-soft); border-radius:100px; font-size:11px; font-weight:600; color:var(--coral); text-transform:uppercase; letter-spacing:.08em; margin-bottom:12px;">
                Features
            </span>
            <h2 style="font-size:40px; font-weight:700; margin-bottom:12px;">
                Simple. Fast. Powerful.
            </h2>
            <p style="color:var(--text-secondary); font-size:17px;">
                Everything you need to create professional 3D models from any photo.
            </p>
        </div>
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:24px;">
            <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius); padding:32px; transition:all .3s;">
                <div style="font-size:36px; margin-bottom:16px;">📤</div>
                <h3 style="font-size:18px; margin-bottom:8px;">Upload Photo</h3>
                <p style="color:var(--text-secondary); font-size:15px;">Drop any product image — works with anything.</p>
            </div>
            <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius); padding:32px; transition:all .3s;">
                <div style="font-size:36px; margin-bottom:16px;">🤖</div>
                <h3 style="font-size:18px; margin-bottom:8px;">AI Processing</h3>
                <p style="color:var(--text-secondary); font-size:15px;">Background removed, 3D model generated automatically.</p>
            </div>
            <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius); padding:32px; transition:all .3s;">
                <div style="font-size:36px; margin-bottom:16px;">🧊</div>
                <h3 style="font-size:18px; margin-bottom:8px;">View & Download</h3>
                <p style="color:var(--text-secondary); font-size:15px;">Spin it, inspect it, download .glb file instantly.</p>
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