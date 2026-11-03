    .idcard-face {
        width: 320px;
        height: 508px;
        margin: 0 auto;
        border-radius: 18px;
        overflow: hidden;
        position: relative;
        background: #fff;
        box-shadow: 0 22px 50px rgba(15, 23, 42, .22);
        font-family: "Segoe UI", Arial, sans-serif;
    }
    .idcard-face-head {
        height: 168px;
        background:
            radial-gradient(circle at 82% 18%, color-mix(in srgb, var(--card-accent) 55%, transparent) 0 2px, transparent 2.5px),
            radial-gradient(circle at 88% 42%, color-mix(in srgb, var(--card-accent) 40%, transparent) 0 1.6px, transparent 2px),
            linear-gradient(145deg, var(--card-dark) 0%, var(--card-ink) 58%, #0b0614 100%);
        background-size: 14px 14px, 10px 10px, auto;
        position: relative;
        padding: 18px 18px 0;
        color: #fff;
    }
    .idcard-face-head::after {
        content: "";
        position: absolute;
        right: 0; top: 0; bottom: 0;
        width: 46%;
        background:
            repeating-linear-gradient(32deg, transparent 0 9px, color-mix(in srgb, var(--card-accent) 28%, transparent) 9px 10px),
            repeating-linear-gradient(-32deg, transparent 0 9px, color-mix(in srgb, var(--card-accent) 18%, transparent) 9px 10px);
        clip-path: polygon(38% 0, 100% 0, 100% 100%, 0 100%);
        opacity: .9;
        pointer-events: none;
    }
    .idcard-head-row {
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        z-index: 2;
    }
    .idcard-hex {
        width: 96px;
        height: 106px;
        flex-shrink: 0;
        clip-path: polygon(50% 0, 100% 25%, 100% 75%, 50% 100%, 0 75%, 0 25%);
        background: var(--card-accent);
        padding: 3px;
    }
    .idcard-hex-inner {
        width: 100%;
        height: 100%;
        clip-path: polygon(50% 0, 100% 25%, 100% 75%, 50% 100%, 0 75%, 0 25%);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .idcard-hex-inner img { width: 96%; height: 96%; object-fit: contain; }
    .idcard-school {
        font-size: 20px;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        line-height: 1.12;
        margin: 0;
        color: var(--card-accent);
        text-shadow: 0 1px 0 rgba(0,0,0,.25);
    }
    .idcard-tagline {
        margin: 5px 0 0;
        font-size: 12px;
        color: #fff;
        opacity: .92;
    }
    .idcard-wave {
        position: absolute;
        left: 0; right: 0; bottom: -1px;
        height: 42px;
        background: #fff;
        clip-path: polygon(0 70%, 38% 28%, 100% 62%, 100% 100%, 0 100%);
        z-index: 2;
    }
    .idcard-stripe {
        position: absolute;
        left: 0; right: 0;
        bottom: 18px;
        height: 10px;
        background: var(--card-accent);
        clip-path: polygon(0 40%, 42% 0, 100% 55%, 100% 100%, 0 100%);
        z-index: 3;
    }
    .idcard-photo-wrap {
        position: relative;
        z-index: 4;
        margin: -52px auto 8px;
        width: 132px;
        height: 132px;
    }
    .idcard-photo {
        width: 132px;
        height: 132px;
        border-radius: 50%;
        border: 6px solid var(--card-accent);
        background: #d9d9d9;
        box-shadow: 0 8px 18px rgba(15,23,42,.18);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: #8a8a8a;
        font-size: 64px;
    }
    .idcard-photo img { width: 100%; height: 100%; object-fit: cover; }
    .idcard-body {
        padding: 4px 28px 70px;
        text-align: center;
        position: relative;
        z-index: 4;
    }
    .idcard-role {
        margin: 4px 0 12px;
        font-size: 13px;
        color: #64748b;
        position: relative;
        z-index: 1;
    }
    .idcard-name {
        font-size: 20px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--card-dark);
        margin: 0;
        line-height: 1.15;
        position: relative;
        z-index: 1;
    }
    .idcard-mid-mark {
        position: absolute;
        left: 50%;
        top: 44px;
        transform: translateX(-50%);
        z-index: 0;
        margin: 0;
        pointer-events: none;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .idcard-mid-mark img {
        width: 150px;
        height: 150px;
        object-fit: contain;
        opacity: .18;
    }
    .idcard-meta {
        text-align: center;
        font-size: 12px;
        color: #334155;
        line-height: 1.7;
        min-height: 72px;
        position: relative;
        z-index: 1;
    }
    .idcard-meta b { color: var(--card-dark); font-weight: 800; }
    .idcard-meta .idcard-meta-lg {
        font-size: 17px;
        line-height: 1.45;
        font-weight: 700;
        margin-bottom: 4px;
        color: #1e293b;
    }
    .idcard-meta .idcard-meta-lg b { font-size: 16px; }
    .idcard-barcode {
        margin: 10px auto 0;
        width: 78px;
        height: 78px;
        padding: 3px;
        background: #fff;
        box-shadow: 0 0 0 1px #e2e8f0;
        position: relative;
        z-index: 6;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .idcard-barcode svg {
        width: 72px;
        height: 72px;
        display: block;
    }
    .idcard-qr-pane { display: none; }
    .idcard-qr-pane.is-on { display: block; }
    .idcard-sign-wrap {
        position: absolute;
        left: 18px;
        bottom: 28px;
        z-index: 5;
        text-align: left;
    }
    .idcard-sign-wrap img { max-height: 32px; max-width: 90px; object-fit: contain; display: block; }
    .idcard-sign-wrap span { display: block; font-size: 9px; color: #64748b; margin-top: 2px; }
    .idcard-foot {
        position: absolute;
        left: 0; right: 0; bottom: 0;
        height: 42px;
        background: var(--card-ink);
        clip-path: polygon(0 72%, 100% 28%, 100% 100%, 0 100%);
        z-index: 1;
    }
    .idcard-foot-accent {
        position: absolute;
        left: 0; right: 0; bottom: 0;
        height: 42px;
        background: var(--card-accent);
        clip-path: polygon(0 88%, 58% 48%, 100% 70%, 100% 100%, 0 100%);
        z-index: 2;
    }

    .idcard-back-logo {
        width: 128px;
        height: 128px;
        margin: -118px auto 0;
        border-radius: 50%;
        background: #fff;
        border: 4px solid var(--card-accent);
        box-shadow: 0 10px 22px rgba(15,23,42,.16);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        z-index: 4;
    }
    .idcard-back-logo img { width: 90%; height: 90%; object-fit: contain; }
    .idcard-back-body {
        padding: 0 22px 188px;
        text-align: center;
        position: static;
        z-index: 4;
    }
    .idcard-back-school {
        font-size: 15px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--card-dark);
        margin: 0 0 2px;
        line-height: 1.2;
    }
    .idcard-back-motto {
        font-size: 11px;
        font-style: italic;
        color: #64748b;
        margin: 0 0 4px;
    }
    .idcard-back-block {
        text-align: left;
        background: color-mix(in srgb, var(--card-accent) 10%, #fff);
        border: 1px solid color-mix(in srgb, var(--card-accent) 28%, #fff);
        border-radius: 12px;
        padding: 8px 12px;
        margin-bottom: 6px;
    }
    .idcard-back-kicker {
        display: block;
        margin: 0 0 8px;
        font-size: 15px;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--card-dark);
        font-weight: 700;
        line-height: 1.2;
    }
    .idcard-back-line {
        display: flex;
        gap: 8px;
        align-items: flex-start;
        font-size: 12px;
        color: #334155;
        margin-bottom: 6px;
        line-height: 1.35;
    }
    .idcard-back-line:last-child { margin-bottom: 0; }
    .idcard-back-line i { color: var(--card-dark); font-size: 15px; margin-top: 1px; }
    .idcard-back-note {
        font-size: 11px;
        color: #475569;
        line-height: 1.45;
        margin: 0;
    }
    .idcard-back-sign {
        position: absolute;
        left: 50%;
        bottom: 72px;
        transform: translateX(-50%);
        text-align: center;
        width: 90%;
        z-index: 6;
        padding-top: 0;
        margin-top: 0;
    }
    .idcard-back-sign img {
        max-height: 132px;
        max-width: 260px;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
        margin: 0 auto -58px;
    }
    .idcard-back-sign .line {
        width: 200px;
        border-bottom: 1px solid #94a3b8;
        margin: 4px auto 0;
    }
    .idcard-back-sign span { display: block; font-size: 12px; font-weight: 700; color: var(--card-dark); }
    .idcard-back-sign small {
        display: block;
        font-size: 9px;
        color: #64748b;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-top: 0;
        line-height: 1;
        position: relative;
        z-index: 1;
    }
    .idcard-face.is-back .idcard-foot {
        clip-path: polygon(0 28%, 100% 72%, 100% 100%, 0 100%);
    }
    .idcard-face.is-back .idcard-foot-accent {
        clip-path: polygon(0 70%, 42% 48%, 100% 88%, 100% 100%, 0 100%);
    }
