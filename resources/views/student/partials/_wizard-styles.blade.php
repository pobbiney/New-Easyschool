<style>
    .form-wizard-stepper {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 32px;
        position: relative;
    }

    .form-wizard-stepper::before {
        content: "";
        position: absolute;
        top: 22px;
        left: 40px;
        right: 40px;
        height: 2px;
        background: var(--neutral-200, #e5e7eb);
        z-index: 0;
    }

    .wizard-step-item {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 1;
        cursor: pointer;
    }

    .wizard-step-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--white, #fff);
        border: 2px solid var(--neutral-300, #d1d5db);
        color: var(--neutral-500, #6b7280);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .wizard-step-item.active .wizard-step-circle,
    .wizard-step-item.completed .wizard-step-circle {
        background: var(--primary-600, #25A194);
        border-color: var(--primary-600, #25A194);
        color: #fff;
    }

    .wizard-step-item.completed .wizard-step-circle {
        background: var(--success-600, #16a34a);
        border-color: var(--success-600, #16a34a);
    }

    .wizard-step-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--neutral-500, #6b7280);
        line-height: 1.3;
    }

    .wizard-step-item.active .wizard-step-label {
        color: var(--primary-600, #25A194);
    }

    .wizard-step-item.completed .wizard-step-label {
        color: var(--success-600, #16a34a);
    }

    .wizard-step-panel {
        display: none;
    }

    .wizard-step-panel.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .wizard-error-msg {
        color: #dc2626;
        font-size: 13px;
        margin-top: 8px;
        display: none;
    }

    .student-reg-grid {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 28px;
        align-items: start;
    }

    .student-photo-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px 16px 16px;
        text-align: center;
        position: sticky;
        top: 88px;
    }

    .student-photo-frame {
        width: 180px;
        height: 180px;
        margin: 0 auto 14px;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        border: 2px dashed #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .student-photo-frame.has-photo {
        border-style: solid;
        border-color: #25A194;
    }

    .student-photo-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .student-photo-placeholder {
        color: #94a3b8;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 12px;
    }

    .student-photo-placeholder i {
        font-size: 36px;
        color: #25A194;
    }

    .student-photo-placeholder span {
        font-size: 12px;
        font-weight: 600;
    }

    .student-photo-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .student-photo-help {
        font-size: 12px;
        color: #64748b;
        line-height: 1.45;
        margin-bottom: 14px;
    }

    .student-photo-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .student-camera-stage {
        position: relative;
        background: #0f172a;
        border-radius: 14px;
        overflow: hidden;
        min-height: 280px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #studentCameraVideo,
    #studentCameraShot {
        width: 100%;
        max-height: 360px;
        object-fit: cover;
        transform: scaleX(-1);
        display: block;
    }

    .student-camera-status {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 12px;
        color: #e2e8f0;
        font-size: 13px;
        text-align: center;
        text-shadow: 0 1px 4px rgba(0,0,0,.45);
    }

    @media (max-width: 991px) {
        .student-reg-grid {
            grid-template-columns: 1fr;
        }

        .student-photo-card {
            position: static;
            max-width: 360px;
            margin: 0 auto 8px;
        }
    }

    @media (max-width: 767px) {
        .form-wizard-stepper {
            flex-direction: column;
            align-items: stretch;
        }

        .form-wizard-stepper::before {
            display: none;
        }

        .wizard-step-item {
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
        }

        .wizard-step-circle {
            margin: 0;
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            font-size: 14px;
        }
    }
</style>
