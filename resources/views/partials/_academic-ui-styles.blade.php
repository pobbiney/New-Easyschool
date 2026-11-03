<style>
    /* Shared academic UI — matches assignment / billing pages */
    .ac-hero,
    .ac-stat-card,
    .ac-list-wrapper {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        background: #fff;
    }

    .ac-hero {
        padding: 24px 28px;
        background: linear-gradient(135deg, rgba(37, 161, 148, 0.12), rgba(99, 102, 241, 0.08));
        margin-bottom: 24px;
    }

    .ac-stat-card {
        padding: 18px 20px;
        height: 100%;
    }

    .ac-stat-card .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .ac-list-wrapper {
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .ac-list-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .ac-avatar {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
    }

    .ac-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .ac-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Colored pills */
    .ac-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .ac-pill-teal    { background: rgba(37, 161, 148, 0.1);  color: #1a7a70; }
    .ac-pill-indigo  { background: rgba(99, 102, 241, 0.1);  color: #4338ca; }
    .ac-pill-amber   { background: rgba(245, 158, 11, 0.12);  color: #b45309; }
    .ac-pill-rose    { background: rgba(244, 63, 94, 0.1);   color: #be123c; }
    .ac-pill-emerald { background: rgba(34, 197, 94, 0.1);   color: #15803d; }
    .ac-pill-sky     { background: rgba(14, 165, 233, 0.1);  color: #0369a1; }
    .ac-pill-violet  { background: rgba(139, 92, 246, 0.1);  color: #6d28d9; }
    .ac-pill-slate   { background: rgba(100, 116, 139, 0.1); color: #475569; }
    .ac-pill-orange  { background: rgba(234, 88, 12, 0.1);  color: #c2410c; }

    .ac-pill-active   { background: rgba(34, 197, 94, 0.1);  color: #15803d; }
    .ac-pill-inactive { background: rgba(239, 68, 68, 0.1);  color: #b91c1c; }
    .ac-pill-draft    { background: rgba(245, 158, 11, 0.12); color: #b45309; }
    .ac-pill-published { background: rgba(34, 197, 94, 0.1); color: #15803d; }

    .ac-pill-present  { background: rgba(34, 197, 94, 0.1);  color: #15803d; }
    .ac-pill-absent   { background: rgba(239, 68, 68, 0.1);  color: #b91c1c; }
    .ac-pill-late     { background: rgba(245, 158, 11, 0.12); color: #b45309; }
    .ac-pill-excused  { background: rgba(99, 102, 241, 0.1);  color: #4338ca; }

    .ac-pill-homework         { background: rgba(14, 165, 233, 0.1);  color: #0369a1; }
    .ac-pill-class_test       { background: rgba(139, 92, 246, 0.1);  color: #6d28d9; }
    .ac-pill-exam             { background: rgba(244, 63, 94, 0.1);   color: #be123c; }
    .ac-pill-class_assignment { background: rgba(234, 88, 12, 0.1);  color: #c2410c; }

    .ac-pill-grade-a { background: rgba(34, 197, 94, 0.12);  color: #15803d; }
    .ac-pill-grade-b { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
    .ac-pill-grade-c { background: rgba(245, 158, 11, 0.12); color: #b45309; }
    .ac-pill-grade-d { background: rgba(234, 88, 12, 0.12); color: #c2410c; }
    .ac-pill-grade-f { background: rgba(239, 68, 68, 0.12);  color: #b91c1c; }

    .ac-workspace-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        padding: 16px 18px;
        background: #fff;
        height: 100%;
        transition: box-shadow 0.15s ease;
    }

    .ac-workspace-card:hover {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .ac-workspace-card .card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .ac-action-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ac-action-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid transparent;
        transition: opacity 0.15s ease;
    }

    .ac-action-pill:hover { opacity: 0.85; color: inherit; }

    .ac-action-pill-teal    { background: rgba(37, 161, 148, 0.1);  color: #1a7a70; border-color: rgba(37, 161, 148, 0.2); }
    .ac-action-pill-indigo  { background: rgba(99, 102, 241, 0.1);  color: #4338ca; border-color: rgba(99, 102, 241, 0.2); }
    .ac-action-pill-amber   { background: rgba(245, 158, 11, 0.12);  color: #b45309; border-color: rgba(245, 158, 11, 0.25); }
    .ac-action-pill-emerald { background: rgba(34, 197, 94, 0.1);  color: #15803d; border-color: rgba(34, 197, 94, 0.2); }
    .ac-action-pill-rose    { background: rgba(244, 63, 94, 0.1);   color: #be123c; border-color: rgba(244, 63, 94, 0.2); }

    .ac-filter-bar {
        padding: 16px 20px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: var(--neutral-50, #f9fafb);
    }

    .ac-summary-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .ac-summary-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }
</style>
