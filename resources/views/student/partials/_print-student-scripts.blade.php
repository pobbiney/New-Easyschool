<script>
    function rgbToHex(r, g, b) {
        return '#' + [r, g, b].map(function (v) {
            return ('0' + Math.max(0, Math.min(255, v)).toString(16)).slice(-2);
        }).join('');
    }

    function hexToRgb(hex) {
        let result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    function darkenHex(hex, amount) {
        let rgb = hexToRgb(hex);
        if (!rgb) return hex;
        return rgbToHex(
            Math.round(rgb.r * (1 - amount)),
            Math.round(rgb.g * (1 - amount)),
            Math.round(rgb.b * (1 - amount))
        );
    }

    function extractDominantColor(img) {
        let canvas = document.createElement('canvas');
        let ctx = canvas.getContext('2d');
        let size = 64;
        canvas.width = size;
        canvas.height = size;

        try {
            ctx.drawImage(img, 0, 0, size, size);
        } catch (e) {
            return null;
        }

        let data;
        try {
            data = ctx.getImageData(0, 0, size, size).data;
        } catch (e) {
            return null;
        }

        let buckets = {};

        for (let i = 0; i < data.length; i += 4) {
            let r = data[i];
            let g = data[i + 1];
            let b = data[i + 2];
            let a = data[i + 3];

            if (a < 100) continue;

            let brightness = (r + g + b) / 3;
            if (brightness > 235 || brightness < 25) continue;

            let saturation = Math.max(r, g, b) - Math.min(r, g, b);
            if (saturation < 18) continue;

            let key = Math.round(r / 24) + ',' + Math.round(g / 24) + ',' + Math.round(b / 24);
            if (!buckets[key]) {
                buckets[key] = { r: 0, g: 0, b: 0, count: 0, sat: 0 };
            }
            buckets[key].r += r;
            buckets[key].g += g;
            buckets[key].b += b;
            buckets[key].count += 1;
            buckets[key].sat += saturation;
        }

        let best = null;
        Object.keys(buckets).forEach(function (key) {
            let bucket = buckets[key];
            let score = bucket.count * (bucket.sat / bucket.count);
            if (!best || score > best.score) {
                best = {
                    score: score,
                    r: Math.round(bucket.r / bucket.count),
                    g: Math.round(bucket.g / bucket.count),
                    b: Math.round(bucket.b / bucket.count)
                };
            }
        });

        return best ? rgbToHex(best.r, best.g, best.b) : null;
    }

    function applyBrandColors(primaryHex) {
        if (!primaryHex) return;

        let dark = darkenHex(primaryHex, 0.22);
        let rgb = hexToRgb(primaryHex);

        document.documentElement.style.setProperty('--brand-primary', primaryHex);
        document.documentElement.style.setProperty('--brand-dark', dark);
        document.documentElement.style.setProperty('--brand-light', 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',0.08)');
        document.documentElement.style.setProperty('--brand-border', 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',0.22)');
    }

    function triggerPrint() {
        @if($autoPrint ?? false)
        setTimeout(function () {
            window.print();
        }, 350);
        @endif
    }

    function initPrintTheme() {
        let logo = document.getElementById('schoolLogo');

        if (!logo) {
            triggerPrint();
            return;
        }

        function processLogo() {
            let color = extractDominantColor(logo);
            if (color) {
                applyBrandColors(color);
            }
            triggerPrint();
        }

        if (logo.complete && logo.naturalWidth > 0) {
            processLogo();
        } else {
            logo.onload = processLogo;
            logo.onerror = triggerPrint;
        }
    }

    window.onload = initPrintTheme;
</script>
