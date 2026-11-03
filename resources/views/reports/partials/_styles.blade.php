<style>
    .rpt-actions { display: flex; flex-wrap: wrap; gap: 12px; }
    .rpt-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 10px;
        min-width: 128px; min-height: 48px; padding: 14px 22px; border-radius: 14px;
        font-size: 16px; font-weight: 800;
        text-decoration: none; border: 0; color: #fff !important; line-height: 1.1;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .14);
        transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
    }
    .rpt-btn i { font-size: 20px; line-height: 1; }
    .rpt-btn:hover { color: #fff !important; transform: translateY(-1px); filter: brightness(1.05); }
    .rpt-btn-print, .btn-print {
        background: linear-gradient(135deg, #6366f1, #4338ca);
        box-shadow: 0 8px 18px rgba(99, 102, 241, .28);
    }
    .rpt-btn-pdf, .btn-pdf {
        background: linear-gradient(135deg, #f43f5e, #be123c);
        box-shadow: 0 8px 18px rgba(244, 63, 94, .28);
    }
    .rpt-btn-excel, .btn-excel {
        background: linear-gradient(135deg, #22c55e, #15803d);
        box-shadow: 0 8px 18px rgba(34, 197, 94, .28);
    }
    .rpt-kpi-icon {
        width: 44px; height: 44px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-size: 20px; flex-shrink: 0;
    }
    .rpt-hero {
        position: relative; overflow: hidden; border-radius: 20px; padding: 28px 32px; margin-bottom: 24px;
        background: linear-gradient(120deg, #ecfeff 0%, #e0e7ff 42%, #fce7f3 100%);
        border: 1px solid #c7d2fe;
        box-shadow: 0 18px 40px rgba(99, 102, 241, .12);
    }
    .rpt-hero-icon {
        width: 58px; height: 58px; border-radius: 18px; display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #6366f1, #25A194); color: #fff; font-size: 26px; flex-shrink: 0;
        box-shadow: 0 12px 24px rgba(99, 102, 241, .35);
    }
    .rpt-hero-title { font-size: 1.35rem; font-weight: 800; letter-spacing: -.02em; color: #0f172a; margin-bottom: 6px; }
    .rpt-hero-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
    .rpt-hero-tag {
        display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 800; color: #fff;
    }
    .rpt-tag-0 { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .rpt-tag-1 { background: linear-gradient(135deg, #f43f5e, #e11d48); }
    .rpt-tag-2 { background: linear-gradient(135deg, #25A194, #0f766e); }
    .rpt-tag-3 { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .rpt-tag-4 { background: linear-gradient(135deg, #818cf8, #6d28d9); }
    .rpt-tag-5 { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .rpt-board { border: 1px solid #e5e7eb; border-radius: 20px; overflow: hidden; background: #fff; box-shadow: 0 10px 30px rgba(15,23,42,.05); }
    .rpt-filter { padding: 18px 24px; background: linear-gradient(90deg, #f0fdfa, #eef2ff); border-bottom: 1px solid #e0e7ff; }
    .rpt-mix { padding: 18px 24px; border-bottom: 1px solid #eef2f6; background: linear-gradient(180deg, #fff 0%, #f8fafc 100%); }
    .rpt-mix-bar { display: flex; height: 14px; border-radius: 999px; overflow: hidden; background: #e2e8f0; box-shadow: inset 0 1px 2px rgba(15,23,42,.08); }
    .rpt-mix-bar span { display: block; height: 100%; }
    .rpt-mix-male { background: linear-gradient(90deg, #60a5fa, #2563eb); }
    .rpt-mix-female { background: linear-gradient(90deg, #f472b6, #db2777); }
    .rpt-mix-other { background: linear-gradient(90deg, #a78bfa, #7c3aed); }
    .rpt-mix-legend { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 10px; font-size: 12px; font-weight: 700; }
    .rpt-mix-legend .male { color: #1d4ed8; }
    .rpt-mix-legend .female { color: #be185d; }
    .rpt-mix-legend .other { color: #6d28d9; }
    .rpt-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
    .rpt-scroll { overflow-x: auto; }
    .rpt-empty { text-align: center; padding: 72px 24px; }
    .rpt-empty-icon {
        width: 72px; height: 72px; margin: 0 auto 16px; border-radius: 22px; display: flex; align-items: center; justify-content: center;
        font-size: 30px; color: #fff; background: linear-gradient(135deg, #6366f1, #25A194);
    }
    .rpt-person { display: flex; align-items: center; gap: 12px; }
    .rpt-avatar {
        width: 38px; height: 38px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 12px; color: #fff; flex-shrink: 0;
    }
    .rpt-tone-0 { background: #25A194; }
    .rpt-tone-1 { background: #6366f1; }
    .rpt-tone-2 { background: #f59e0b; }
    .rpt-tone-3 { background: #ec4899; }
    .rpt-tone-4 { background: #3b82f6; }
    .rpt-tone-5 { background: #a855f7; }
    .rpt-pill {
        display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 800; color: #fff; white-space: nowrap;
    }
    .rpt-pill-ok { background: linear-gradient(135deg, #22c55e, #15803d); }
    .rpt-pill-bad { background: linear-gradient(135deg, #f43f5e, #be123c); }
    .rpt-pill-warn { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .rpt-pill-info { background: linear-gradient(135deg, #818cf8, #6d28d9); }
    .rpt-pill-teal { background: linear-gradient(135deg, #25A194, #0f766e); }
    .rpt-pill-sky { background: linear-gradient(135deg, #38bdf8, #0284c7); }
    .rpt-pill-pink { background: linear-gradient(135deg, #f472b6, #db2777); }
    .rpt-pill-slate { background: linear-gradient(135deg, #64748b, #334155); }
    .rpt-money { color: #0f766e; font-weight: 800; white-space: nowrap; }
    .rpt-num-male { color: #1d4ed8; font-weight: 800; }
    .rpt-num-female { color: #be185d; font-weight: 800; }
    .rpt-num-total { color: #0f766e; font-weight: 800; }
    @media (max-width: 767px) { .rpt-hero { padding: 22px 18px; } }
</style>
