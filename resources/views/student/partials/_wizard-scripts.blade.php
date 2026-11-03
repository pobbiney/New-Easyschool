<script>
    let currentStep = 1;
    const totalSteps = 4;
    let docCounter = {{ $docCounterStart ?? 0 }};

    function showStep(step) {
        currentStep = step;

        $('.wizard-step-panel').removeClass('active');
        $('.wizard-step-panel[data-step="' + step + '"]').addClass('active');

        $('.wizard-step-item').each(function () {
            let itemStep = $(this).data('step');
            $(this).removeClass('active completed');

            if (itemStep < step) {
                $(this).addClass('completed');
                $(this).find('.wizard-step-circle').html('<i class="ri-check-line"></i>');
            } else if (itemStep === step) {
                $(this).addClass('active');
            }
        });

        restoreStepIcons(step);

        $('#wizardPrevBtn').toggle(step > 1);
        $('#wizardSaveContinueBtn').toggle(step < totalSteps);
        $('#wizardSubmitBtn').toggle(step === totalSteps);
        $('#currentStepInput').val(step);

        $('html, body').animate({ scrollTop: $('#studentWizardForm').offset().top - 80 }, 200);
    }

    function restoreStepIcons(current) {
        const icons = ['ri-user-3-line', 'ri-parent-line', 'ri-heart-pulse-line', 'ri-file-upload-line'];
        $('.wizard-step-item').each(function () {
            let itemStep = $(this).data('step');
            if (itemStep >= current) {
                $(this).find('.wizard-step-circle').html('<i class="' + icons[itemStep - 1] + '"></i>');
            }
        });
    }

    function validateStep(step) {
        let isValid = true;
        let stepPanel = $('.wizard-step-panel[data-step="' + step + '"]');

        if (step === 1) {
            stepPanel.find('.wizard-required').each(function () {
                $(this).removeClass('border-danger-600');

                if ($(this).is('select')) {
                    if (!$(this).val()) {
                        $(this).addClass('border-danger-600');
                        isValid = false;
                    }
                } else if ($(this).val().trim() === '') {
                    $(this).addClass('border-danger-600');
                    isValid = false;
                }
            });

            $('#step1Error').toggle(!isValid);
            return isValid;
        }

        if (step === 2) {
            stepPanel.find('.wizard-step2-required').each(function () {
                $(this).removeClass('border-danger-600');

                if ($(this).is('select')) {
                    if (!$(this).val()) {
                        $(this).addClass('border-danger-600');
                        isValid = false;
                    }
                } else if ($(this).val().trim() === '') {
                    $(this).addClass('border-danger-600');
                    isValid = false;
                }
            });

            $('#step2Error').toggle(!isValid);
            return isValid;
        }

        return true;
    }

    function saveAndContinue() {
        if (!validateStep(currentStep)) {
            return;
        }

        if (currentStep >= totalSteps) {
            return;
        }

        let form = document.getElementById('studentWizardForm');
        let formData = new FormData(form);
        formData.set('current_step', currentStep);

        $('#wizardSaveContinueBtn').prop('disabled', true).html('<i class="ri-loader-4-line"></i> Saving...');

        fetch('{{ $draftSaveUrl }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) {
                    throw data;
                }
                return data;
            });
        })
        .then(function (data) {
            if (data.student_record_id) {
                $('#studentRecordId').val(data.student_record_id);
            }

            if (typeof showAppToast === 'function') {
                showAppToast('success', data.message || 'Progress saved. You can continue later.');
            }

            showStep(currentStep + 1);
        })
        .catch(function (error) {
            let message = 'Could not save progress. Please try again.';

            if (error.message) {
                message = error.message;
            }

            if (error.errors) {
                message = Object.values(error.errors).flat().join(' ');
            }

            if (typeof showAppToast === 'function') {
                showAppToast('error', message);
            } else {
                alert(message);
            }
        })
        .finally(function () {
            $('#wizardSaveContinueBtn').prop('disabled', false).html('<i class="ri-save-line"></i> Save & Continue Later');
        });
    }

    $('#wizardSaveContinueBtn').click(function () {
        saveAndContinue();
    });

    $('#wizardPrevBtn').click(function () {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    });

    $('.wizard-step-item').click(function () {
        let targetStep = $(this).data('step');

        if (targetStep < currentStep) {
            showStep(targetStep);
            return;
        }

        if (targetStep > currentStep) {
            return;
        }
    });

    $('#studentPicture').change(function () {
        let file = this.files[0];
        $('#studentPictureData').val('');
        if (file) {
            let reader = new FileReader();
            reader.onload = function (e) {
                setStudentPhotoPreview(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    let originalStudentPhoto = $('#studentPicturePreview img').attr('src') || '';

    function setStudentPhotoPreview(src) {
        const $frame = $('#studentPicturePreview');
        $frame.addClass('has-photo').html('<img src="' + src + '" alt="Student photo preview">');
        $('#clearStudentPhoto').toggleClass('d-none', src === originalStudentPhoto || !src);
    }

    function clearStudentPhotoPreview() {
        const input = document.getElementById('studentPicture');
        if (input) {
            input.value = '';
        }
        $('#studentPictureData').val('');
        if (originalStudentPhoto) {
            setStudentPhotoPreview(originalStudentPhoto);
            $('#clearStudentPhoto').addClass('d-none');
            return;
        }
        $('#studentPicturePreview').removeClass('has-photo').html(
            '<div class="student-photo-placeholder"><i class="ri-camera-lens-line"></i><span>No photo yet</span></div>'
        );
        $('#clearStudentPhoto').addClass('d-none');
    }

    $('#clearStudentPhoto').on('click', clearStudentPhotoPreview);

    (function initStudentCamera() {
        const modalEl = document.getElementById('studentCameraModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        const video = document.getElementById('studentCameraVideo');
        const canvas = document.getElementById('studentCameraCanvas');
        const shot = document.getElementById('studentCameraShot');
        const statusEl = document.getElementById('studentCameraStatus');
        const snapBtn = document.getElementById('studentCameraSnap');
        const retakeBtn = document.getElementById('studentCameraRetake');
        const useBtn = document.getElementById('studentCameraUse');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
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

        function showLive() {
            video.classList.remove('d-none');
            shot.classList.add('d-none');
            snapBtn.classList.remove('d-none');
            retakeBtn.classList.add('d-none');
            useBtn.classList.add('d-none');
            capturedBlob = null;
        }

        function startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                setStatus('Camera is not supported in this browser. Upload a file instead.');
                snapBtn.disabled = true;
                return;
            }

            snapBtn.disabled = true;
            setStatus('Starting camera…');
            showLive();

            navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 720 } },
                audio: false,
            }).then(function (mediaStream) {
                stream = mediaStream;
                video.srcObject = mediaStream;
                return video.play();
            }).then(function () {
                snapBtn.disabled = false;
                setStatus('Centre the student, then capture.');
            }).catch(function (error) {
                snapBtn.disabled = true;
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

        document.getElementById('openStudentCamera')?.addEventListener('click', function () {
            modal.show();
        });

        modalEl.addEventListener('shown.bs.modal', startCamera);
        modalEl.addEventListener('hidden.bs.modal', function () {
            stopStream();
            capturedBlob = null;
            showLive();
            setStatus('Allow camera access to take a photo.');
        });

        snapBtn.addEventListener('click', function () {
            if (!video.videoWidth) {
                setStatus('Camera is not ready yet.');
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

        retakeBtn.addEventListener('click', function () {
            if (shot.src) {
                URL.revokeObjectURL(shot.src);
            }
            capturedBlob = null;
            showLive();
            setStatus('Centre the student, then capture.');
        });

        useBtn.addEventListener('click', function () {
            if (!capturedBlob) {
                return;
            }

            const file = new File([capturedBlob], 'student-capture.jpg', { type: 'image/jpeg' });
            const input = document.getElementById('studentPicture');

            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                $('#studentPictureData').val('');
            } catch (err) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#studentPictureData').val(e.target.result);
                };
                reader.readAsDataURL(capturedBlob);
            }

            setStudentPhotoPreview(URL.createObjectURL(capturedBlob));
            modal.hide();
        });
    })();

    if ($('#studentPicturePreview img').length) {
        $('#studentPicturePreview').addClass('has-photo');
        $('#clearStudentPhoto').removeClass('d-none');
    }

    $('#addDocument').click(function () {
        let docName = $('#docName').val();
        let fileInput = $('#documentFile')[0];
        let file = fileInput.files[0];

        if (docName === '') {
            showAppToast('error', 'Enter document name');
            return;
        }

        if (!file) {
            showAppToast('error', 'Choose a file to upload');
            return;
        }

        let row = `
            <tr id="doc_row_${docCounter}">
                <td>${docName}</td>
                <td>${file.name}</td>
                <td><button type="button" class="btn btn-danger btn-sm removeDoc" data-id="${docCounter}">Remove</button></td>
            </tr>
        `;

        $('#documentTable tbody').append(row);

        $('#documentContainer').append(`
            <div id="doc_hidden_${docCounter}" style="display:none">
                <input type="hidden" name="documents[${docCounter}][doc_name]" value="${docName}">
                <input type="file" name="documents[${docCounter}][document]" class="realFileInput">
            </div>
        `);

        let realInput = $('#doc_hidden_' + docCounter + ' .realFileInput')[0];
        const dt = new DataTransfer();
        dt.items.add(file);
        realInput.files = dt.files;

        $('#docName').val('');
        $('#documentFile').val('');
        docCounter++;
    });

    $(document).on('click', '.removeDoc', function () {
        let id = $(this).data('id');
        $('#doc_row_' + id).remove();
        $('#doc_hidden_' + id).remove();
    });

    $('#studentWizardForm').on('submit', function (event) {
        for (let step = 1; step <= 2; step++) {
            if (!validateStep(step)) {
                event.preventDefault();
                showStep(step);

                if (typeof showAppToast === 'function') {
                    showAppToast('error', 'Please complete all required fields before submitting.');
                }

                return false;
            }
        }
    });

    showStep(1);
</script>
