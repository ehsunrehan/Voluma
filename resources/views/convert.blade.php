@extends('layouts.app')

@section('title','Convert')

@section('content')

<script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>

<div id="convertPopupContainer" class="convert-popup-container"></div>
<div class="convert-page">

    <div class="convert-row convert-row-top">
        <div class="convert-upload-box convert-clickable" id="convertViewerWrap">

        <div id="convertUploadBigLoader" class="convert-loader" style="display:none;">
            <div class="convert-loader-ring"></div>
            <div class="convert-loader-percent" id="convertUploadBigPercent">0%</div>
            <div class="convert-loader-label">Uploading file...</div>
        </div>

        <div id="convertBgUploadBadge" class="convert-bg-upload-badge" style="display:none;">
            <span id="convertBgUploadPercent">0%</span>
        </div>

        <div class="convert-upload-controls">
            <button type="button" id="convertUploadBtn" class="convert-btn-secondary">Upload File</button>
            <span id="convertUploadName" class="convert-upload-name" style="display:none;"></span>
            <button type="button" id="convertReuploadBtn" class="convert-btn-secondary" style="display:none;">Reupload</button>
            <span id="convertUploadSize" class="convert-upload-size" style="display:none;"></span>
        </div>

            <model-viewer id="convertModelViewer" class="convert-model-viewer" camera-controls auto-rotate style="display:none;"></model-viewer>
            <div id="convertUploadFileCard" class="convert-upload-file-card" style="display:none;">
                <svg viewBox="0 0 64 64" width="44" height="44" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="32,4 58,18 58,46 32,60 6,46 6,18" fill="none" stroke="#4ecdc4" stroke-width="2"/>
                    <polyline points="6,18 32,32 58,18" fill="none" stroke="#4ecdc4" stroke-width="2"/>
                    <line x1="32" y1="32" x2="32" y2="60" stroke="#4ecdc4" stroke-width="2"/>
                    <polyline points="19,11 45,25" stroke="#ff6b6b" stroke-width="2" stroke-dasharray="3,3"/>
                </svg>
                <div class="convert-upload-file-info">
                    <div class="convert-upload-file-name" id="convertUploadFileName"></div>
                    <div class="convert-upload-file-size" id="convertUploadFileSize"></div>
                </div>
            </div>
            <div id="convertPlaceholder" class="convert-placeholder">
                <p>Click here to upload a 3D file</p>
            </div>

            <div id="convertUploadLoader" class="convert-loader" style="display:none;">
                <div class="convert-loader-ring"></div>
                <div class="convert-loader-percent" id="convertUploadLoaderPercent">0%</div>
                <div class="convert-loader-label">Uploading file...</div>
            </div>

            <div id="convertLoader" class="convert-loader" style="display:none;">
                <div class="convert-loader-ring"></div>
                <div class="convert-loader-percent" id="convertLoaderPercent">0%</div>
                <div class="convert-loader-label">Rebuilding geometry...</div>
            </div>

  
        </div>

        <div class="convert-format-box">
            <label for="convertFormatSelect">Convert To</label>
            <select id="convertFormatSelect" class="convert-dropdown">
                <option value="">Select format</option>
                @foreach($formats as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <p class="convert-error-text" id="convertFormatError" style="display:none;">Please choose a format first.</p>
        </div>
    </div>

    <p class="convert-error-text convert-error-center" id="convertFileError" style="display:none;">Please upload a file first.</p>

    <div class="convert-row convert-row-mid">
        <button type="button" id="convertBtn" class="convert-btn-primary">Convert</button>
    </div>

    <div class="convert-row convert-row-bottom" id="convertResultRow">
        <div class="convert-result-placeholder" id="convertResultPlaceholder">
            <p>Your converted model will appear here</p>
        </div>
        <div class="convert-result-box" id="convertResultBox" style="display:none;">
            <model-viewer id="convertResultViewer" class="convert-model-viewer" camera-controls auto-rotate style="display:none;"></model-viewer>
            <div id="convertResultFileCard" class="convert-result-file-card" style="display:none;">
                <div class="convert-file-icon">
                    <svg viewBox="0 0 64 64" width="56" height="56" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="32,4 58,18 58,46 32,60 6,46 6,18" fill="none" stroke="#4ecdc4" stroke-width="2"/>
                        <polyline points="6,18 32,32 58,18" fill="none" stroke="#4ecdc4" stroke-width="2"/>
                        <line x1="32" y1="32" x2="32" y2="60" stroke="#4ecdc4" stroke-width="2"/>
                        <polyline points="19,11 45,25" stroke="#ff6b6b" stroke-width="2" stroke-dasharray="3,3"/>
                    </svg>
                </div>
                <div class="convert-file-label">Model converted successfully</div>
</div>
<div class="convert-download-row">
    <div class="convert-download-info">
        <span id="convertResultLabelText" class="convert-download-name"></span>
        <span id="convertResultLabelSize" class="convert-download-size"></span>
    </div>
    <button type="button" id="convertDownloadBtn" class="convert-btn-primary">Download</button>
</div>
        </div>
    </div>

</div>

<input type="file" id="convertFileInput" accept=".glb,.gltf,.stl,.obj,.fbx,.dae,.ply,.3mf,.usdz,.stp" hidden>

<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const MAX_UPLOAD_BYTES = 100 * 1024 * 1024; // 100MB

    const fileInput = document.getElementById('convertFileInput');
    const uploadBtn = document.getElementById('convertUploadBtn');
    const reuploadBtn = document.getElementById('convertReuploadBtn');
    const fileError = document.getElementById('convertFileError');
    const formatSelect = document.getElementById('convertFormatSelect');
    const formatError = document.getElementById('convertFormatError');
    const convertBtn = document.getElementById('convertBtn');

    const placeholder = document.getElementById('convertPlaceholder');
    const modelViewer = document.getElementById('convertModelViewer');
    const loader = document.getElementById('convertLoader');
    const loaderPercent = document.getElementById('convertLoaderPercent');

    const bigUploadLoader = document.getElementById('convertUploadBigLoader');
    const bigUploadPercent = document.getElementById('convertUploadBigPercent');

    const bgBadge = document.getElementById('convertBgUploadBadge');
    const bgBadgePercent = document.getElementById('convertBgUploadPercent');

    const resultPlaceholder = document.getElementById('convertResultPlaceholder');
    const resultBox = document.getElementById('convertResultBox');
    const resultViewer = document.getElementById('convertResultViewer');
    const downloadBtn = document.getElementById('convertDownloadBtn');

    let uploadedPath = null;
    let uploadedFormat = null;
    let uploadedHash = null;
    let fileSelected = false;
    let isUploadingInBackground = false;
    let isConverting = false;
    let isFetchingStatus = false;
    let lastConvertedKey = null;
    let currentTargetFormat = null;
    let currentUploadXhr = null;
    let conversionStartTime = null;
    let convertingPopupEl = null;
    

    function setUploadButtonsState(state) {
    if (state === 'upload') {
        uploadBtn.style.display = 'inline-block';
        reuploadBtn.style.display = 'none';
    } else {
        uploadBtn.style.display = 'none';
        reuploadBtn.style.display = 'inline-block';
    }
}


    function formatBytes(bytes) {
        if (!bytes) return '';
        const mb = bytes / (1024 * 1024);
        if (mb >= 1) return mb.toFixed(2) + ' MB';
        return (bytes / 1024).toFixed(1) + ' KB';
    }

    uploadBtn.addEventListener('click', () => fileInput.click());
    reuploadBtn.addEventListener('click', () => fileInput.click());

    document.getElementById('convertViewerWrap').addEventListener('click', function (e) {
        if (e.target.closest('#convertUploadBtn') || e.target.closest('#convertReuploadBtn')) return;
        if (fileSelected) return;
        fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (currentUploadXhr) {
            currentUploadXhr.abort();
            currentUploadXhr = null;
        }
        if (!file) return;

        fileError.style.display = 'none';

        const ext = file.name.split('.').pop().toLowerCase();
        const allowed = ['glb','gltf','stl','obj','fbx','dae','ply','3mf','usdz','stp'];

        if (!allowed.includes(ext)) {
            showTopPopup('Please use a 3D modeling or printing format (GLB, STL, OBJ, FBX, etc.)', true);
            fileInput.value = '';
            return;
        }

        if (file.size > MAX_UPLOAD_BYTES) {
            showTopPopup('File size exceeds 100MB. Please upload a smaller 3D file.', true);
            fileInput.value = '';
            return;
        }

        // instant preview
placeholder.style.display = 'none';
fileSelected = true;

const sizeText = formatBytes(file.size);

if (ext === 'glb') {
    modelViewer.style.display = 'block';
    modelViewer.src = URL.createObjectURL(file);
    document.getElementById('convertUploadFileCard').style.display = 'none';

    document.getElementById('convertUploadName').textContent = file.name;
    document.getElementById('convertUploadName').style.display = 'inline-block';
    document.getElementById('convertUploadSize').textContent = sizeText;
    document.getElementById('convertUploadSize').style.display = 'inline-block';

    document.getElementById('convertViewerWrap').classList.add('convert-has-model');
} else {
    modelViewer.style.display = 'none';
    document.getElementById('convertUploadFileName').textContent = file.name;
    document.getElementById('convertUploadFileSize').textContent = sizeText;
    document.getElementById('convertUploadFileCard').style.display = 'flex';

    document.getElementById('convertUploadName').style.display = 'none';
    document.getElementById('convertUploadSize').style.display = 'none';

    document.getElementById('convertViewerWrap').classList.remove('convert-has-model');
}

setUploadButtonsState('reupload');

// ---- background upload (silent, for actual conversion later) ----


uploadedPath = null;
uploadedFormat = null;
uploadedHash = null;

const isGlbType = (ext === 'glb');

if (isGlbType) {
    modelViewer.classList.add('convert-blur');
    bigUploadLoader.style.display = 'flex';
    bigUploadPercent.textContent = '0%';
} else if (bgBadge) {
    bgBadge.style.display = 'flex';
    if (bgBadgePercent) bgBadgePercent.textContent = '0%';
}

const formData = new FormData();
formData.append('file', file);

if (currentUploadXhr) {
    currentUploadXhr.abort();
    currentUploadXhr = null;
}

const xhr = new XMLHttpRequest();
currentUploadXhr = xhr;
xhr.open('POST', '/convert/upload');
xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
xhr.setRequestHeader('Accept', 'application/json');

xhr.upload.addEventListener('progress', function (e) {
    if (e.lengthComputable) {
        const percent = Math.round((e.loaded / e.total) * 100);
        if (isGlbType) {
            bigUploadPercent.textContent = percent + '%';
        } else if (bgBadgePercent) {
            bgBadgePercent.textContent = percent + '%';
        }
    }
});

xhr.onload = function () {
    if (xhr !== currentUploadXhr) return;

    bigUploadLoader.style.display = 'none';
    if (bgBadge) bgBadge.style.display = 'none';
    modelViewer.classList.remove('convert-blur');

    let data;
    try {
        data = JSON.parse(xhr.responseText);
    } catch (err) {
        console.error('upload error: invalid response', xhr.status, xhr.responseText);
        showTopPopup('Upload failed, please reupload.', true);
        return;
    }

    if (!data.success) {
        if (data.errors && data.errors.file) {
            showTopPopup(data.errors.file[0], true);
        } else {
            showTopPopup('Upload failed, please reupload.', true);
        }
        return;
    }

    uploadedPath = data.path;
    uploadedFormat = data.format;
    uploadedHash = data.hash;

    showTopPopup('Your file has been uploaded successfully.', false);
};

xhr.onerror = function () {
    if (xhr !== currentUploadXhr) return;
    bigUploadLoader.style.display = 'none';
    if (bgBadge) bgBadge.style.display = 'none';
    modelViewer.classList.remove('convert-blur');
    console.error('upload network error');
    showTopPopup('Upload failed, please reupload.', true);
};

xhr.send(formData);

    });

    formatSelect.addEventListener('change', function () {
        if (this.value) formatError.style.display = 'none';
    });

    convertBtn.addEventListener('click', function () {
        let hasError = false;

        if (!fileSelected) {
            fileError.style.display = 'block';
            hasError = true;
        }
        if (!formatSelect.value) {
            formatError.style.display = 'block';
            hasError = true;
        }
        if (hasError) return;

        if (isUploadingInBackground) {
            showTopPopup('File is still uploading, please wait a moment.', true);
            return;
        }

        if (!uploadedPath) {
            showTopPopup('Upload failed, please reupload the file.', true);
            return;
        }

        if (isConverting) return;

        const targetFormat = formatSelect.value;
        currentTargetFormat = targetFormat;
        const requestKey = uploadedHash + '|' + targetFormat;

        if (requestKey === lastConvertedKey) {
            showTopPopup('Already converted.', false, true);
            return;
        }

        isConverting = true;
showLoader();
showConvertingPopup();
conversionStartTime = Date.now();
startFakeProgress();

fetch('/convert/start', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    body: JSON.stringify({ path: uploadedPath, from: uploadedFormat, to: targetFormat, hash: uploadedHash }),
})
.then(res => res.json())
.then(data => {
    if (!data.success) {
        isConverting = false;
        stopFakeProgress();
        hideLoader();
        hideConvertingPopup(function () {
            showTopPopup(data.message || 'Conversion could not start. Please try again.', true);
        });
        return;
    }

    if (data.already_converted) {
        isConverting = false;
        stopFakeProgress();
        hideLoader();
        hideConvertingPopup(function () {
            showTopPopup('Already converted.', false, true);
        });
        return;
    }

    startPolling(data.job_id, requestKey);
})
.catch(err => {
    console.error('start error:', err);
    isConverting = false;
    stopFakeProgress();
    hideLoader();
    hideConvertingPopup(function () {
        showTopPopup('Something went wrong.', true);
    });
});


    });

    let fakeProgressTimer = null;
    let fakeProgressValue = 0;

    function startFakeProgress() {
        fakeProgressValue = 0;
        updateLoaderPercent(0);
        const progressStartTime = Date.now();
        fakeProgressTimer = setInterval(function () {
            const elapsedSeconds = (Date.now() - progressStartTime) / 1000;
            const target = 92 * (1 - Math.exp(-elapsedSeconds / 45));
            fakeProgressValue = Math.floor(target);
            updateLoaderPercent(fakeProgressValue);
        }, 500);
    }

    function stopFakeProgress() {
        if (fakeProgressTimer) {
            clearInterval(fakeProgressTimer);
            fakeProgressTimer = null;
        }
    }

