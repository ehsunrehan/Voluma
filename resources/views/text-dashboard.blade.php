@extends('layouts.app')

@section('title','Text to 3D')

@section('content')

<section class="dashboard">
    <div class="container">
        <div class="dashboard-grid">

            <!-- Prompt Card -->
            <div class="dashboard-card">

                <div class="card-header">
                    <h2 class="card-title">Describe Your Model</h2>
                    <p class="card-desc">
                        Write a detailed description and generate a professional 3D model directly from text.
                    </p>
                </div>

                <div class="text-prompt-area">

                    <textarea
                        id="promptInput"
                        class="prompt-textarea"
                        placeholder="e.g. A cute cartoon fox sitting, orange fur, big eyes, simple pose"></textarea>

                    <button
                        class="btn btn-primary"
                        id="generateTextBtn"
                        data-low-credits="{{ $credits <= 0 ? '1' : '0' }}">
                        Generate 3D
                    </button>

                </div>

            </div>

            <!-- Preview -->
            <div class="dashboard-card">

                <div class="card-header">
                    <h2 class="card-title">3D Preview</h2>
                    <p class="card-desc">Your generated model will appear here.</p>
                </div>

                <div class="viewer-area">

                    <div class="viewer-placeholder" id="viewerPlaceholder">3D Preview</div>

                    <div id="loadingOverlay">
                        <div class="loader"></div>
                        <div id="loadingPercent">0%</div>
                        <div class="progress-bar">
                            <div id="progressFill"></div>
                        </div>
                        <div id="loadingText">Uploading AI Input...</div>
                    </div>

                    <div id="particleLoader">
                        <div class="particle-logo">✦</div>
                        <span>Finalizing Preview...</span>
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
                    <span class="label">Status</span>
                    <span class="value" id="statusText">Waiting for prompt...</span>
                </div>

                <div class="dashboard-actions">
                    <button id="downloadBtn" class="btn btn-secondary" disabled>Download</button>
                    <button id="renewBtn" class="btn btn-primary" disabled>Renew</button>
                </div>

            </div>

        </div>
    </div>
</section>

<section id="how" class="how-section">
    <div class="container">
        <div class="section-heading">
            <span class="section-tag">TEXT IN, DIMENSIONAL OUT</span>
            <h2>
                From a written idea to something
                <br>
                you can rotate.
            </h2>
        </div>

        <div class="how-grid">
            <div class="how-card">
                <span class="how-label">Your prompt</span>
                <div class="how-image">📝</div>
            </div>

            <div class="how-arrow">→</div>

            <div class="how-card active">
                <span class="how-label">Your 3D model</span>
                <div class="how-image">🧊</div>
            </div>
        </div>
    </div>
</section>

<!-- Download Modal -->
<div id="downloadModalOverlay" class="download-modal-overlay" style="display:none;">
    <div class="download-modal-box">

        <h3 class="download-modal-title">Download 3D Model</h3>

        <label class="download-modal-label" for="downloadFileName">File Name</label>
        <input type="text" id="downloadFileName" class="download-modal-input" placeholder="Enter file name">

        <div class="download-modal-row">
            <div class="download-modal-col">
                <label class="download-modal-label" for="downloadFileType">File Type</label>
                <select id="downloadFileType" class="download-modal-select">
                    <option value="" selected disabled>Select type</option>
                    <option value="glb">.GLB</option>
                </select>
            </div>
            <div class="download-modal-col">
                <label class="download-modal-label">&nbsp;</label>
                <button id="confirmDownloadBtn" class="btn btn-primary download-modal-confirm">Download</button>
            </div>
        </div>

        <p id="downloadTypeError" class="download-modal-error" style="display:none;">Please select a file type.</p>

        <button id="closeDownloadModal" class="download-modal-close">Cancel</button>

    </div>
</div>

<script src="{{ asset('website/js/text-app.js') }}"></script>

@endsection