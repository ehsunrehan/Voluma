document.addEventListener('DOMContentLoaded', function () {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const browseBtn = document.getElementById('browseBtn');
    const statusText = document.getElementById('statusText');
    const downloadBtn = document.getElementById('downloadBtn');
    const renewBtn = document.getElementById('renewBtn');
    const previewImage = document.getElementById('previewImage');
    const viewerPlaceholder = document.getElementById('viewerPlaceholder');
    const uploadState = document.getElementById('uploadState');
    const previewState = document.getElementById('previewState');
    const uploadedPreview = document.getElementById('uploadedPreview');
    const imageName = document.getElementById('imageName');
    const imageSize = document.getElementById('imageSize');
    const replaceBtn = document.getElementById('replaceBtn');
    const generateBtn = document.getElementById('generateBtn');
    const modelViewer = document.getElementById("modelViewer");
    const loadingOverlay = document.getElementById("loadingOverlay");
    const loadingPercent = document.getElementById("loadingPercent");
    const progressFill = document.getElementById("progressFill");
    const particleLoader = document.getElementById("particleLoader");
    const loadingText = document.getElementById("loadingText");
    const creditCount = document.getElementById("creditCount");
    const navbarCreditCount = document.getElementById("navbarCreditCount");
    const downloadModalOverlay = document.getElementById("downloadModalOverlay");
    const downloadFileName = document.getElementById("downloadFileName");
    const downloadFileType = document.getElementById("downloadFileType");
    const downloadTypeError = document.getElementById("downloadTypeError");
    const confirmDownloadBtn = document.getElementById("confirmDownloadBtn");
    const closeDownloadModal = document.getElementById("closeDownloadModal");



    let uploadedImage = "";
    let currentTaskId = "";
    let polling = null;
    let modelLoaded = false;
    let generationFinished = false;
    let isRenewFlow = false;

    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', function (e) {
            if (e.target.tagName !== 'BUTTON') fileInput.click();
        });

        if (browseBtn) {
            browseBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                fileInput.click();
            });
        }

        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                alert('Please upload an image file.');
                this.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('File too large. Max 5MB.');
                this.value = '';
                return;
            }

            statusText.textContent = 'Uploading...';
            statusText.className = 'loading';

            const formData = new FormData();
            formData.append('image', file);
            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            fetch('/generate', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
    },
    body: formData
})
    .then(response => response.json())
    .then(data => {
        console.log(data);
        if (data.success) {
            uploadedImage = data.image_path;
            statusText.textContent = '✅ Image uploaded Successfully!';
            statusText.className = 'success';

            uploadState.style.display = "none";
            previewState.style.display = "flex";

            uploadedPreview.src = URL.createObjectURL(file);
            imageName.textContent = file.name;
            imageSize.textContent = (file.size / 1024 / 1024).toFixed(2) + " MB";

            downloadBtn.disabled = false;
            renewBtn.disabled = false;

            console.log(data.image_path);

            if (generateBtn && generateBtn.dataset.lowCredits === "1") {
            showNoCreditsPopup("You don't have enough credits.");
            }
        }
        else {
            showNoCreditsPopup(data.message || 'Generation failed.');
        }
    })
    .catch(err => {
        showNoCreditsPopup(err.message || 'Something went wrong.');
        console.error(err);
    });


        });
    }
    if (replaceBtn) {
        replaceBtn.addEventListener("click", function () {
            fileInput.click();
        });
    }
    if (generateBtn) {

        generateBtn.addEventListener("click", function () {

            if (generateBtn.dataset.lowCredits === "1") {
                showNoCreditsPopup("You don't have enough credits.");
                return;
            }


            // Reset previous model
            modelViewer.style.opacity = "0";
            modelViewer.style.display = "none";
            modelViewer.removeAttribute("src");
            // modelViewer.load();

            previewImage.style.display = "none";
            viewerPlaceholder.style.display = "none";

            particleLoader.style.display = "none";
            loadingOverlay.style.display = "flex";
            loadingText.style.display = "block";

            loadingPercent.innerHTML = "0%";
            progressFill.style.width = "0%";



            generationFinished = false;
            modelLoaded = false;
            statusText.textContent = "Creating 3D model...";
            statusText.className = "loading";

            previewImage.style.filter = "blur(8px)";
            modelViewer.style.filter = "blur(8px)";

            loadingOverlay.style.display = "flex";
            loadingPercent.innerHTML = "0%";

            fetch('/generate/model', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },

                body: JSON.stringify({

                    image_path: uploadedImage

                })

            })

                .then(res => res.json())

                .then(data => {

                    console.log(data);

                    currentTaskId = data.task_id;

                    startPolling();

                });

        });


    }