function startPolling(jobId, requestKey) {
    if (isFetchingStatus) return;
    
    if (conversionStartTime && (Date.now() - conversionStartTime) > 1200000) {
        stopFakeProgress();
        isConverting = false;
        hideLoader();
        hideConvertingPopup(function () {
            showTopPopup('Conversion is taking longer than expected. Please try again.', true);
        });
        return;
    }
    isFetchingStatus = true;

    fetch('/convert/status/' + jobId)
        .then(res => res.json())
        .then(data => {
            isFetchingStatus = false;

            if (data.status === 'in_progress' || data.status === 'queued') {
                setTimeout(() => startPolling(jobId, requestKey), 2000);
            } else if (data.status === 'success') {
                stopFakeProgress();
                updateLoaderPercent(100);
                lastConvertedKey = requestKey;
                isConverting = false;
                hideConvertingPopup(function () {
                    showTopPopup('Model converted successfully.', false);
                });
                setTimeout(function () {
                    showResult(data.signedUrl, jobId, currentTargetFormat, data.converted_name, data.converted_size);
                }, 400);
            } else if (data.status === 'failed') {
                stopFakeProgress();
                isConverting = false;
                hideLoader();
                hideConvertingPopup(function () {
                    showTopPopup('Conversion failed, please try again.', true);
                });
            }
        })
        .catch(() => {
            isFetchingStatus = false;
            setTimeout(() => startPolling(jobId, requestKey), 3000);
        });
}

    function showLoader() {
        loader.style.display = 'flex';
        modelViewer.classList.add('convert-blur');
        updateLoaderPercent(0);
    }

    function hideLoader() {
        loader.style.display = 'none';
        modelViewer.classList.remove('convert-blur');
    }

    function updateLoaderPercent(p) {
        loaderPercent.textContent = p + '%';
    }

    function showResult(url, jobId, format, name, size) {
        hideLoader();
        resultPlaceholder.style.display = 'none';
        resultBox.style.display = 'flex';

        resultViewer.style.display = 'none';
        document.getElementById('convertResultFileCard').style.display = 'flex';

        document.getElementById('convertResultLabelText').textContent = name || ('converted-model.' + format);
        document.getElementById('convertResultLabelSize').textContent = size ? formatBytes(parseInt(size)) : '';

        downloadBtn.dataset.jobId = jobId;
    }
    downloadBtn.addEventListener('click', function () {
        const jobId = this.dataset.jobId;
        if (!jobId) return;
        window.location.href = '/convert/download/' + jobId;
    });

    function showTopPopup(message, isError, isInfo) {
        const container = document.getElementById('convertPopupContainer');
        const popup = document.createElement('div');
        popup.className = isError ? 'credit-popup-error' : 'convert-popup-success';
        if (isInfo) popup.classList.add('credit-popup-info');
        popup.textContent = message;
        container.appendChild(popup);
        setTimeout(() => popup.remove(), 3000);
    }
function showConvertingPopup() {
    const container = document.getElementById('convertPopupContainer');
    const popup = document.createElement('div');
    popup.className = 'convert-popup-converting';
    popup.innerHTML = '<span class="convert-popup-spinner"></span><span>Converting</span>';
    container.appendChild(popup);
    convertingPopupEl = popup;
    return popup;
}

function hideConvertingPopup(callback) {
    if (!convertingPopupEl) {
        if (callback) callback();
        return;
    }
    const el = convertingPopupEl;
    convertingPopupEl = null;
    el.classList.add('popup-exit');
    setTimeout(function () {
        el.remove();
        if (callback) callback();
    }, 300);
}



})();
</script>

@endsection