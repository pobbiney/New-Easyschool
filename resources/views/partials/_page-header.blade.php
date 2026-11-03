{{--
    Optional reusable page header.

    @include('partials._page-header', [
        'section' => 'Settings',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Settings', 'url' => null],
            ['label' => 'Promotion Settings', 'url' => null, 'active' => true],
        ],
        'title' => 'Promotion Settings',
        'subtitle' => 'Optional description',
        'actions' => '<a href="..." class="btn btn-primary-600">Save</a>',
    ])
--}}
@php
    $section = $section ?? null;
    $crumbs = $crumbs ?? [];
    $title = $title ?? null;
    $subtitle = $subtitle ?? null;
    $actions = $actions ?? null;

    if (! $title && ! empty($crumbs)) {
        $lastCrumb = end($crumbs);
        if (! empty($lastCrumb['active']) || empty($lastCrumb['url'])) {
            $title = $lastCrumb['label'] ?? null;
        }
    }

    $trailCrumbs = $crumbs;
    if ($title && count($trailCrumbs) > 0) {
        $last = end($trailCrumbs);
        if (($last['label'] ?? '') === $title) {
            array_pop($trailCrumbs);
        }
    }
@endphp

<div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24" data-ph-enhanced="1">
    <div>
        @if($section)
            <h1 class="fw-semibold mb-4 h6 text-primary-light">{{ strtoupper($section) }}</h1>
        @endif

        @if($title)
            <h2 class="ph-page-title">{{ $title }}</h2>
        @endif

        @if(! empty($trailCrumbs))
            <div class="ph-trail" data-ph-enhanced="1">
                @foreach($trailCrumbs as $crumb)
                    @if(! $loop->first)
                        <span class="ph-sep"><i class="ri-arrow-right-s-line"></i></span>
                    @endif
                    @if(! empty($crumb['url']) && empty($crumb['active']))
                        <a href="{{ $crumb['url'] }}" class="hover-text-primary">{{ $crumb['label'] }}</a>
                    @elseif($loop->last)
                        <span class="ph-current">{{ $crumb['label'] }}</span>
                    @else
                        <span>{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </div>
        @endif

        @if($subtitle)
            <p class="text-neutral-600 mt-4 mb-0">{{ $subtitle }}</p>
        @endif
    </div>

    @if(! empty($actions))
        <div class="page-header__actions">
            {!! $actions !!}
        </div>
    @endif
</div>
