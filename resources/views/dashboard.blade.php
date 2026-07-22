@extends('layouts.app')

@section('title','Dashboard')

@section('content')

<section class="dashboard">

    <div class="container">

        <div class="dashboard-grid">

            <!-- Upload Card -->

            <div class="dashboard-card">

                <div class="card-header">

                    <h2 class="card-title">

                        Upload Image

                    </h2>

                    <p class="card-desc">

                        Upload a PNG, JPG or JPEG image to generate a professional 3D model.

                    </p>

                </div>

                <div class="upload-area" id="uploadArea">

    <!-- Upload State -->

    <div id="uploadState">

        <div class="upload-icon">

            📷

        </div>

        <h3>

            Drag & Drop Your Image

        </h3>

        <p>

            or click below to browse

        </p>

        <button
            class="btn btn-primary"
            id="browseBtn">

            Browse Image

        </button>

    </div>

<input
  type="file"
  id="fileInput"
  accept="image/*"
  hidden
>

    <!-- Preview State -->

    <div
        id="previewState"
        style="display:none;">

        <img
            id="uploadedPreview"
            src=""
            alt="Preview">

        <div class="image-info">

            <h4 id="imageName">

            </h4>

            <span id="imageSize">

            </span>

        </div>

        <div class="preview-buttons">

            <button
                class="btn btn-secondary"
                id="replaceBtn">

                Re-upload

            </button>

            <button
                class="btn btn-primary"
                id="generateBtn"
                @if($credits<=0) disabled @endif>
                Generate 3D
            </button>
            
            @if($credits<=0)
                <p
                id="creditWarning"
                style="margin-top:15px;color:#ff6b6b;text-align:center;">
                You don't have enough credits.

                </p>

            @endif

        </div>

    </div>

</div>

            </div>

            <!-- Preview -->

            <div class="dashboard-card">

                <div class="card-header">

                    <h2 class="card-title">

                        3D Preview

                    </h2>

                    <p class="card-desc">

                        Your generated model will appear here.

                    </p>

                </div>

                <div class="viewer-area">

                    <div class="viewer-placeholder" id="viewerPlaceholder">

                        3D Preview

                    </div>

                    <img
                    id="previewImage"
                    src=""
                    alt="Preview"
                    style="display:none;"
                    >




                    <div id="loadingOverlay">
                        <div class="loader"></div>
                        <div id="loadingPercent">0%</div>
                        <div class="progress-bar">
                            <div id="progressFill"></div>
                        </div>
                        <div id="loadingText">
                            Uploading AI Input...
                        </div>
                    </div>

                    
                    <div id="particleLoader">
                        <div class="particle-logo">
                            ✦
                        </div>
                        <span>
                            Finalizing Preview...
                        </span>
                    </div>



                    <model-viewer
                        id="modelViewer"
                        src=""
                        camera-controls
                        auto-rotate
                        shadow-intensity="1"
                        style="display:none;width:100%;height:100%;">
                    </model-viewer>








                </div>

                <div class="status-box">

                    <span class="label">

                        Status

                    </span>

                    <span class="value" id="statusText">

                        Waiting for image...

                    </span>

                </div>

                <div class="dashboard-actions">

                    <button id="downloadBtn" class="btn btn-secondary" disabled>

                        Download

                    </button>

                    <button id="renewBtn" class="btn btn-primary" disabled>

                        Renew

                    </button>

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

                    <span id="creditCount">{{ $credits }}</span>

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









@endsection