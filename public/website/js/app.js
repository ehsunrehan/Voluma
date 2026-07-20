    document.addEventListener('DOMContentLoaded', function() {
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

        let uploadedImage = "";
        let currentTaskId = "";
        let polling = null;

        if (uploadArea && fileInput) {
            uploadArea.addEventListener('click', function(e) {
                if (e.target.tagName !== 'BUTTON') fileInput.click();
            });

            if (browseBtn) {
                browseBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    fileInput.click();
                });
            }

            fileInput.addEventListener('change', function() {
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


                            // Left Upload Card
                            uploadState.style.display = "none";
                            previewState.style.display = "flex";

                            uploadedPreview.src = URL.createObjectURL(file);

                            imageName.textContent = file.name;

                            imageSize.textContent =
                                (file.size / 1024 / 1024).toFixed(2) + " MB";

                            downloadBtn.disabled = false;
                            renewBtn.disabled = false;

                            console.log(data.image_path);
                        }
                        else {
                        statusText.textContent = '❌ ' + (data.message || 'Generation failed.');
                        statusText.className = 'error';
                    }
                })
                .catch(err => {
                    statusText.textContent = '❌ Error: ' + err.message;
                    statusText.className = 'error';
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

            statusText.textContent = "Creating 3D model...";
            statusText.className = "loading";

            previewImage.style.filter = "blur(8px)";
            modelViewer.style.filter = "blur(8px)";

            loadingOverlay.style.display = "flex";
            loadingPercent.innerHTML = "0%";

            fetch('/generate/model',{

                method:'POST',
                
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
                },

                body:JSON.stringify({

                    image_path:uploadedImage

                })

            })

            .then(res=>res.json())

            .then(data=>{

                console.log(data);

                currentTaskId = data.task_id;

                startPolling();

            });

        });


    }


        if (downloadBtn) {
            downloadBtn.addEventListener('click', () => alert('Download coming soon.'));
        }

        if (renewBtn) {
            renewBtn.addEventListener('click', () => alert('Renew coming soon.'));
        }


        function startPolling(){

            polling = setInterval(()=>{

                fetch('/generate/status/'+currentTaskId)
                

                .then(res=>res.json())

                .then(data=>{

                    console.log(data);

                    loadingPercent.innerHTML = data.data.progress + "%";
                    if(data.data.progress < 20){

    statusText.innerHTML="Uploading AI Input...";

    }

    else if(data.data.progress < 50){

    statusText.innerHTML="Analyzing Image...";

    }

    else if(data.data.progress < 80){

    statusText.innerHTML="Generating Geometry...";

    }

    else if(data.data.progress < 100){

    statusText.innerHTML="Applying Textures...";

    }

    else{

    statusText.innerHTML="Preparing Viewer...";

    }
                    progressFill.style.width = data.data.progress + "%";

    if (data.data.status === "success") {

        clearInterval(polling);
            
            console.log(data.data.output.pbr_model);

            modelViewer.src="/stream-model/"+currentTaskId;

            

            modelViewer.style.display = "block";

            previewImage.style.display = "none";

            loadingOverlay.style.display = "none";

            particleLoader.style.display = "flex";

            setTimeout(() => {

    particleLoader.style.display = "none";

    loadingOverlay.style.display = "none";

    modelViewer.style.transition = ".8s";

    modelViewer.style.filter = "blur(0px)";

    previewImage.style.filter = "blur(0px)";

    viewerPlaceholder.style.display = "none";

    previewImage.style.display = "none";

    modelViewer.style.opacity = "1";

    statusText.textContent = "✅ 3D Model Ready!";

    statusText.className = "success";

}, 800);
        

    }



                    if(data.data.status === "failed"){

                        clearInterval(polling);

                        statusText.textContent = "❌ Generation Failed";
                        statusText.className = "error";
                        loadingOverlay.style.display = "none";

                        // previewImage.style.filter = "blur(0)";

                        // modelViewer.style.filter = "blur(0)";

                    }

                });

            },1000);

        }




    });