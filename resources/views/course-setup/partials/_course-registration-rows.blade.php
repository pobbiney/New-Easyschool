@php
    $isRegistered = $course['is_registered'] ?? false;
    $registrationId = $course['registration_id'] ?? null;
    $courseId = $course['id'];
    $courseName = e($course['name']);
    $parentName = $course['parent_name'] ?? null;
    $category = e($course['category'] ?? '—');
    $isSubCourse = $course['is_sub_course'] ?? false;
@endphp
<tr class="course-registration-row" data-course-id="{{ $courseId }}">
    <td>
        <div class="course-name-cell">
            <span class="course-avatar"><i class="ri-book-open-line"></i></span>
            <div>
                <span class="fw-medium">{{ $courseName }}</span>
                @if($parentName)
                    <span class="parent-hint">{{ $parentName }}</span>
                @endif
            </div>
        </div>
    </td>
    <td>
        <span class="type-badge">{{ $isSubCourse ? 'Sub-Course' : 'Course' }}</span>
    </td>
    <td>
        <span class="category-badge">{{ $category }}</span>
    </td>
    <td class="registration-action-cell">
        @if($isRegistered)
            <button type="button"
                class="btn btn-pill btn-pill-danger course-registration-toggle-btn"
                data-action="remove"
                data-course-id="{{ $courseId }}"
                data-registration-id="{{ $registrationId }}">
                <i class="ri-subtract-line"></i>
                Remove
            </button>
        @else
            <button type="button"
                class="btn btn-pill btn-pill-primary course-registration-toggle-btn"
                data-action="add"
                data-course-id="{{ $courseId }}">
                <i class="ri-add-line"></i>
                Add
            </button>
        @endif
    </td>
</tr>
