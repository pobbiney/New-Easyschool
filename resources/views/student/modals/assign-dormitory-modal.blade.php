<div class="modal fade" id="assignDormitoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content radius-12 border-0">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-semibold">Assign Dormitory</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('assign-student-dormitory-process') }}" id="assignDormitoryForm">
                @csrf
                <input type="hidden" name="student_id" id="assign_student_id">
                <div class="modal-body p-24">
                    <p class="text-sm text-secondary-light mb-16">Assign <strong id="assign_student_name">student</strong> to a house, dormitory, and bed.</p>

                    <div class="mb-16">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">House</label>
                        <select class="form-control form-select" name="house_id" id="assign_house_id" required>
                            <option value="">Select house</option>
                            @foreach($houses as $house)
                                <option value="{{ $house->id }}">{{ $house->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-16">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Dormitory</label>
                        <select class="form-control form-select" name="dormitory_id" id="assign_dormitory_id" required disabled>
                            <option value="">Select dormitory</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Bed</label>
                        <select class="form-control form-select" name="bed_id" id="assign_bed_id" required disabled>
                            <option value="">Select bed</option>
                        </select>
                    </div>

                    <div class="mt-16 p-12 radius-8 bg-neutral-50" id="currentAssignmentBox" style="display:none;">
                        <small class="text-secondary-light d-block mb-4">Current assignment</small>
                        <span class="text-sm fw-medium" id="currentAssignmentText"></span>
                    </div>
                </div>
                <div class="modal-footer border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger-600" id="unassignDormitoryBtn" style="display:none;">Remove Assignment</button>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-outline-neutral-400" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-600">Save Assignment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('unassign-student-dormitory-process') }}" id="unassignDormitoryForm" class="d-none">
    @csrf
    <input type="hidden" name="student_id" id="unassign_student_id">
</form>
