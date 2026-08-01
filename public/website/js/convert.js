(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

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

    const resultPlaceholder = document.getElementById('convertResultPlaceholder');
    const resultBox = document.getElementById('convertResultBox');
    const resultViewer = document.getElementById('convertResultViewer');
    const downloadBtn = document.getElementById('convertDownloadBtn');

    let uploadedPath = null;
    let uploadedFormat = null;
    let uploadedHash = null;
    let isConverting = false;
    let isFetchingStatus = false;
    let lastConvertedKey = null;

    uploadBtn.addEventListener('click', () => fileInput.click());
        document.getElementById('convertViewerWrap').addEventListener('click', function (e) {
            if (e.target.closest('#convertUploadBtn') || e.target.closest('#convertReuploadBtn')) return;
            fileInput.click();
        });
    reuploadBtn.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        fileError.style.display = 'none';

        const formData = new FormData();
        formData.append('file', file);

        fetch('/convert/upload', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                if (data.invalid_format) {
                    showTopPopup('Please use a 3D modeling or printing format (GLB, STL, OBJ, FBX, etc.)', true);
                }
                fileInput.value = '';
                return;
            }

            uploadedPath = data.path;
            uploadedFormat = data.format;
            uploadedHash = data.hash;

            placeholder.style.display = 'none';
            modelViewer.style.display = 'block';
            modelViewer.src = '/storage/' + uploadedPath;
            uploadBtn.style.display = 'none';
            reuploadBtn.style.display = 'inline-block';
        })
        .catch(() => showTopPopup('Upload failed, please try again.', true));
    });

    formatSelect.addEventListener('change', function () {
        if (this.value) formatError.style.display = 'none';
    });

    convertBtn.addEventListener('click', function () {
        let hasError = false;
        if (!uploadedPath) { fileError.style.display = 'block'; hasError = true; }
        if (!formatSelect.value) { formatError.style.display = 'block'; hasError = true; }
        if (hasError || isConverting) return;

        const targetFormat = formatSelect.value;
        const requestKey = uploadedHash + '|' + targetFormat;

        if (requestKey === lastConvertedKey) {
            showTopPopup('Already converted.', false, true);
            return;
        }

        isConverting = true;
        showLoader();
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
                hideLoader();
                showTopPopup(data.no_credits ? 'Not enough credits.' : 'Conversion could not start.', true);
                return;
            }

            if (data.already_converted) {
                isConverting = false;
                hideLoader();
                showTopPopup('Already converted.', false, true);
                return;
            }

            showTopPopup('10 credits deducted', false);
            startPolling(data.job_id, requestKey);
        })
        .catch(() => {
            isConverting = false;
            hideLoader();
            showTopPopup('Something went wrong.', true);
        });
    });

    let fakeProgressTimer = null;
    let fakeProgressValue = 0;

    function startFakeProgress() {
        fakeProgressValue = 0;
        updateLoaderPercent(0);

        fakeProgressTimer = setInterval(function () {
            if (fakeProgressValue < 90) {
                fakeProgressValue += Math.floor(Math.random() * 4) + 2; // +2 to +5
                if (fakeProgressValue > 90) fakeProgressValue = 90;
                updateLoaderPercent(fakeProgressValue);
            }
        }, 700);
    }

    function stopFakeProgress() {
        if (fakeProgressTimer) {
            clearInterval(fakeProgressTimer);
            fakeProgressTimer = null;
        }
    }

    function startPolling(jobId, requestKey) {
        if (isFetchingStatus) return;
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
                    setTimeout(function () {
                        showResult(data.signedUrl, jobId, currentTargetFormat);
                    }, 400);
                } else if (data.status === 'failed') {
                    stopFakeProgress();
                    isConverting = false;
                    hideLoader();
                    showTopPopup('Conversion failed, please try again.', true);
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

    function showResult(url, jobId) {
        hideLoader();
        resultPlaceholder.style.display = 'none';
        resultBox.style.display = 'block';
        resultViewer.src = url;
        downloadBtn.dataset.jobId = jobId;
    }

    downloadBtn.addEventListener('click', function () {
        const jobId = this.dataset.jobId;
        if (!jobId) return;
        window.location.href = '/convert/download/' + jobId;
    });

    function showTopPopup(message, isError, isInfo) {
        const popup = document.createElement('div');
        popup.className = isError ? 'credit-popup-error' : 'credit-popup';
        if (isInfo) popup.classList.add('credit-popup-info');
        popup.textContent = message;
        document.body.appendChild(popup);
        setTimeout(() => popup.remove(), 3000);
    }
})();