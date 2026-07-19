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

                    <div class="upload-icon">

                        📷

                    </div>

                    <h3>

                        Drag & Drop Your Image

                    </h3>

                    <p>

                        or click below to browse

                    </p>

                    <button class="btn btn-primary" id="browseBtn">

                        Browse Image

                    </button>

                    <input
                        type="file"
                        id="fileInput"
                        accept="image/*"
                        hidden
                    >

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

                    <div class="viewer-placeholder">

                        3D Preview

                    </div>

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

                    <button class="btn btn-secondary" disabled>

                        Download

                    </button>

                    <button class="btn btn-primary" disabled>

                        Renew

                    </button>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection