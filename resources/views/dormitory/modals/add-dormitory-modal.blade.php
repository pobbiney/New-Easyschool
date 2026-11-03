<div class="my-sidebar dormitory-sidebar bg-white position-fixed end-0 top-0 h-100vh overflow-y-auto z-99 max-w-700-px w-100 translate-x-full duration-300 active-translate-0">
    <div class="px-20 py-12 border-bottom d-flex align-items-center justify-content-between gap-20">
        <h5 class="text-lg mb-0">Add Dormitory</h5>
        <button type="button" class="close-my-sidebar text-danger-600 text-lg d-flex">
            <i class="ri-close-large-line"></i>
        </button>
    </div>
    <form method="POST" action="{{ route('add-dormitory-process') }}" class="d-flex flex-column p-20">
        @csrf
        <div class="row g-3">
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">House</label>
                <select class="form-control form-select" name="house_id" required>
                    <option value="">Select house</option>
                    @foreach($houses as $house)
                        <option value="{{ $house->id }}" {{ old('house_id') == $house->id ? 'selected' : '' }}>{{ $house->name }}</option>
                    @endforeach
                </select>
                @error('house_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Dormitory Name</label>
                <input type="text" class="form-control" name="name" placeholder="e.g. Room A1" value="{{ old('name') }}">
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Number of Beds</label>
                <input type="number" class="form-control" name="bed_count" min="1" max="100" placeholder="e.g. 4" value="{{ old('bed_count', 1) }}">
                <small class="text-secondary-light">Beds will be created automatically for student assignment.</small>
                @error('bed_count') <small class="text-danger d-block">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                <select class="form-control form-select" name="status">
                    <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <button type="button" class="close-my-sidebar border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">Cancel</button>
                    <button type="submit" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8">Save Dormitory</button>
                </div>
            </div>
        </div>
    </form>
</div>
