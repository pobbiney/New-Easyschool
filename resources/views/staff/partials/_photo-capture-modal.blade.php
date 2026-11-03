<div class="modal fade" id="staffCameraModal" tabindex="-1" aria-labelledby="staffCameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="staffCameraModalLabel">Capture staff photo</h6>
                <button type="button" class="btn-close" id="staffCameraClose" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="staff-camera-stage">
                    <video id="staffCameraVideo" autoplay playsinline muted></video>
                    <canvas id="staffCameraCanvas" class="d-none"></canvas>
                    <img id="staffCameraShot" class="d-none" alt="Captured photo">
                    <p class="staff-camera-status mb-0" id="staffCameraStatus">Allow camera access to take a photo.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-neutral-400" id="staffCameraCancel">Cancel</button>
                <button type="button" class="btn btn-outline-primary-600 d-none" id="staffCameraRetake">Retake</button>
                <button type="button" class="btn btn-primary-600" id="staffCameraSnap">
                    <i class="ri-camera-line"></i> Capture
                </button>
                <button type="button" class="btn btn-primary-600 d-none" id="staffCameraUse">Use this photo</button>
            </div>
        </div>
    </div>
</div>
