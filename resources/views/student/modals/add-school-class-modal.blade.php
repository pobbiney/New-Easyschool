<div class="my-sidebar bg-white position-fixed end-0 top-0 h-100vh overflow-y-auto z-99 max-w-700-px w-100 translate-x-full duration-300 active-translate-0">
    <div class="px-20 py-12 border-bottom d-flex align-items-center justify-content-between gap-20">
        <h5 class="text-lg mb-0">Add Class</h5>
        <button type="button" class="close-my-sidebar text-danger-600 text-lg d-flex">
            <i class="ri-close-large-line"></i>
        </button>
    </div>
    <form method="POST" action="{{ route('add-school-class-process') }}" class="d-flex flex-column p-20">
        @csrf
        <div class="row g-3">
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Class Category</label>
                <select class="form-control form-select" name="class_category_id">
                    <option value="">Select category</option>
                    @foreach($activeClassCategories as $category)
                        <option value="{{ $category->id }}" {{ old('class_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('class_category_id') <small class="text-danger">{{ $message }}</small> @enderror
                @if($activeClassCategories->isEmpty())
                    <small class="text-warning-600 d-block mt-6">
                        <a href="{{ route('class-categories') }}">Add a class category</a> before creating classes.
                    </small>
                @endif
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Class Name</label>
                <input type="text" class="form-control" name="name" placeholder="e.g. Primary 1" value="{{ old('name') }}">
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                <select class="form-control form-select" name="status">
                    <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <button type="reset" class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">Cancel</button>
                    <button type="submit" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8" @if($activeClassCategories->isEmpty()) disabled @endif>Save Class</button>
                </div>
            </div>
        </div>
    </form>
</div>
