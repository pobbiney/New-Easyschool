@php
    $moduleIcons = [
        'Staff Management' => 'ri-team-line',
        'HR' => 'ri-id-card-line',
        'Student Management' => 'ri-graduation-cap-line',
        'Course Setup' => 'ri-book-open-line',
        'User Management' => 'ri-shield-user-line',
        'Settings' => 'ri-settings-3-line',
        'Dormitory' => 'ri-hotel-bed-line',
        'Class Setup' => 'ri-layout-grid-line',
    ];

    $screenIcons = [
        'Staff' => 'ri-user-add-line',
        'Employee' => 'ri-user-add-line',
        'HR' => 'ri-id-card-line',
        'Leave' => 'ri-calendar-event-line',
        'Payroll' => 'ri-money-cny-box-line',
        'Payslip' => 'ri-file-text-line',
        'Attendance' => 'ri-calendar-check-line',
        'Student' => 'ri-user-follow-line',
        'Course' => 'ri-book-2-line',
        'Category' => 'ri-shield-user-line',
        'Academic' => 'ri-calendar-line',
        'Class' => 'ri-layout-grid-line',
        'School' => 'ri-building-line',
        'Dormitory' => 'ri-hotel-bed-line',
        'List' => 'ri-list-check',
    ];

    $resolveScreenIcon = function ($linkName) use ($screenIcons) {
        foreach ($screenIcons as $keyword => $icon) {
            if (stripos($linkName, $keyword) !== false) {
                return $icon;
            }
        }

        return 'ri-window-line';
    };

    $savedAccessLinks = $savedAccessLinks ?? [];
    $savedAccessLinkIds = array_map('intval', old('extra_link_ids', $assignedExtraLinkIds ?? []));
    $inheritedPreviewLinks = $savedAccessLinks;

    if (old('extra_link_ids') && isset($childLinks)) {
        $previewLinkIds = array_map('intval', old('extra_link_ids'));
        $inheritedPreviewLinks = $childLinks
            ->whereIn('link_id', $previewLinkIds)
            ->sortBy('link_name')
            ->map(function ($link) {
                return [
                    'link_id' => (int) $link->link_id,
                    'link_name' => $link->link_name,
                ];
            })
            ->values()
            ->all();
    }
@endphp

@include('staff.partials._extra-screens-styles')

<div class="staff-access-card shadow-1 radius-12 bg-base overflow-hidden mt-24" id="system-access-section"
    data-saved-access-link-ids='@json($savedAccessLinkIds)'
    data-saved-access-links='@json($inheritedPreviewLinks)'>
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-12">
            <span class="section-title-icon"><i class="ri-shield-keyhole-line"></i></span>
            <div>
                <h6 class="text-lg fw-semibold mb-4 section-card-title">System Access</h6>
                <p class="text-sm text-secondary-light mb-0">Turn on login, then choose a category and any extra screens.</p>
            </div>
        </div>
        <div class="form-switch switch-primary d-flex align-items-center gap-10">
            <input class="form-check-input" type="checkbox" role="switch" name="enable_system_access" value="1"
                id="enable_system_access"
                @if(old('enable_system_access', !empty($staffUser))) checked @endif>
            <label class="form-check-label line-height-1 fw-medium text-secondary-light" for="enable_system_access">
                Enable login
            </label>
        </div>
    </div>

    <div class="card-body p-20 system-access-fields" id="system-access-fields">
        <div class="row gy-3">
            <div class="col-md-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">User Category</label>
                <select class="form-control form-select" name="user_cat" id="staff_user_cat">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->cat_id }}"
                            {{ (string) old('user_cat', $staffUser->user_cat ?? '') === (string) $category->cat_id ? 'selected' : '' }}>
                            {{ $category->cat_name }}
                        </option>
                    @endforeach
                </select>
                @error('user_cat') <small class="text-danger-600">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Login Password</label>
                <input type="password" name="password" id="staff_login_password" class="form-control"
                    placeholder="{{ !empty($staffUser) ? 'Leave blank to keep current password' : 'Minimum 8 characters' }}">
                @error('password') <small class="text-danger-600">{{ $message }}</small> @enderror
                <small class="text-secondary-light d-block mt-4 system-access-hint">
                    @if(!empty($staffUser))
                        Only fill this in if you want to change the password.
                    @else
                        Required when system login is enabled.
                    @endif
                </small>
            </div>

            <div class="col-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Inherited Screens</label>
                <div id="inherited-screens-preview" class="inherited-screens-box border radius-8 p-16">
                    @if(count($inheritedPreviewLinks))
                        @foreach($inheritedPreviewLinks as $link)
                            <span class="screen-badge">{{ $link['link_name'] }}</span>
                        @endforeach
                    @else
                        <span class="text-sm text-secondary-light">Select a user category to preview inherited screens.</span>
                    @endif
                </div>
            </div>

            <div class="col-12">
                <div class="extra-screens-panel">
                    <div class="extra-screens-toolbar">
                        <div>
                            <label class="text-sm fw-semibold extra-screens-heading d-inline-block mb-4">Extra Screens</label>
                            <p class="text-sm text-secondary-light mb-0">Select additional pages this user can access.</p>
                        </div>
                        <span class="extra-screens-count" id="extra-screens-count">0 selected</span>
                    </div>

                    <div class="extra-screens-list">
                    @foreach($parentLinks as $parent)
                        @php
                            $moduleChildren = $childLinks->where('link_parent', $parent->link_id);
                        @endphp

                        @if($moduleChildren->count())
                            <div class="extra-module-group" data-module-id="{{ $parent->link_id }}">
                                <div class="extra-module-header">
                                    <span class="extra-module-icon">
                                        <i class="{{ $moduleIcons[$parent->link_name] ?? 'ri-apps-2-line' }}"></i>
                                    </span>
                                    <div class="extra-module-heading">
                                        <h6 class="extra-module-title">{{ $parent->link_name }}</h6>
                                        <span class="extra-module-meta">{{ $moduleChildren->count() }}</span>
                                    </div>
                                </div>

                                <div class="extra-screen-grid">
                                    @foreach($moduleChildren as $child)
                                        @php
                                            $isChecked = in_array((int) $child->link_id, $savedAccessLinkIds, true);
                                        @endphp
                                        <label class="extra-screen-tile {{ $isChecked ? 'is-selected' : '' }}"
                                            for="extra_link_{{ $child->link_id }}"
                                            data-link-id="{{ $child->link_id }}">
                                            <input class="extra-link-checkbox" type="checkbox"
                                                name="extra_link_ids[]" value="{{ $child->link_id }}"
                                                id="extra_link_{{ $child->link_id }}"
                                                @if($isChecked) checked @endif>
                                            <span class="extra-screen-tile-body">
                                                <span class="extra-screen-check"><i class="ri-check-line"></i></span>
                                                <span class="extra-screen-icon">
                                                    <i class="{{ $resolveScreenIcon($child->link_name) }}"></i>
                                                </span>
                                                <span class="extra-screen-copy">
                                                    <span class="extra-screen-name">{{ $child->link_name }}</span>
                                                    <span class="extra-screen-note">Already included in the selected category</span>
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
