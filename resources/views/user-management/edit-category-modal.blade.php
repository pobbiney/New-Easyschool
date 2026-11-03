<!-- Edit sidebar start -->
<div class="edit-sidebar bg-white position-fixed end-0 top-0 h-100vh overflow-y-auto z-99 max-w-700-px w-100 translate-x-full duration-300 active-translate-0">
    <div class="px-20 py-12 border-bottom d-flex align-items-center justify-content-between gap-20">
        <h5 class="text-lg mb-0">Edit User Category</h5>
        <button type="button" class="close-edit-sidebar text-danger-600 text-lg d-flex">
            <i class="ri-close-large-line"></i>
        </button>
    </div>
    <form method="POST" action="{{ route('update-user-category-process') }}" class="d-flex flex-column p-20">
        @csrf
        <input type="hidden" name="cat_id" id="edit_cat_id">

        <div class="row g-3">
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Category Name</label>
                <input type="text" class="form-control" name="cat_name" id="edit_cat_name" placeholder="Enter category name">
                @error('cat_name') <small style="color:red;">{{ $message }}</small> @enderror
            </div>

            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                <select class="form-control form-select" name="status" id="edit_status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                @error('status') <small style="color:red;">{{ $message }}</small> @enderror
            </div>

            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Screen Access</label>
                <p class="text-sm text-secondary-light mb-12">Select which screens this category can access.</p>

                @foreach($parentLinks as $parent)
                    <div class="border radius-8 p-16 mb-12">
                        <h6 class="text-md fw-semibold mb-12">{{ $parent->link_name }}</h6>
                        @foreach($childLinks as $child)
                            @if($child->link_parent == $parent->link_id)
                                <div class="form-check style-check d-flex align-items-center mb-8">
                                    <input class="form-check-input edit-link-checkbox" type="checkbox"
                                        name="link_ids[]" value="{{ $child->link_id }}" id="edit_link_{{ $child->link_id }}">
                                    <label class="form-check-label" for="edit_link_{{ $child->link_id }}">
                                        {{ $child->link_name }}
                                    </label>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <button type="button"
                        class="close-edit-sidebar border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">
                        Cancel
                    </button>
                    <button type="submit"
                        class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8 max-w-156-px w-100">
                        Update
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- Edit sidebar end -->
