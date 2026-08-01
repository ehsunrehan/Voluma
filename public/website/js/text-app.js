document.addEventListener('DOMContentLoaded', function () {

    const promptInput = document.getElementById('promptInput');
    const generateTextBtn = document.getElementById('generateTextBtn');
    const statusText = document.getElementById('statusText');
    const downloadBtn = document.getElementById('downloadBtn');
    const renewBtn = document.getElementById('renewBtn');
    const viewerPlaceholder = document.getElementById('viewerPlaceholder');
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

    let currentTaskId = "";
    let polling = null;
    let modelLoaded = false;
    let generationFinished = false;
    let isRenewFlow = false;
    let currentPrompt = "";

    if (generateTextBtn) {
        generateTextBtn.addEventListener("click", function () {

            if (generateTextBtn.dataset.lowCredits === "1") {
                showNoCreditsPopup("You don't have enough credits.");
                return;
            }

            const prompt = promptInput.value.trim();

            if (!prompt) {
                showNoCreditsPopup("Please describe your model first.");
                return;
            }

            currentPrompt = prompt;

            modelViewer.style.opacity = "0";
            modelViewer.style.display = "none";
            modelViewer.src = "";
            modelViewer.removeAttribute("src");

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

            modelViewer.style.filter = "blur(8px)";

            fetch('/generate/text-model', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ prompt: prompt })
            })
                .then(res => res.json())
                .then(data => {

                    if (!data.success) {
                        loadingOverlay.style.display = "none";
                        showNoCreditsPopup(data.message || 'Generation failed.');
                        return;
                    }

                    currentTaskId = data.task_id;
                    downloadBtn.disabled = false;
                    renewBtn.disabled = false;
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

            downloadFileName.value = currentPrompt.substring(0, 30) || "model";
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
                body: JSON.stringify({ task_id: currentTaskId, type: selectedType , name: finalName})
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

    if (renewBtn) {
    renewBtn.addEventListener('click', function () {

        if (!modelLoaded || !currentTaskId) {
            showNoCreditsPopup('Model is not ready yet.');
            return;
        }

        if (renewBtn.disabled) return;

            renewBtn.disabled = true;
            isRenewFlow = true;

            const previousTaskId = currentTaskId;
            const previousModelSrc = modelViewer.src;

            modelViewer.style.opacity = "0";
            modelViewer.style.display = "none";
            modelViewer.src = "";
            modelViewer.removeAttribute("src");

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

            modelViewer.style.filter = "blur(8px)";

            fetch('/renew/model', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ task_id: previousTaskId })
            })
                .then(res => res.json())
                .then(data => {

                    renewBtn.disabled = false;

                    if (!data.success) {
                        isRenewFlow = false;
                        generationFinished = true;
                        currentTaskId = previousTaskId;

                        loadingOverlay.style.display = "none";
                        loadingText.style.display = "none";
                        particleLoader.style.display = "none";

                        modelViewer.src = previousModelSrc;
                        modelViewer.style.display = "block";
                        modelViewer.style.filter = "blur(0px)";
                        modelViewer.style.opacity = "1";

                        statusText.textContent = "✅ 3D Model Ready!";
                        statusText.className = "success";
                        modelLoaded = true;

                        showNoCreditsPopup(data.message || 'Renew failed.');
                        return;
                    }

                    currentTaskId = data.task_id;
                    startPolling();
                })
                .catch(function () {
                    renewBtn.disabled = false;
                    isRenewFlow = false;
                    generationFinished = true;
                    currentTaskId = previousTaskId;

                    loadingOverlay.style.display = "none";
                    particleLoader.style.display = "none";

                    modelViewer.src = previousModelSrc;
                    modelViewer.style.display = "block";
                    modelViewer.style.filter = "blur(0px)";
                    modelViewer.style.opacity = "1";

                    statusText.textContent = "✅ 3D Model Ready!";
                    statusText.className = "success";
                    modelLoaded = true;

                    showNoCreditsPopup('Something went wrong while renewing.');
                });

        });
    }

    function startPolling() {

        let isFetching = false;

        polling = setInterval(() => {

            if (isFetching) return;
            isFetching = true;

            fetch('/generate/status/' + currentTaskId)
                .then(res => res.json())
                .then(data => {

                    isFetching = false;

                    if (generationFinished) return;

                    

                    loadingPercent.innerHTML = data.data.progress + "%";
                    if (data.data.progress < 20) statusText.innerHTML = "Uploading your Input...";
                    else if (data.data.progress < 50) statusText.innerHTML = "Analyzing Prompt...";
                    else if (data.data.progress < 80) statusText.innerHTML = "Generating Geometry...";
                    else if (data.data.progress < 100) statusText.innerHTML = "Applying Textures...";
                    else statusText.innerHTML = "Preparing Viewer...";

                    progressFill.style.width = data.data.progress + "%";

                    if (data.data.status === "success" && !generationFinished) {

                        generationFinished = true;
                        clearInterval(polling);

                        loadingText.innerHTML = "Preparing Viewer...";
                        particleLoader.style.display = "flex";
                        modelViewer.style.display = "block";
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
                                    if (!data.success) {
                                        showNoCreditsPopup(data.message);
                                        return;
                                    }
                                    if (creditCount) creditCount.textContent = data.credits;
                                    if (navbarCreditCount) navbarCreditCount.textContent = data.credits;
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
                .catch(() => { isFetching = false; });

        }, 1000);
    }

function showCreditAnimation() {
    const popup = document.createElement("div");
    popup.className = "credit-popup";
    popup.innerHTML = "-10 Credits";
    document.body.appendChild(popup);
    requestAnimationFrame(() => {
        requestAnimationFrame(() => popup.classList.add("show"));
    });
    setTimeout(() => popup.classList.remove("show"), 1700);
    setTimeout(() => popup.remove(), 2300);
}

    function showNoCreditsPopup(message) {
        const popup = document.createElement("div");
        popup.className = "credit-popup credit-popup-error";
        popup.innerHTML = message || "Something went wrong.";
        document.body.appendChild(popup);
        setTimeout(() => popup.classList.add("show"), 20);
        setTimeout(() => popup.classList.remove("show"), 1700);
        setTimeout(() => popup.remove(), 2300);
    }

});