if (downloadBtn) {
        downloadBtn.addEventListener('click', function () {

            if (!modelLoaded || !currentTaskId) {
                showNoCreditsPopup('Model is not ready yet.');
                return;
            }

            const baseName = (imageName.textContent || 'model').replace(/\.[^/.]+$/, "");

            downloadFileName.value = baseName;
            downloadFileType.value = "";
            downloadTypeError.style.display = "none";

            downloadModalOverlay.style.display = "flex";

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
                    task_id: currentTaskId,
                    type: selectedType
                })
            })
                .then(res => res.json())
                .then(data => {

                    confirmDownloadBtn.disabled = false;
                    confirmDownloadBtn.textContent = "Download";

                    if (!data.success) {
                        downloadTypeError.textContent = data.message || 'Download failed. Please try another format.';
                        downloadTypeError.style.display = "block";
                        return;
                    }

                    fetch(data.download_url)
                        .then(res => res.blob())
                        .then(blob => {
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = finalName + '.' + selectedType;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            URL.revokeObjectURL(url);

                            downloadModalOverlay.style.display = "none";
                        });

                })
                .catch(function () {
                    confirmDownloadBtn.disabled = false;
                    confirmDownloadBtn.textContent = "Download";
                    downloadTypeError.textContent = 'Something went wrong. Please try again.';
                    downloadTypeError.style.display = "block";
                });

        });
    }


    
    if (renewBtn) {
        renewBtn.addEventListener('click', function () {

            if (!currentTaskId) {
                showNoCreditsPopup('No previous generation found to renew.');
                return;
            }

            if (renewBtn.disabled) {
                return;   // already ek request chal rahi hai, dobara mat chalao
            }

            renewBtn.disabled = true;
            isRenewFlow = true;

            modelViewer.style.opacity = "0";
            modelViewer.style.display = "none";
            modelViewer.removeAttribute("src");

            previewImage.style.display = "none";
            viewerPlaceholder.style.display = "none";

            particleLoader.style.display = "none";
            loadingOverlay.style.display = "flex";
            loadingText.style.display = "block";

            loadingPercent.innerHTML = "0%";
            progressFill.style.width = "0%";

            generationFinished = false;
            modelLoaded = false;
            statusText.textContent = "Renewing 3D model...";
            statusText.className = "loading";

            previewImage.style.filter = "blur(8px)";
            modelViewer.style.filter = "blur(8px)";

            fetch('/renew/model', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    task_id: currentTaskId
                })
            })
                .then(res => res.json())
                .then(data => {
                    console.log(data);

                    renewBtn.disabled = false;

                    if (!data.success) {
                        isRenewFlow = false;
                        loadingOverlay.style.display = "none";
                        showNoCreditsPopup(data.message || 'Renew failed.');
                        return;
                    }

                    currentTaskId = data.task_id;
                    startPolling();
                });

        });
    }

    


    function startPolling() {

        let isFetching = false;

        polling = setInterval(() => {

            if (isFetching) {
                return;
            }

            isFetching = true;

            fetch('/generate/status/' + currentTaskId)

                .then(res => res.json())

                .then(data => {

                    isFetching = false;

                    if (generationFinished) {
                        return;
                    }

                    console.log(data);

                    loadingPercent.innerHTML = data.data.progress + "%";
                    if (data.data.progress < 20) {

                        statusText.innerHTML = "Uploading AI Input...";

                    }

                    else if (data.data.progress < 50) {

                        statusText.innerHTML = "Analyzing Image...";

                    }

                    else if (data.data.progress < 80) {

                        statusText.innerHTML = "Generating Geometry...";

                    }

                    else if (data.data.progress < 100) {

                        statusText.innerHTML = "Applying Textures...";

                    }

                    else {

                        statusText.innerHTML = "Preparing Viewer...";

                    }
                    progressFill.style.width = data.data. progress + "%";

                    if (data.data.status === "success" && !generationFinished) {

                        generationFinished = true;

                        clearInterval(polling);

                        loadingText.innerHTML = "Preparing Viewer...";

                        particleLoader.style.display = "flex";

                        modelViewer.style.display = "block";

                        previewImage.style.display = "none";

                        loadingOverlay.style.display = "none";

                        loadingText.style.display = "none";

                       modelViewer.src = "/stream-model/" + currentTaskId;

                        if (isRenewFlow) {
                            isRenewFlow = false;
                        } else {
                            fetch('/credits/deduct', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            })
                                .then(res => res.json())
                                .then(data => {
                                    console.log(data);

                                    if (!data.success) {
                                        showNoCreditsPopup(data.message);
                                        return;
                                    }

                                    creditCount.textContent = data.credits;
                                    if (navbarCreditCount) {
                                        navbarCreditCount.textContent = data.credits;
                                    }

                                    showCreditAnimation();
                                });
                        }

                        modelViewer.addEventListener("load", function () {

                            modelLoaded = true;

                            loadingOverlay.style.display = "none";
                            loadingText.style.display = "none";
                            particleLoader.style.display = "none";

                            progressFill.style.width = "0%";
                            loadingPercent.innerHTML = "0%";

                            modelViewer.style.transition = ".8s";
                            modelViewer.style.filter = "blur(0px)";
                            modelViewer.style.opacity = "1";

                            previewImage.style.filter = "blur(0px)";
                            previewImage.style.display = "none";

                            viewerPlaceholder.style.display = "none";

                            statusText.textContent = "✅ 3D Model Ready!";
                            statusText.className = "success";

                        }, { once: true });
                    }




                    if (data.data.status === "failed") {

                        clearInterval(polling);

                        statusText.textContent = "❌ Generation Failed";
                        statusText.className = "error";
                        loadingOverlay.style.display = "none";
                    }

                })
                .catch(() => {
                    isFetching = false;
                });

        }, 1000);

    }

    function showCreditAnimation() {

        const popup = document.createElement("div");

        popup.className = "credit-popup";

        popup.innerHTML = "-10 Credits";

        document.body.appendChild(popup);

        setTimeout(() => {

            popup.classList.add("show");

        }, 20);

        setTimeout(() => {

            popup.classList.remove("show");

        }, 1700);

        setTimeout(() => {

            popup.remove();

        }, 2300);

    }


    function showNoCreditsPopup(message) {

    const popup = document.createElement("div");

    popup.className = "credit-popup credit-popup-error";

    popup.innerHTML = message || "Not enough credits.";

    document.body.appendChild(popup);

    setTimeout(() => {
        popup.classList.add("show");
    }, 20);

    setTimeout(() => {
        popup.classList.remove("show");
    }, 1700);

    setTimeout(() => {
        popup.remove();
    }, 2300);

}


    function animateCredits(newCredits) {

        const start = parseInt(creditCount.textContent);

        const end = parseInt(newCredits);

        let current = start;

        const timer = setInterval(() => {

            if (current === end) {

                clearInterval(timer);

                return;

            }

            current--;

            creditCount.textContent = current;

            creditCount.style.animation = "creditPulse .3s";

            setTimeout(() => {

                creditCount.style.animation = "";

            }, 300);

        }, 40);


    }








});