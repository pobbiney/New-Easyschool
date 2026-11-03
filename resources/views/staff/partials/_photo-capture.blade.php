@php
    $existingPhoto = $existingPhoto ?? null;
    $hasPhoto = !empty($existingPhoto);
@endphp
<div class="staff-photo-card">
    <div class="staff-photo-box{{ $hasPhoto ? ' has-photo' : '' }}" id="staffPhotoFrame">
        @if($hasPhoto)
            <img id="photoPreview" src="{{ asset($existingPhoto) }}" alt="Staff photo preview">
            <div class="staff-photo-placeholder d-none" id="photoPlaceholder">
                <i class="ri-camera-lens-line"></i>
                <span class="text-sm fw-medium">No photo yet</span>
                <span class="text-xs text-secondary-light">Take a live photo or upload a file</span>
            </div>
        @else
            <div class="staff-photo-placeholder" id="photoPlaceholder">
                <i class="ri-camera-lens-line"></i>
                <span class="text-sm fw-medium">No photo yet</span>
                <span class="text-xs text-secondary-light">Take a live photo or upload a file</span>
            </div>
            <img id="photoPreview" src="" alt="Staff photo preview" class="d-none">
        @endif
    </div>
    <p class="staff-photo-help">Capture a live picture during registration, or upload one.</p>
    <div class="staff-photo-actions">
        <button type="button" class="btn btn-primary-600 w-100" id="openStaffCamera">
            <i class="ri-camera-line"></i> Take photo
        </button>
        <label class="btn btn-outline-primary-600 w-100 mb-0" for="imageUpload">
            <i class="ri-upload-2-line"></i> Upload file
        </label>
    </div>
    <input type="file" name="image" accept="image/*" id="imageUpload" class="d-none">
    <input type="hidden" name="image_data" id="staffImageData" value="">
    <button type="button" class="btn btn-sm btn-outline-neutral-400 w-100 mt-8 d-none" id="clearStaffPhoto">
        <i class="ri-close-line"></i> Remove photo
    </button>
</div>
