document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const browseBtn = document.getElementById('browseBtn');
    const statusText = document.getElementById('statusText');
    const downloadBtn = document.getElementById('downloadBtn');
    const renewBtn = document.getElementById('renewBtn');

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

            fetch('/generate/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusText.textContent = '✅ Model generated!';
                    statusText.className = 'success';
                    downloadBtn.disabled = false;
                    renewBtn.disabled = false;
                    console.log('Model URL:', data.model_url);
                } else {
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

    if (downloadBtn) {
        downloadBtn.addEventListener('click', () => alert('Download coming soon.'));
    }

    if (renewBtn) {
        renewBtn.addEventListener('click', () => alert('Renew coming soon.'));
    }
});