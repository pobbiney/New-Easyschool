<style>
    .ah-hero-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }

    .ah-type-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .ah-workspace-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }
    @media (max-width: 991px) { .ah-workspace-grid { grid-template-columns: 1fr; } }

    .ah-panel {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        height: 100%;
    }

    .ah-panel-head {
        padding: 18px 22px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: linear-gradient(180deg, #fafafa, #fff);
    }

    .ah-panel-body { padding: 16px 18px 20px; }

    .ah-slot-card {
        border: 1px solid #eef2f7;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 12px;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
        background: #fff;
    }

    .ah-slot-card:hover {
        border-color: rgba(37, 161, 148, 0.25);
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
    }

    .ah-slot-card:last-child { margin-bottom: 0; }

    .ah-slot-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .ah-empty-slot {
        text-align: center;
        padding: 32px 20px;
        border: 2px dashed #e5e7eb;
        border-radius: 14px;
        background: #fafafa;
    }

    .ah-empty-slot i { font-size: 28px; color: #9ca3af; margin-bottom: 8px; display: block; }

    .ah-assessment-panel .table thead th {
        background: #f9fafb;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6b7280;
        padding: 14px 16px;
        white-space: nowrap;
        border-bottom: 1px solid #e5e7eb;
    }

    .ah-assessment-panel .table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }

    .ah-assessment-panel .table tbody tr:hover td {
        background: rgba(37, 161, 148, 0.03);
    }

    .ah-assessment-title {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 200px;
    }

    .ah-assessment-icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .ah-assessment-icon.homework { background: rgba(14, 165, 233, 0.12); color: #0369a1; }
    .ah-assessment-icon.class_test { background: rgba(139, 92, 246, 0.12); color: #6d28d9; }
    .ah-assessment-icon.exam { background: rgba(244, 63, 94, 0.12); color: #be123c; }
    .ah-assessment-icon.class_assignment { background: rgba(234, 88, 12, 0.12); color: #c2410c; }

    .ah-assessment-meta {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
    }

    .ah-toolbar {
        padding: 14px 22px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .ah-filter-pills { display: flex; flex-wrap: wrap; gap: 8px; }

    .ah-filter-pill {
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #64748b;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .ah-filter-pill:hover,
    .ah-filter-pill.is-active {
        border-color: rgba(37, 161, 148, 0.35);
        background: rgba(37, 161, 148, 0.08);
        color: #1a7a70;
    }

    .ah-empty-assessments {
        text-align: center;
        padding: 56px 24px;
    }

    .ah-empty-assessments .ac-avatar {
        width: 64px;
        height: 64px;
        font-size: 28px;
        margin: 0 auto 16px;
        background: rgba(14, 165, 233, 0.1);
        color: #0369a1;
    }

    .ah-action-form { display: inline; margin: 0; }

    .ah-action-form button.ac-action-pill {
        cursor: pointer;
        font: inherit;
    }
</style>
