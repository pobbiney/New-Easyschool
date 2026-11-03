@php
    $courseLabel = $isSubCourse ? 'Sub-Course' : 'Course';
    $initials = strtoupper(substr($course->name, 0, 2));
    $teacherCount = $course->teachingAssignments->count();
    $courseUrl = route('get-course-teacher-assignment', $course->id);
@endphp

<tr class="course-group-row"
    data-course-id="{{ $course->id }}"
    data-course-url="{{ $courseUrl }}"
    data-course-name="{{ $course->name }}"
    data-course-type="{{ $courseLabel }}">
    <td>
        <div class="course-name-cell">
            <span class="course-avatar">{{ $initials }}</span>
            <div>
                <span class="fw-semibold text-primary-600">{{ $course->name }}</span>
                @if($isSubCourse && $course->parent)
                    <span class="parent-hint">Under: {{ $course->parent->name }}</span>
                @endif
            </div>
        </div>
    </td>
    <td><span class="type-badge">{{ $courseLabel }}</span></td>
    <td class="course-teachers-cell">
        @if($teacherCount > 0)
            <button type="button"
                class="teacher-count-pill view-course-teachers-btn is-active"
                data-course-id="{{ $course->id }}"
                data-course-name="{{ $course->name }}"
                data-url="{{ $courseUrl }}">
                {{ $teacherCount }}
            </button>
        @else
            <span class="teacher-count-pill is-empty">0</span>
        @endif
    </td>
    <td>
        <button type="button" class="btn btn-sm btn-outline-primary-600 assign-course-teacher-btn"
            data-course-id="{{ $course->id }}"
            data-url="{{ $courseUrl }}">
            <i class="ri-user-shared-line"></i> Assign Teacher
        </button>
    </td>
</tr>
