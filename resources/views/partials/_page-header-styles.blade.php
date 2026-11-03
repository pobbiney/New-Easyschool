<style>
    /* Premium page header */
    .dashboard-main-body > .page-header,
    .dashboard-main-body > .breadcrumb {
        --ph-accent: #25a194;
        --ph-accent-2: #6366f1;
        --ph-ink: #0f172a;
        --ph-muted: #64748b;

        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 28px !important;
        padding: 28px 32px;
        border-radius: 22px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background-color: #fff;
        background-image:
            radial-gradient(circle at 0% 0%, rgba(37, 161, 148, 0.14) 0%, transparent 42%),
            radial-gradient(circle at 100% 0%, rgba(99, 102, 241, 0.1) 0%, transparent 38%),
            radial-gradient(circle at 100% 100%, rgba(37, 161, 148, 0.06) 0%, transparent 35%),
            url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%2394a3b8' fill-opacity='0.08'%3E%3Ccircle cx='1' cy='1' r='1'/%3E%3C/g%3E%3C/svg%3E");
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.04),
            0 12px 40px rgba(15, 23, 42, 0.07);
        overflow: hidden;
        isolation: isolate;
    }

    .dashboard-main-body > .page-header::before,
    .dashboard-main-body > .breadcrumb::before {
        content: "";
        position: absolute;
        inset-inline-start: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: linear-gradient(180deg, var(--ph-accent) 0%, var(--ph-accent-2) 100%);
        box-shadow: 0 0 20px rgba(37, 161, 148, 0.35);
    }

    .dashboard-main-body > .page-header::after,
    .dashboard-main-body > .breadcrumb::after {
        content: "";
        position: absolute;
        right: -20px;
        top: -20px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(37, 161, 148, 0.15), rgba(99, 102, 241, 0.08));
        filter: blur(2px);
        pointer-events: none;
    }

    .dashboard-main-body > .page-header > div:first-child,
    .dashboard-main-body > .breadcrumb > div:first-child {
        position: relative;
        z-index: 1;
        min-width: 0;
        flex: 1;
    }

    .dashboard-main-body > .page-header > .btn,
    .dashboard-main-body > .breadcrumb > .btn,
    .dashboard-main-body > .page-header > a.btn,
    .dashboard-main-body > .breadcrumb > a.btn,
    .dashboard-main-body > .page-header > button.btn,
    .dashboard-main-body > .breadcrumb > button.btn,
    .dashboard-main-body > .page-header > .gb-hero-actions,
    .dashboard-main-body > .breadcrumb > .gb-hero-actions,
    .dashboard-main-body > .page-header > div:last-child:not(:only-child),
    .dashboard-main-body > .breadcrumb > div:last-child:not(:only-child) {
        position: relative;
        z-index: 1;
        flex-shrink: 0;
        align-self: center;
    }

    /* Section badge */
    .dashboard-main-body > .page-header h1,
    .dashboard-main-body > .breadcrumb h1 {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 12px !important;
        padding: 5px 12px 5px 10px;
        font-size: 0.6875rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #fff !important;
        background: linear-gradient(135deg, var(--ph-accent) 0%, #1d8a7f 100%);
        border: none;
        border-radius: 999px;
        line-height: 1.3;
        box-shadow: 0 4px 14px rgba(37, 161, 148, 0.28);
    }

    .dashboard-main-body > .page-header h1::before,
    .dashboard-main-body > .breadcrumb h1::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.85);
        box-shadow: 0 0 8px rgba(255, 255, 255, 0.6);
    }

    /* Large page title (injected or dashboard h6) */
    .dashboard-main-body > .page-header .ph-page-title,
    .dashboard-main-body > .breadcrumb .ph-page-title {
        margin: 0 0 10px !important;
        font-size: clamp(1.375rem, 2.5vw, 1.875rem) !important;
        font-weight: 800 !important;
        letter-spacing: -0.03em;
        line-height: 1.15;
        color: var(--ph-ink) !important;
        background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .dashboard-main-body > .page-header > div > h6.mb-0,
    .dashboard-main-body > .breadcrumb > div > h6.mb-0 {
        margin: 0 0 8px !important;
        font-size: clamp(1.375rem, 2.5vw, 1.875rem) !important;
        font-weight: 800 !important;
        letter-spacing: -0.03em;
        color: var(--ph-ink) !important;
        line-height: 1.15;
    }

    /* Breadcrumb trail pill */
    .dashboard-main-body > .page-header .ph-trail,
    .dashboard-main-body > .breadcrumb .ph-trail,
    .dashboard-main-body > .page-header > div > div,
    .dashboard-main-body > .breadcrumb > div > div {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 2px;
        margin-top: 2px;
        padding: 6px 12px;
        font-size: 0.8125rem;
        line-height: 1.4;
        color: var(--ph-muted);
        background: rgba(248, 250, 252, 0.92);
        border: 1px solid #eef2f7;
        border-radius: 999px;
        backdrop-filter: blur(4px);
    }

    .dashboard-main-body > .page-header .ph-trail a,
    .dashboard-main-body > .breadcrumb .ph-trail a,
    .dashboard-main-body > .page-header > div > div a,
    .dashboard-main-body > .breadcrumb > div > div a {
        color: #475569;
        font-weight: 500;
        text-decoration: none !important;
        padding: 2px 4px;
        border-radius: 6px;
        transition: color 0.15s, background 0.15s;
    }

    .dashboard-main-body > .page-header .ph-trail a:hover,
    .dashboard-main-body > .breadcrumb .ph-trail a:hover,
    .dashboard-main-body > .page-header > div > div a:hover,
    .dashboard-main-body > .breadcrumb > div > div a:hover {
        color: var(--ph-accent);
        background: rgba(37, 161, 148, 0.08);
    }

    .dashboard-main-body > .page-header .ph-trail .ph-sep,
    .dashboard-main-body > .breadcrumb .ph-trail .ph-sep {
        display: inline-flex;
        align-items: center;
        color: #cbd5e1;
        font-size: 0.75rem;
        margin: 0 2px;
        user-select: none;
    }

    .dashboard-main-body > .page-header .ph-trail .ph-current,
    .dashboard-main-body > .breadcrumb .ph-trail .ph-current {
        color: var(--ph-accent);
        font-weight: 600;
        padding: 2px 4px;
    }

    .dashboard-main-body > .page-header > div > div span.text-secondary-light,
    .dashboard-main-body > .breadcrumb > div > div span.text-secondary-light {
        color: var(--ph-muted);
    }

    .dashboard-main-body > .page-header > div > p,
    .dashboard-main-body > .breadcrumb > div > p {
        margin: 12px 0 0 !important;
        padding-left: 2px;
        font-size: 0.875rem;
        color: var(--ph-muted);
        line-height: 1.6;
        max-width: 48rem;
    }

    .dashboard-main-body > .page-header .btn-primary-600,
    .dashboard-main-body > .breadcrumb .btn-primary-600 {
        border-radius: 12px;
        padding: 10px 18px;
        font-weight: 600;
        box-shadow: 0 8px 22px rgba(37, 161, 148, 0.25);
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .dashboard-main-body > .page-header .btn-primary-600:hover,
    .dashboard-main-body > .breadcrumb .btn-primary-600:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px rgba(37, 161, 148, 0.32);
    }

    @media (max-width: 767px) {
        .dashboard-main-body > .page-header,
        .dashboard-main-body > .breadcrumb {
            padding: 22px 20px;
            border-radius: 18px;
            align-items: flex-start;
        }

        .dashboard-main-body > .page-header > .btn,
        .dashboard-main-body > .breadcrumb > .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.dashboard-main-body > .page-header, .dashboard-main-body > .breadcrumb').forEach(function (header) {
        if (header.dataset.phEnhanced === '1') {
            return;
        }

        const content = header.querySelector(':scope > div:first-child');
        if (!content) {
            return;
        }

        const sectionLabel = content.querySelector('h1');
        const standaloneTitle = content.querySelector('h6.mb-0, h6.fw-semibold');
        const trails = content.querySelectorAll(':scope > div');

        trails.forEach(function (trail) {
            if (trail.dataset.phEnhanced === '1' || !trail.querySelector('a, span')) {
                return;
            }

            const parts = [];
            trail.querySelectorAll('a, span').forEach(function (node) {
                const isAnchor = node.tagName === 'A';
                const href = isAnchor ? node.getAttribute('href') : null;
                const text = (node.textContent || '').replace(/\s+/g, ' ').trim();

                if (!text) {
                    return;
                }

                text.split('/').forEach(function (piece) {
                    const label = piece.trim();
                    if (label) {
                        parts.push({
                            label: label,
                            href: isAnchor ? href : null,
                        });
                    }
                });
            });

            if (parts.length === 0) {
                return;
            }

            const unique = [];
            parts.forEach(function (part) {
                if (!unique.length || unique[unique.length - 1].label.toLowerCase() !== part.label.toLowerCase()) {
                    unique.push(part);
                }
            });

            if (sectionLabel && unique.length && !content.querySelector('.ph-page-title')) {
                const last = unique[unique.length - 1];
                const title = document.createElement('h2');
                title.className = 'ph-page-title';
                title.textContent = last.label;
                sectionLabel.insertAdjacentElement('afterend', title);
                unique.pop();
            }

            if (unique.length === 0) {
                trail.remove();
                return;
            }

            trail.classList.add('ph-trail');
            trail.innerHTML = '';

            unique.forEach(function (part, index) {
                if (index > 0) {
                    const sep = document.createElement('span');
                    sep.className = 'ph-sep';
                    sep.innerHTML = '<i class="ri-arrow-right-s-line"></i>';
                    trail.appendChild(sep);
                }

                if (part.href && index < unique.length - 1) {
                    const link = document.createElement('a');
                    link.href = part.href;
                    link.textContent = part.label;
                    link.className = 'hover-text-primary';
                    trail.appendChild(link);
                } else if (index === unique.length - 1) {
                    const current = document.createElement('span');
                    current.className = 'ph-current';
                    current.textContent = part.label;
                    trail.appendChild(current);
                } else {
                    const span = document.createElement('span');
                    span.textContent = part.label;
                    trail.appendChild(span);
                }
            });

            trail.dataset.phEnhanced = '1';
        });

        if (standaloneTitle && !sectionLabel && !content.querySelector('.ph-page-title')) {
            standaloneTitle.classList.add('ph-page-title');
        }

        header.dataset.phEnhanced = '1';
    });
});
</script>
