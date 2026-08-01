@extends('layouts.app')

@section('title','Gallery')

@section('content')

<section class="gallery-section">
    <div class="container">

        <div class="section-heading">
            <span class="section-tag" style="margin-top: 20px;">YOUR CREATIONS</span>
            <h2>Gallery</h2>
            <p class="gallery-heading-note">Models older than 24 hours are automatically removed from your gallery.</p>
        </div>

        <div class="gallery-grid">
            @forelse($generations as $generation)
                @php
                    $isText = $generation->source_type === 'text';

                    if ($isText) {
                        $cleanName = $generation->prompt ? \Illuminate\Support\Str::limit($generation->prompt, 28) : 'Text Model';
                    } else {
                        $rawName = basename($generation->original_image ?? '');
                        $cleanName = preg_replace('/^\d+_/', '', $rawName);
                        $cleanName = pathinfo($cleanName, PATHINFO_FILENAME);
                    }

                    $sizeLabel = '—';
                    if ($generation->file_size) {
                        $bytes = (int) $generation->file_size;
                        if ($bytes >= 1048576) {
                            $sizeLabel = round($bytes / 1048576, 2) . ' MB';
                        } else {
                            $sizeLabel = round($bytes / 1024, 1) . ' KB';
                        }
                    }
                @endphp

                <div class="gallery-card"
                     data-task-id="{{ $generation->task_id }}"
                     data-name="{{ $cleanName }}"
                     data-model-url="{{ $generation->tripo_url }}"
                     @if($isText) data-prompt="{{ $generation->prompt }}" @endif>

                    @if($isText)
                        <div class="gallery-card-image gallery-card-image-text gallery-prompt-trigger">
                            <span class="gallery-text-preview">prompt</span>
                        </div>
                    @else
                        <div class="gallery-card-image gallery-image-trigger">
                            <img src="{{ asset('storage/'.$generation->original_image) }}" alt="{{ $cleanName }}">
                        </div>
                    @endif

                    <div class="gallery-card-name">{{ $cleanName }}</div>
                    <div class="gallery-card-size">{{ $sizeLabel }}</div>
                    <div class="gallery-card-source">{{ $isText ? 'Text-to-3D' : 'Image-to-3D' }}</div>

                    <div class="gallery-card-actions">
                        <button class="btn btn-secondary gallery-view-btn">View 3D</button>
                        <button class="btn btn-primary gallery-download-btn">Download</button>
                    </div>

                </div>
            @empty
                <p class="gallery-empty">No models yet. Generate your first 3D model to see it here.</p>
            @endforelse
        </div>

    </div>
</section>

<!-- 3D Model View Modal -->
<div id="galleryViewModalOverlay" class="gallery-view-overlay" style="display:none;">
    <div class="gallery-view-box">
        <button id="closeGalleryViewModal" class="gallery-view-close">✕</button>

            <div id="galleryModelViewerContainer" style="width:100%;flex:1;"></div>

        <div class="gallery-view-actions">
            <button id="galleryViewDownloadBtn" class="btn btn-primary">Download</button>
        </div>
    </div>
</div>

<!-- Prompt View Modal (read-only) -->
<div id="promptViewModalOverlay" class="gallery-view-overlay" style="display:none;">
    <div class="prompt-view-box">
        <button id="closePromptModal" class="gallery-view-close">✕</button>
        <h3 class="download-modal-title">Inserted Prompt</h3>
        <div class="prompt-view-text" id="promptViewText"></div>
        <button id="promptViewOpenModelBtn" class="btn btn-primary" style="margin-top:16px;width:100%;">
            View 3D Model
        </button>
    </div>
</div>

<!-- Original Image Preview Modal -->
<div id="imagePreviewModalOverlay" class="gallery-view-overlay" style="display:none;">
    <div class="image-preview-box">
        <button id="closeImagePreviewModal" class="gallery-view-close">✕</button>
        <img id="imagePreviewImg" src="" alt="Original image">
    </div>
</div>

