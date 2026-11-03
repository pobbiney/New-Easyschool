@php
    $headerText = $settings->header_text ?? ($school->name ?? 'School name');
    $footerText = $settings->footer_text ?? 'This card remains the property of the school and must be produced on request.';
    $mottoText = $school->motto ?? '';
    $signatureUrl = $signatureUrl ?? null;
    $dark = $palette['dark'] ?? '#3B0A5C';
    $accent = $palette['accent'] ?? '#F5C518';
    $ink = $palette['ink'] ?? '#1A0B2E';
    $svgId = 'idc'.bin2hex(random_bytes(4));
@endphp
<div class="idcard-face">
    <div class="idcard-face-head" style="background-color: {{ $ink }};">
        <svg class="idcard-head-bg" viewBox="0 0 320 168" preserveAspectRatio="none" aria-hidden="true">
            <defs>
                <linearGradient id="{{ $svgId }}-g" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="{{ $dark }}"/>
                    <stop offset="58%" stop-color="{{ $ink }}"/>
                    <stop offset="100%" stop-color="#0b0614"/>
                </linearGradient>
                <pattern id="{{ $svgId }}-d" width="14" height="14" patternUnits="userSpaceOnUse">
                    <circle cx="11" cy="2.5" r="1.15" fill="{{ $accent }}" fill-opacity="0.55"/>
                </pattern>
                <pattern id="{{ $svgId }}-s" width="12" height="12" patternUnits="userSpaceOnUse" patternTransform="rotate(32)">
                    <rect width="1.2" height="12" fill="{{ $accent }}" fill-opacity="0.32"/>
                </pattern>
            </defs>
            <rect width="320" height="168" fill="url(#{{ $svgId }}-g)"/>
            <rect width="320" height="168" fill="url(#{{ $svgId }}-d)"/>
            <polygon points="198,0 320,0 320,168 153,168" fill="url(#{{ $svgId }}-s)"/>
        </svg>
        <div class="idcard-head-row">
            <div class="idcard-hex">
                <svg viewBox="0 0 96 106" aria-hidden="true">
                    <polygon fill="{{ $accent }}" points="48,1 94,26.5 94,79.5 48,105 2,79.5 2,26.5"/>
                    <polygon fill="#ffffff" points="48,5.5 89.5,28.5 89.5,77.5 48,100.5 6.5,77.5 6.5,28.5"/>
                </svg>
                <img src="{{ $logoUrl }}" alt="">
            </div>
            <div>
                <p class="idcard-school">{{ $headerText }}</p>
                <p class="idcard-tagline">{{ $cardTitle }}</p>
            </div>
        </div>
        <svg class="idcard-wave" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true">
            <polygon fill="#ffffff" points="0,29.4 121.6,11.8 320,26 320,42 0,42"/>
        </svg>
        <svg class="idcard-stripe" viewBox="0 0 320 10" preserveAspectRatio="none" aria-hidden="true">
            <polygon fill="{{ $accent }}" points="0,4 134.4,0 320,5.5 320,10 0,10"/>
        </svg>
    </div>

    @if ($person['show_photo'] ?? true)
        <div class="idcard-photo-wrap">
            <div class="idcard-photo">
                <img src="{{ $person['photo'] }}" alt="">
            </div>
        </div>
    @endif

    <div class="idcard-body">
        <p class="idcard-name">{{ $person['name'] }}</p>
        <p class="idcard-role">{{ $person['role'] }}</p>
        <div class="idcard-mid-mark">
            <img src="{{ $logoUrl }}" alt="">
        </div>
        <div class="idcard-meta">
            @foreach ($person['rows'] as $row)
                <div @class(['idcard-meta-lg' => ! empty($row['large'])])><b>{{ $row['label'] }}</b> : {{ $row['value'] }}</div>
            @endforeach
        </div>
        <div class="idcard-barcode">
            {!! $person['qr'] ?? '' !!}
        </div>
    </div>

    <svg class="idcard-foot" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true">
        <polygon fill="{{ $ink }}" points="0,30.2 320,11.8 320,42 0,42"/>
    </svg>
    <svg class="idcard-foot-accent" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true">
        <polygon fill="{{ $accent }}" points="0,37 185.6,20.2 320,29.4 320,42 0,42"/>
    </svg>
