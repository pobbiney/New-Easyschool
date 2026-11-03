<script>
    (function () {
        const imageInput = document.getElementById('imageUpload');
        const photoPreview = document.getElementById('photoPreview');
        const photoPlaceholder = document.getElementById('photoPlaceholder');
        const photoFrame = document.getElementById('staffPhotoFrame');
        const imageData = document.getElementById('staffImageData');
        const clearBtn = document.getElementById('clearStaffPhoto');
        const originalPhoto = photoPreview && photoPreview.src && !photoPreview.classList.contains('d-none')
            ? photoPreview.src
            : '';

        function showPreview(src) {
            if (!photoPreview) return;
            photoPreview.src = src;
            photoPreview.classList.remove('d-none');
            if (photoPlaceholder) {
                photoPlaceholder.classList.add('d-none');
            }
            if (photoFrame) {
                photoFrame.classList.add('has-photo');
            }
            if (clearBtn) {
                clearBtn.classList.toggle('d-none', !src || src === originalPhoto);
            }
        }

        function resetPreview() {
            if (imageInput) {
                imageInput.value = '';
            }
            if (imageData) {
                imageData.value = '';
            }
            if (originalPhoto) {
                showPreview(originalPhoto);
                if (clearBtn) {
                    clearBtn.classList.add('d-none');
                }
                return;
            }
            if (photoPreview) {
                photoPreview.src = '';
                photoPreview.classList.add('d-none');
            }
            if (photoPlaceholder) {
                photoPlaceholder.classList.remove('d-none');
            }
            if (photoFrame) {
                photoFrame.classList.remove('has-photo');
            }
            if (clearBtn) {
                clearBtn.classList.add('d-none');
            }
        }

        if (imageInput) {
            imageInput.addEventListener('change', function () {
                const file = this.files[0];
                if (imageData) {
                    imageData.value = '';
                }
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function (event) {
                    showPreview(event.target.result);
                };
                reader.readAsDataURL(file);
            });
        }

        clearBtn?.addEventListener('click', resetPreview);

        const modalEl = document.getElementById('staffCameraModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        document.body.appendChild(modalEl);

        const video = document.getElementById('staffCameraVideo');
        const canvas = document.getElementById('staffCameraCanvas');
        const shot = document.getElementById('staffCameraShot');
        const statusEl = document.getElementById('staffCameraStatus');
        const snapBtn = document.getElementById('staffCameraSnap');
        const retakeBtn = document.getElementById('staffCameraRetake');
        const useBtn = document.getElementById('staffCameraUse');
        const cancelBtn = document.getElementById('staffCameraCancel');
        const closeBtn = document.getElementById('staffCameraClose');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: true, keyboard: true });
        let stream = null;
        let capturedBlob = null;

        function setStatus(text) {
            if (statusEl) {
                statusEl.textContent = text;
            }
        }

        function stopStream() {
            if (stream) {
                stream.getTracks().forEach(function (track) {
                    track.stop();
                });
                stream = null;
            }
            if (video) {
                video.srcObject = null;
            }
        }

        function closeCamera() {
            stopStream();
            modal.hide();
        }

        function showLive() {
            video.classList.remove('d-none');
            shot.classList.add('d-none');
            snapBtn.classList.remove('d-none');
            snapBtn.disabled = false;
            retakeBtn.classList.add('d-none');
            useBtn.classList.add('d-none');
            capturedBlob = null;
        }

        function startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                setStatus('Camera is not supported in this browser. Upload a file instead.');
                return;
            }

            setStatus('Starting camera…');
            showLive();

            navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 720 } },
                audio: false,
            }).then(function (mediaStream) {
                stream = mediaStream;
                video.srcObject = mediaStream;
                video.onloadedmetadata = function () {
                    snapBtn.disabled = false;
                    setStatus('Centre the staff member, then capture.');
                };
                return video.play();
            }).then(function () {
                snapBtn.disabled = false;
                setStatus('Centre the staff member, then capture.');
            }).catch(function (error) {
                let message = 'Could not access the camera. Check permission and try again.';
                if (error && (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError')) {
                    message = 'Camera permission was denied. Allow access or upload a file.';
                } else if (error && error.name === 'NotFoundError') {
                    message = 'No camera was found on this device.';
                }
                setStatus(message);
                if (typeof showAppToast === 'function') {
                    showAppToast('error', message);
                }
            });
        }

        document.getElementById('openStaffCamera')?.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            modal.show();
        });

        function onCancel(event) {
            event.preventDefault();
            event.stopPropagation();
            closeCamera();
        }

        cancelBtn?.addEventListener('click', onCancel);
        closeBtn?.addEventListener('click', onCancel);

        modalEl.addEventListener('shown.bs.modal', startCamera);
        modalEl.addEventListener('hidden.bs.modal', function () {
            stopStream();
            capturedBlob = null;
            showLive();
            setStatus('Allow camera access to take a photo.');
        });

        snapBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (!video.videoWidth) {
                setStatus('Camera is not ready yet. Wait a moment, then try again.');
                return;
            }

            const size = Math.min(video.videoWidth, video.videoHeight, 720);
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext('2d');
            const sx = (video.videoWidth - size) / 2;
            const sy = (video.videoHeight - size) / 2;
            ctx.translate(size, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, sx, sy, size, size, 0, 0, size, size);

            canvas.toBlob(function (blob) {
                if (!blob) {
                    setStatus('Could not capture that frame. Try again.');
                    return;
                }
                capturedBlob = blob;
                shot.src = URL.createObjectURL(blob);
                shot.classList.remove('d-none');
                video.classList.add('d-none');
                snapBtn.classList.add('d-none');
                retakeBtn.classList.remove('d-none');
                useBtn.classList.remove('d-none');
                setStatus('Happy with this photo? Use it, or retake.');
            }, 'image/jpeg', 0.88);
        });

        retakeBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (shot.src) {
                URL.revokeObjectURL(shot.src);
            }
            capturedBlob = null;
            showLive();
            setStatus('Centre the staff member, then capture.');
        });

        useBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (!capturedBlob) {
                return;
            }

            const file = new File([capturedBlob], 'staff-capture.jpg', { type: 'image/jpeg' });

            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                imageInput.files = dt.files;
                if (imageData) {
                    imageData.value = '';
                }
            } catch (err) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (imageData) {
                        imageData.value = e.target.result;
                    }
                };
                reader.readAsDataURL(capturedBlob);
            }

            showPreview(URL.createObjectURL(capturedBlob));
            closeCamera();
        });
    })();
</script>
