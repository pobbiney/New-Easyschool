<style>
    .sv-shell { --sv-green:#168f7f; --sv-red:#d93654; --sv-ink:#10213a; }
    .sv-nav,.sv-card,.sv-stat { background:var(--white,#fff);border:1px solid #e5ebf1;border-radius:18px;box-shadow:0 8px 28px rgba(15,23,42,.045); }
    .sv-nav { padding:10px;display:flex;gap:7px;overflow-x:auto;margin-bottom:20px; }
    .sv-nav a { white-space:nowrap;text-decoration:none;color:#627084;font-weight:700;font-size:12px;padding:9px 13px;border-radius:10px; }
    .sv-nav a.active { color:#fff;background:var(--sv-green); }
    .sv-card { overflow:hidden; }
    .sv-card-head { display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:18px 20px;border-bottom:1px solid #edf1f5; }
    .sv-card-head h6 { margin:0 0 3px; }.sv-card-body { padding:20px; }
    .sv-stat { padding:17px;height:100%; }.sv-stat small { color:#718096;font-weight:650; }.sv-stat strong { display:block;color:var(--sv-ink);font-size:1.25rem;margin-top:7px; }
    .sv-table-wrap { overflow:auto; }.sv-table { min-width:1100px; }.sv-money { white-space:nowrap;font-weight:750; }.sv-credit { color:#087b67; }.sv-debit { color:#cf2f4c; }
    .sv-ref { font:11px ui-monospace,SFMono-Regular,Menlo,monospace;color:#6f7e90; }.sv-empty { padding:46px 20px;text-align:center;color:#718096; }
    .sv-empty i { display:block;color:var(--sv-green);font-size:34px;margin-bottom:7px; }.sv-note { border:1px solid #cce8e2;background:#f1fbf8;color:#17675e;border-radius:14px;padding:14px 16px; }
    .sv-warn { border-color:#f4c6cf;background:#fff5f7;color:#9e2940; }.sv-section-label { font-size:11px;letter-spacing:.09em;text-transform:uppercase;font-weight:800;color:#718096; }
    [data-theme=dark] .sv-nav,[data-theme=dark] .sv-card,[data-theme=dark] .sv-stat { background:#1e293b;border-color:#334155; }
    [data-theme=dark] .sv-card-head { border-color:#334155; }[data-theme=dark] .sv-stat strong { color:#f8fafc; }
</style>
