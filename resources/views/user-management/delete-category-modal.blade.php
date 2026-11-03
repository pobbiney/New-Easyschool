<!-- Modal Delete Category start -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered max-w-340-px">
        <div class="modal-content radius-16 bg-base">
            <form method="POST" action="{{ route('delete-user-category-process') }}">
                @csrf
                <input type="hidden" name="cat_id" id="delete_cat_id">
                <div class="modal-body pt-32 px-36 pb-24 text-center">
                    <span class="mb-16 fs-1 line-height-1 text-danger">
                        <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                    </span>
                    <h6 class="text-lg fw-semibold text-primary-light mb-8">Delete User Category</h6>
                    <p class="text-sm text-secondary-light mb-0">Are you sure you want to delete <strong id="delete_cat_name"></strong>?</p>
                    <div class="d-flex align-items-center justify-content-center gap-3 mt-24">
                        <button type="button"
                            class="flex-grow-1 border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-24 py-11 radius-8"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-grow-1 btn btn-primary-600 border border-primary-600 text-md px-16 py-12 radius-8">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Delete Category end -->
