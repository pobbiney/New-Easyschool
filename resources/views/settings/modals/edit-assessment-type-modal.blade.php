<div class="edit-sidebar bg-white position-fixed end-0 top-0 h-100vh overflow-y-auto z-99 max-w-700-px w-100 translate-x-full duration-300 active-translate-0">
    <div class="px-20 py-12 border-bottom d-flex align-items-center justify-content-between gap-20">
        <h5 class="text-lg mb-0">Edit Assessment Type</h5>
        <button type="button" class="close-edit-sidebar text-danger-600 text-lg d-flex">
            <i class="ri-close-large-line"></i>
        </button>
    </div>
    <form method="POST" action="{{ route('update-assessment-type-process') }}" class="d-flex flex-column p-20">
        @csrf
        <input type="hidden" name="assessment_type_id" id="edit_assessment_type_id">
        <div class="row g-3">
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Class Category</label>
                <select class="form-control form-select" name="class_category_id" id="edit_assessment_type_class_category_id" required>
                    @foreach($classCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <small class="text-secondary-light">Locked after the type is used on assessments.</small>
                @error('class_category_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Type Name</label>
                <input type="text" class="form-control" name="name" id="edit_assessment_type_name" placeholder="e.g. Quiz">
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Code</label>
                <input type="text" class="form-control" id="edit_assessment_type_slug" readonly disabled>
                <small class="text-secondary-light">Code is fixed after creation so existing assessments stay linked.</small>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Assessment Category</label>
                <select class="form-control form-select" name="category" id="edit_assessment_type_category" required>
                    @foreach(\App\Models\AssessmentType::categoryOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('category') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Max Number</label>
                <input type="number" class="form-control" name="max_number" id="edit_assessment_type_max_number" min="1" max="999">
                <small class="text-secondary-light">Maximum assessments of this type per class and subject this term.</small>
                @error('max_number') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Sort Order</label>
                <input type="number" class="form-control" name="sort_order" id="edit_assessment_type_sort_order" min="1" max="99">
                @error('sort_order') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                <select class="form-control form-select" name="status" id="edit_assessment_type_status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                @error('status') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <button type="reset" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">Cancel</button>
                    <button type="submit" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8">Update</button>
                </div>
            </div>
        </div>
    </form>
</div>
