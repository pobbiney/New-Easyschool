<style>
    .staff-form-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
    }

    .staff-sidebar-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        position: sticky;
        top: 96px;
    }

    .staff-photo-box {
        width: 100%;
        max-width: 220px;
        aspect-ratio: 1;
        margin: 0 auto;
        border: 2px dashed var(--neutral-300, #d1d5db);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        background: var(--neutral-50, #f9fafb);
        transition: border-color 0.2s ease;
    }

    .staff-photo-box:hover {
        border-color: var(--primary-600, #25A194);
    }

    .staff-photo-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .staff-photo-placeholder {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: var(--neutral-500, #6b7280);
        padding: 16px;
        text-align: center;
    }

    .staff-photo-placeholder i {
        font-size: 36px;
        color: var(--primary-600, #25A194);
    }

    .staff-photo-box.has-photo {
        border-style: solid;
        border-color: var(--primary-600, #25A194);
        cursor: default;
    }

    .staff-photo-help {
        font-size: 12px;
        color: #64748b;
        line-height: 1.45;
        text-align: center;
        margin: 12px 0 12px;
    }

    .staff-photo-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-width: 220px;
        margin: 0 auto;
    }

    .staff-camera-stage {
        position: relative;
        background: #0f172a;
        border-radius: 14px;
        overflow: hidden;
        min-height: 280px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #staffCameraVideo,
    #staffCameraShot {
        width: 100%;
        max-height: 360px;
        object-fit: cover;
        transform: scaleX(-1);
        display: block;
    }

    .staff-camera-status {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 12px;
        color: #e2e8f0;
        font-size: 13px;
        text-align: center;
        text-shadow: 0 1px 4px rgba(0,0,0,.45);
        pointer-events: none;
    }

    #staffCameraModal {
        z-index: 1080;
    }

    .staff-id-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 999px;
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
        font-weight: 600;
        font-size: 14px;
    }

    .section-title-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
        font-size: 18px;
    }

    .section-card-title {
        color: var(--primary-600, #25A194);
    }

    .screen-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
        margin: 0 6px 6px 0;
    }

    .inherited-screens-box {
        min-height: 56px;
        background: var(--neutral-50, #f9fafb);
    }

    .system-access-fields.is-disabled {
        opacity: 0.55;
        pointer-events: none;
    }

    .staff-tip-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .staff-tip-list li {
        display: flex;
        gap: 10px;
        font-size: 13px;
        color: var(--neutral-600, #4b5563);
        margin-bottom: 10px;
    }

    .staff-tip-list li i {
        color: var(--primary-600, #25A194);
        margin-top: 2px;
    }

    .staff-form-actions {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        background: var(--white, #fff);
        padding: 16px 20px;
    }

    .qualification-row {
        background: var(--neutral-50, #f9fafb);
        border-color: var(--neutral-200, #e5e7eb) !important;
    }

    .qualification-row-label {
        color: var(--primary-600, #25A194);
    }
</style>
