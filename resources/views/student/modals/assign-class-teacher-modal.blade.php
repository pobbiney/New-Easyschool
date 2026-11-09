<div class="modal fade" id="assignClassTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content radius-12 border-0">
            <div class="modal-header border-bottom px-24 py-16">
                <div>
                    <h6 class="modal-title fw-semibold mb-4">Assign Class Teachers</h6>
                    <p class="text-sm text-secondary-light mb-0">Add teachers for <strong id="assign_class_name">this class</strong>. Every assigned teacher gets full class-teacher access.</p>
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

                    <div class="mb-16" id="currentTeachersBox" style="display:none;">
                        <small class="text-secondary-light d-block mb-8">Current teachers</small>
                        <div id="currentTeachersList" class="d-flex flex-column gap-10"></div>
                    </div>

                    <div class="mb-8">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Add teacher</label>
                        <select class="form-control form-select" name="staff_id" id="assign_staff_id" required>
                            <option value="">Select teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->full_name }} — {{ $teacher->position }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-secondary-light d-block mt-8">Only staff with the Teacher user category are listed. A class can have more than one teacher.</small>
                    </div>
                </div>
                <div class="modal-footer border-top px-24 py-16 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-neutral-400" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-600">Add Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('unassign-class-teacher-process') }}" id="unassignClassTeacherForm" class="d-none">
    @csrf
    <input type="hidden" name="class_id" id="unassign_class_id">
    <input type="hidden" name="staff_id" id="unassign_staff_id">
</form>