<!-- Download Modal -->
<div id="galleryDownloadModalOverlay" class="download-modal-overlay" style="display:none;">
    <div class="download-modal-box">

        <h3 class="download-modal-title">Download 3D Model</h3>

        <label class="download-modal-label" for="galleryDownloadFileName">File Name</label>
        <input
            type="text"
            id="galleryDownloadFileName"
            class="download-modal-input"
            placeholder="Enter file name">

        <div class="download-modal-row">

            <div class="download-modal-col">
                <label class="download-modal-label" for="galleryDownloadFileType">File Type</label>
                <select id="galleryDownloadFileType" class="download-modal-select">
                    <option value="" selected disabled>Select type</option>
                    <option value="glb">.GLB</option>
                </select>
            </div>

            <div class="download-modal-col">
                <label class="download-modal-label">&nbsp;</label>
                <button id="galleryConfirmDownloadBtn" class="btn btn-primary download-modal-confirm">
                    Download
                </button>
            </div>

        </div>

        <p id="galleryDownloadTypeError" class="download-modal-error" style="display:none;">
            Please select a file type.
        </p>

        <button id="closeGalleryDownloadModal" class="download-modal-close">
            Cancel
        </button>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const viewModalOverlay = document.getElementById('galleryViewModalOverlay');
    const galleryModelViewerContainer = document.getElementById('galleryModelViewerContainer');

    function createFreshModelViewer(src) {
        galleryModelViewerContainer.innerHTML = '';
        const mv = document.createElement('model-viewer');
        mv.setAttribute('camera-controls', '');
        mv.setAttribute('auto-rotate', '');
        mv.setAttribute('shadow-intensity', '1');
        mv.setAttribute('loading', 'eager');
        mv.setAttribute('reveal', 'auto');
        mv.style.width = '100%';
        mv.style.height = '100%';
        galleryModelViewerContainer.appendChild(mv);
        mv.setAttribute('src', src);
    }
    const closeViewModal = document.getElementById('closeGalleryViewModal');
    const galleryViewDownloadBtn = document.getElementById('galleryViewDownloadBtn');

    const promptViewModalOverlay = document.getElementById('promptViewModalOverlay');
    const promptViewText = document.getElementById('promptViewText');
    const closePromptModal = document.getElementById('closePromptModal');
    const promptViewOpenModelBtn = document.getElementById('promptViewOpenModelBtn');

    const imagePreviewModalOverlay = document.getElementById('imagePreviewModalOverlay');
    const imagePreviewImg = document.getElementById('imagePreviewImg');
    const closeImagePreviewModal = document.getElementById('closeImagePreviewModal');

    const downloadModalOverlay = document.getElementById('galleryDownloadModalOverlay');
    const downloadFileName = document.getElementById('galleryDownloadFileName');
    const downloadFileType = document.getElementById('galleryDownloadFileType');
    const downloadTypeError = document.getElementById('galleryDownloadTypeError');
    const confirmDownloadBtn = document.getElementById('galleryConfirmDownloadBtn');
    const closeDownloadModal = document.getElementById('closeGalleryDownloadModal');

    let activeTaskId = null;
    let activeName = 'model';
    let activeModelUrl = null;

    function openModelView(taskId, name, modelUrl) {
        activeTaskId = taskId;
        activeName = name;
        activeModelUrl = modelUrl;

        viewModalOverlay.style.display = 'flex';

        const src = '/stream-model/' + taskId;

        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                createFreshModelViewer(src);
            });
        });
    }

    document.querySelectorAll('.gallery-card').forEach(function (card) {

        const taskId = card.dataset.taskId;
        const name = card.dataset.name;
        const modelUrl = card.dataset.modelUrl;
        const prompt = card.dataset.prompt;

        const viewBtn = card.querySelector('.gallery-view-btn');
        const downloadBtn = card.querySelector('.gallery-download-btn');
        const imageTrigger = card.querySelector('.gallery-image-trigger img');
        const promptTrigger = card.querySelector('.gallery-prompt-trigger');

        if (viewBtn) {
            viewBtn.addEventListener('click', function () {
                openModelView(taskId, name, modelUrl);
            });
        }

        if (imageTrigger) {
            imageTrigger.addEventListener('click', function () {
                openModelView(taskId, name, modelUrl);
            });
        }

        if (promptTrigger) {
            promptTrigger.addEventListener('click', function () {
                promptViewText.textContent = prompt || '';
                promptViewOpenModelBtn.dataset.taskId = taskId;
                promptViewOpenModelBtn.dataset.name = name;
                promptViewOpenModelBtn.dataset.modelUrl = modelUrl || '';
                promptViewModalOverlay.style.display = 'flex';
            });
        }

        if (downloadBtn) {
            downloadBtn.addEventListener('click', function () {
                activeTaskId = taskId;
                activeName = name;
                openDownloadModal();
            });
        }

    });

    if (promptViewOpenModelBtn) {
        promptViewOpenModelBtn.addEventListener('click', function () {
            promptViewModalOverlay.style.display = 'none';
            openModelView(
                promptViewOpenModelBtn.dataset.taskId,
                promptViewOpenModelBtn.dataset.name,
                promptViewOpenModelBtn.dataset.modelUrl
            );
        });
    }

    if (closePromptModal) {
        closePromptModal.addEventListener('click', function () {
            promptViewModalOverlay.style.display = 'none';
        });
    }

    if (closeImagePreviewModal) {
        closeImagePreviewModal.addEventListener('click', function () {
            imagePreviewModalOverlay.style.display = 'none';
            imagePreviewImg.src = '';
        });
    }

    function openDownloadModal() {
        downloadFileName.value = activeName;
        downloadFileType.value = "";
        downloadTypeError.style.display = "none";
        downloadModalOverlay.style.display = "flex";
    }

    if (closeViewModal) {
        closeViewModal.addEventListener('click', function () {
            viewModalOverlay.style.display = 'none';
            galleryModelViewerContainer.innerHTML = '';
        });
    }

    if (galleryViewDownloadBtn) {
        galleryViewDownloadBtn.addEventListener('click', function () {
            openDownloadModal();
        });
    }

    if (closeDownloadModal) {
        closeDownloadModal.addEventListener('click', function () {
            downloadModalOverlay.style.display = "none";
        });
    }

    if (confirmDownloadBtn) {
        confirmDownloadBtn.addEventListener('click', function () {

            const selectedType = downloadFileType.value;

            if (!selectedType) {
                downloadTypeError.style.display = "block";
                return;
            }

            downloadTypeError.style.display = "none";

            const finalName = (downloadFileName.value || '').trim() || "model";

            confirmDownloadBtn.disabled = true;
            confirmDownloadBtn.textContent = "Preparing...";

            fetch('/download/model', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    task_id: activeTaskId,
                    type: selectedType,
                    name: finalName
                })
            })
                .then(res => res.json())
                .then(data => {

                    confirmDownloadBtn.disabled = false;
                    confirmDownloadBtn.textContent = "Download";

                    if (!data.success) {
                        downloadTypeError.textContent = data.message || 'Download failed.';
                        downloadTypeError.style.display = "block";
                        return;
                    }


                    
                    window.location.href = data.download_url;
                    downloadModalOverlay.style.display = "none";


                })
                .catch(function () {
                    confirmDownloadBtn.disabled = false;
                    confirmDownloadBtn.textContent = "Download";
                    downloadTypeError.textContent = 'Something went wrong.';
                    downloadTypeError.style.display = "block";
                });

        });
    }

});
</script>

@endsection