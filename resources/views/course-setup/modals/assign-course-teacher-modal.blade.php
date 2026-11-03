<div class="modal fade" id="assignCourseTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content radius-12 border-0">
            <div class="modal-header border-bottom px-24 py-16">
                <div>
                    <h6 class="modal-title fw-semibold mb-4">Assign Course Teacher</h6>
                    <p class="text-sm text-secondary-light mb-0">Assign one teacher per class for <strong id="assign_course_name">this course</strong>. Use different classes to assign multiple teachers.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('assign-course-teacher-process') }}" id="assignCourseTeacherForm">
                @csrf
                <input type="hidden" name="course_id" id="assign_course_id">
                <div class="modal-body p-24">
                    <div class="assign-course-preview mb-20">
                        <span class="assign-course-preview-icon"><i class="ri-book-open-line"></i></span>
                        <div>
                            <span class="text-xs text-secondary-light d-block mb-4">Course</span>
                            <span class="fw-semibold text-primary-600" id="assign_course_name_inline">—</span>
                            <span class="d-block text-xs text-secondary-light mt-4" id="assign_course_type_label"></span>
                        </div>
                    </div>

                    <div class="mb-16">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Class</label>
                        <select class="form-control form-select" name="school_class_id" id="assign_school_class_id" required>
                            <option value="">Select class</option>
                            @foreach($schoolClasses as $schoolClass)
                                <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-16">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Teacher</label>
                        <select class="form-control form-select" name="staff_id" id="assign_staff_id" required>
                            <option value="">Select teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-secondary-light d-block mt-8">One teacher per class. To assign another teacher, choose a different class.</small>
                    </div>

                    <div class="assigned-teachers-panel" id="assignedTeachersPanel">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-12">
                            <h6 class="text-sm fw-semibold mb-0">Assigned Teachers</h6>
                            <span class="text-xs text-secondary-light" id="assignedTeachersClassLabel">Select a class</span>
                        </div>
                        <div id="assignedTeachersList" class="assigned-teachers-list">
                            <p class="text-sm text-secondary-light mb-0">Assigned teachers will appear here.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-24 py-16">
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-outline-neutral-400" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-600" id="assignCourseTeacherSubmitBtn">Assign Teacher</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="viewCourseTeachersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content radius-12 border-0 view-teachers-modal">
            <div class="view-teachers-header">
                <span class="view-teachers-header-icon"><i class="ri-team-line"></i></span>
                <div class="view-teachers-header-content">
                    <h6 class="modal-title fw-semibold">Assigned Teachers</h6>
                    <p class="text-sm text-secondary-light mb-0">
                        <strong id="view_teachers_course_name" class="text-primary-600">—</strong>
                    </p>
                    <span class="view-teachers-count-pill">
                        <i class="ri-user-follow-line"></i>
                        <span id="viewTeachersCount">0</span> assigned
                    </span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body view-teachers-body p-0">
                <div id="viewTeachersList" class="view-teachers-list"></div>
            </div>
            <div class="view-teachers-footer">
                <button type="button" class="btn btn-pill btn-pill-neutral" data-bs-dismiss="modal">
                    <i class="ri-close-line"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('unassign-course-teacher-process') }}" id="unassignCourseTeacherForm" class="d-none">
    @csrf
    <input type="hidden" name="assignment_id" id="unassign_assignment_id">
</form>