</div>

<div class="idcard-face is-back">
    <div class="idcard-face-head" style="background-color: {{ $ink }};">
        <svg class="idcard-head-bg" viewBox="0 0 320 168" preserveAspectRatio="none" aria-hidden="true">
            <defs>
                <linearGradient id="{{ $svgId }}-bg" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="{{ $dark }}"/>
                    <stop offset="58%" stop-color="{{ $ink }}"/>
                    <stop offset="100%" stop-color="#0b0614"/>
                </linearGradient>
                <pattern id="{{ $svgId }}-bd" width="14" height="14" patternUnits="userSpaceOnUse">
                    <circle cx="11" cy="2.5" r="1.15" fill="{{ $accent }}" fill-opacity="0.55"/>
                </pattern>
                <pattern id="{{ $svgId }}-bs" width="12" height="12" patternUnits="userSpaceOnUse" patternTransform="rotate(32)">
                    <rect width="1.2" height="12" fill="{{ $accent }}" fill-opacity="0.32"/>
                </pattern>
            </defs>
            <rect width="320" height="168" fill="url(#{{ $svgId }}-bg)"/>
            <rect width="320" height="168" fill="url(#{{ $svgId }}-bd)"/>
            <polygon points="198,0 320,0 320,168 153,168" fill="url(#{{ $svgId }}-bs)"/>
        </svg>
        <div class="idcard-head-row">
             
           
        </div>
        <svg class="idcard-wave" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true">
            <polygon fill="#ffffff" points="0,29.4 121.6,11.8 320,26 320,42 0,42"/>
        </svg>
        <svg class="idcard-stripe" viewBox="0 0 320 10" preserveAspectRatio="none" aria-hidden="true">
            <polygon fill="{{ $accent }}" points="0,4 134.4,0 320,5.5 320,10 0,10"/>
        </svg>
    </div>
    <div class="idcard-back-logo">
        <img src="{{ $logoUrl }}" alt="">
    </div>
    <div class="idcard-back-body">
        <p class="idcard-back-school">{{ $headerText }}</p>
        @if ($mottoText !== '')
            <p class="idcard-back-motto">{{ $mottoText }}</p>
        @endif
        <div class="idcard-back-block">
            <span class="idcard-back-kicker">If found, return to</span>
            @if (! empty($school->address))
                <div class="idcard-back-line"><i class="ri-map-pin-line"></i><span>{{ $school->address }}</span></div>
            @endif
            @if (! empty($school->phone))
                <div class="idcard-back-line"><i class="ri-phone-line"></i><span>{{ $school->phone }}</span></div>
            @endif
            @if (! empty($school->email))
                <div class="idcard-back-line"><i class="ri-mail-line"></i><span>{{ $school->email }}</span></div>
            @endif
            @if (empty($school->address) && empty($school->phone) && empty($school->email))
                <div class="idcard-back-line"><i class="ri-building-line"></i><span>School office</span></div>
            @endif
        </div>
        <p class="idcard-back-note">{{ $footerText }}</p>
        <div class="idcard-back-sign">
            @if ($signatureUrl)
                <img src="{{ $signatureUrl }}" alt="">
            @else
                <div class="line"></div>
            @endif<br/>
            <small>Authorised signature</small>
        </div>
    </div>
    <svg class="idcard-foot" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true">
        <polygon fill="{{ $ink }}" points="0,11.8 320,30.2 320,42 0,42"/>
    </svg>
    <svg class="idcard-foot-accent" viewBox="0 0 320 42" preserveAspectRatio="none" aria-hidden="true">
        <polygon fill="{{ $accent }}" points="0,29.4 134.4,20.2 320,37 320,42 0,42"/>
    </svg>
</div>
