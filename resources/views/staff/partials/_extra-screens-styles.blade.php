<style>
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
        flex-shrink: 0;
    }

    .section-card-title {
        color: var(--primary-600, #25A194);
    }

    .extra-screens-panel {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        background: var(--white, #fff);
        overflow: hidden;
    }

    .extra-screens-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        background: var(--neutral-50, #f9fafb);
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
    }

    .extra-screens-heading {
        color: var(--primary-600, #25A194);
    }

    .extra-screens-count {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(37, 161, 148, 0.12);
        color: var(--primary-600, #25A194);
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .extra-screens-list {
        max-height: 380px;
        overflow-y: auto;
        padding: 12px;
    }

    .extra-screens-list::-webkit-scrollbar {
        width: 6px;
    }

    .extra-screens-list::-webkit-scrollbar-thumb {
        background: var(--neutral-300, #d1d5db);
        border-radius: 999px;
    }

    .extra-module-group {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 10px;
        background: var(--neutral-50, #f9fafb);
        overflow: hidden;
    }

    .extra-module-group + .extra-module-group {
        margin-top: 12px;
    }

    .extra-module-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: var(--white, #fff);
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
    }

    .extra-module-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 161, 148, 0.12);
        color: var(--primary-600, #25A194);
        font-size: 16px;
        flex-shrink: 0;
    }

    .extra-module-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
        min-width: 0;
    }

    .extra-module-title {
        font-size: 14px;
        font-weight: 600;
        margin: 0;
        line-height: 1.3;
        color: var(--primary-600, #25A194);
    }

    .extra-module-meta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-600, #4b5563);
        flex-shrink: 0;
    }

    .extra-screen-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 10px;
    }

    @media (min-width: 768px) {
        .extra-screen-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .extra-screen-tile {
        position: relative;
        display: block;
        margin: 0;
        cursor: pointer;
    }

    .extra-screen-tile input {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
    }

    .extra-screen-tile-body {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 52px;
        padding: 10px 12px;
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 10px;
        background: var(--white, #fff);
        transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
    }

    .extra-screen-tile:hover .extra-screen-tile-body {
        border-color: rgba(37, 161, 148, 0.45);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }

    .extra-screen-check {
        width: 20px;
        height: 20px;
        border-radius: 6px;
        border: 1.5px solid var(--neutral-300, #d1d5db);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: transparent;
        background: var(--white, #fff);
        font-size: 12px;
        flex-shrink: 0;
        transition: all 0.15s ease;
    }

    .extra-screen-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
        font-size: 16px;
        flex-shrink: 0;
    }

    .extra-screen-copy {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .extra-screen-name {
        display: block;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
        color: #111827;
        word-break: break-word;
    }

    .extra-screen-note {
        display: none;
        margin-top: 4px;
        font-size: 12px;
        line-height: 1.3;
        color: var(--neutral-500, #6b7280);
    }

    .extra-screen-tile.is-selected .extra-screen-tile-body {
        border-color: var(--primary-600, #25A194);
        background: rgba(37, 161, 148, 0.05);
    }

    .extra-screen-tile.is-selected .extra-screen-check {
        border-color: var(--primary-600, #25A194);
        background: var(--primary-600, #25A194);
        color: #fff;
    }

    .extra-screen-tile.is-selected .extra-screen-icon {
        background: rgba(37, 161, 148, 0.14);
    }

    .extra-screen-tile.is-selected .extra-screen-name {
        color: var(--primary-600, #25A194);
    }

    .extra-screen-tile.is-inherited {
        cursor: pointer;
    }

    .extra-screen-tile.is-inherited .extra-screen-tile-body {
        background: var(--neutral-100, #f3f4f6);
        border-style: dashed;
        box-shadow: none;
    }

    .extra-screen-tile.is-inherited:hover .extra-screen-tile-body {
        box-shadow: none;
        border-color: var(--neutral-200, #e5e7eb);
    }

    .extra-screen-tile.is-inherited .extra-screen-name {
        color: var(--neutral-600, #4b5563);
    }

    .extra-screen-tile.is-inherited .extra-screen-note {
        display: block;
    }
</style>
