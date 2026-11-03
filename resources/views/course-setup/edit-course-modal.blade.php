<div class="edit-sidebar bg-white position-fixed end-0 top-0 h-100vh overflow-y-auto z-99 max-w-700-px w-100 translate-x-full duration-300 active-translate-0">
    <div class="px-20 py-12 border-bottom d-flex align-items-center justify-content-between gap-20">
        <h5 class="text-lg mb-0">Edit Course</h5>
        <button type="button" class="close-edit-sidebar text-danger-600 text-lg d-flex">
            <i class="ri-close-large-line"></i>
        </button>
    </div>
    <form enctype="multipart/form-data" method="POST" action="{{ route('update-course-process') }}" class="d-flex flex-column p-20">
        @csrf
        <div class="row g-3">
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Parent Course</label>
                <select class="form-control form-select" name="parent_id" id="edit_course_parent_id">
                    <option value="">Top-level course</option>
                    @foreach($parentCourses as $parentCourse)
                        <option value="{{ $parentCourse->id }}">{{ $parentCourse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Course Name</label>
                <input type="text" class="form-control" id="coursename" name="name" placeholder="Enter course name">
                @error('name') <small class="text-danger-600">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Course Category</label>
                <select class="form-control form-select" name="course_category" id="coursecat">
                    <option value="" disabled>Choose category</option>
                    <option value="Subject">Subject</option>
                    <option value="4RS">4RS</option>
                </select>
                @error('course_category') <small class="text-danger-600">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                <select class="form-control form-select" name="status" id="coursestats">
                    <option value="" disabled>Choose status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                @error('status') <small class="text-danger-600">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Description</label>
                <textarea class="form-control" name="description" id="coursedesc" placeholder="Enter course description"></textarea>
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <button type="button" class="close-edit-sidebar border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8 max-w-156-px w-100">
                        Update
                    </button>
                </div>
            </div>
        </div>
        <input type="hidden" name="course_id" id="CourseID">
    </form>
</div>
