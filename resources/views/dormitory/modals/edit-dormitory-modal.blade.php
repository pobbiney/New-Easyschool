<div class="edit-sidebar edit-dormitory-sidebar bg-white position-fixed end-0 top-0 h-100vh overflow-y-auto z-99 max-w-700-px w-100 translate-x-full duration-300 active-translate-0">
    <div class="px-20 py-12 border-bottom d-flex align-items-center justify-content-between gap-20">
        <h5 class="text-lg mb-0">Edit Dormitory</h5>
        <button type="button" class="close-edit-sidebar text-danger-600 text-lg d-flex">
            <i class="ri-close-large-line"></i>
        </button>
    </div>
    <form method="POST" action="{{ route('update-dormitory-process') }}" class="d-flex flex-column p-20">
        @csrf
        <input type="hidden" name="dormitory_id" id="edit_dormitory_id">
        <div class="row g-3">
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">House</label>
                <select class="form-control form-select" name="house_id" id="edit_dormitory_house_id" required>
                    @foreach($houses as $house)
                        <option value="{{ $house->id }}">{{ $house->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Dormitory Name</label>
                <input type="text" class="form-control" name="name" id="edit_dormitory_name">
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Number of Beds</label>
                <input type="number" class="form-control" name="bed_count" id="edit_dormitory_bed_count" min="1" max="100">
                <small class="text-secondary-light">Cannot reduce below occupied beds.</small>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                <select class="form-control form-select" name="status" id="edit_dormitory_status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <button type="button" class="close-edit-sidebar border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">Cancel</button>
                    <button type="submit" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8">Update Dormitory</button>
                </div>
            </div>
        </div>
    </form>
</div>
