<div class="modal fade" id="assignClassTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content radius-12 border-0">
            <div class="modal-header border-bottom px-24 py-16">
                <div>
                    <h6 class="modal-title fw-semibold mb-4">Assign Class Teacher</h6>
                    <p class="text-sm text-secondary-light mb-0">Choose one teacher for <strong id="assign_class_name">this class</strong>.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('assign-class-teacher-process') }}" id="assignClassTeacherForm">
                @csrf
                <input type="hidden" name="class_id" id="assign_class_id">
                <div class="modal-body p-24">
                    <div class="assign-class-preview mb-20">
                        <span class="assign-class-preview-icon"><i class="ri-book-open-line"></i></span>
                        <div>
                            <span class="text-xs text-secondary-light d-block mb-4">Class</span>
                            <span class="fw-semibold text-primary-600" id="assign_class_name_inline">—</span>
                        </div>
                    </div>

                    <div class="mb-16">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Teacher</label>
                        <select class="form-control form-select" name="staff_id" id="assign_staff_id" required>
                            <option value="">Select teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->full_name }} — {{ $teacher->position }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-secondary-light d-block mt-8">Only staff with the Teacher user category are listed. Each class can have one teacher.</small>
                    </div>

                    <div class="current-teacher-box" id="currentTeacherBox" style="display:none;">
                        <small class="text-secondary-light d-block mb-8">Current teacher</small>
                        <div class="d-flex align-items-center gap-12">
                            <span class="teacher-mini-avatar" id="currentTeacherAvatar">T</span>
                            <div>
                                <span class="d-block fw-semibold text-sm" id="currentTeacherName"></span>
                                <span class="d-block text-xs text-secondary-light" id="currentTeacherMeta"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-24 py-16 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger-600" id="unassignClassTeacherBtn" style="display:none;">
                        Remove Teacher
                    </button>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-outline-neutral-400" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-600">Save Assignment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('unassign-class-teacher-process') }}" id="unassignClassTeacherForm" class="d-none">
    @csrf
    <input type="hidden" name="class_id" id="unassign_class_id">
</form>
