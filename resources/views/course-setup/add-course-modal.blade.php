<!-- Add sidebar start -->
<div
    class="add-course-sidebar my-sidebar bg-white position-fixed end-0 top-0 h-100vh overflow-y-auto z-99 max-w-700-px w-100 translate-x-full duration-300 active-translate-0">
    <div class="px-20 py-12 border-bottom d-flex align-items-center justify-content-between gap-20">
        <h5 class="text-lg mb-0">Add New Course</h5>
        <button type="button" class="close-my-sidebar text-danger-600 text-lg d-flex">
            <i class="ri-close-large-line"></i>
        </button>
    </div>
    <form enctype="multipart/form-data" method="POST" action="{{ route('add-course-process') }}" class="d-flex flex-column p-20">
        @csrf
        <div class="row g-3">
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Parent Course <span class="text-secondary-light fw-normal">(optional)</span></label>
                <select class="form-control form-select" name="parent_id" id="add_course_parent_id">
                    <option value="">Top-level course</option>
                    @foreach($parentCourses as $parentCourse)
                        <option value="{{ $parentCourse->id }}" {{ (string) old('parent_id') === (string) $parentCourse->id ? 'selected' : '' }}>
                            {{ $parentCourse->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-secondary-light d-block mt-4">Leave blank to create a main course. Select a parent to create a sub-course.</small>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Course Name</label>
                <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Enter course name">
                @error('name') <small class="text-danger-600">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Course Category</label>
                <select class="form-control form-select" name="course_category" id="add_course_category">
                    <option value="" disabled {{ old('course_category') ? '' : 'selected' }}>Choose category</option>
                    <option value="Subject" {{ old('course_category') == 'Subject' ? 'selected' : '' }}>Subject</option>
                    <option value="4RS" {{ old('course_category') == '4RS' ? 'selected' : '' }}>4RS</option>
                </select>
                @error('course_category') <small class="text-danger-600">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                <select class="form-control form-select" name="status">
                    <option value="" disabled {{ old('status') ? '' : 'selected' }}>Choose status</option>
                    <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <small class="text-danger-600">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Description</label>
                <textarea class="form-control" name="description" placeholder="Enter course description">{{ old('description') }}</textarea>
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <button type="button" class="close-my-sidebar border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8 max-w-156-px w-100">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- Add sidebar end -->
