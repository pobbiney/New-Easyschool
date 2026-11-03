<div class="edit-sidebar bg-white position-fixed end-0 top-0 h-100vh overflow-y-auto z-99 max-w-700-px w-100 translate-x-full duration-300 active-translate-0">
    <div class="px-20 py-12 border-bottom d-flex align-items-center justify-content-between gap-20">
        <h5 class="text-lg mb-0">Edit Product</h5>
        <button type="button" class="close-edit-sidebar text-danger-600 text-lg d-flex"><i class="ri-close-large-line"></i></button>
    </div>
    <form method="POST" action="{{ route('update-pos-product-process') }}" enctype="multipart/form-data" class="d-flex flex-column p-20">
        @csrf
        <input type="hidden" name="product_id" id="edit_pos_product_id">
        <div class="row g-3">
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Category</label>
                <select class="form-control form-select" name="pos_category_id" id="edit_pos_product_category_id">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Product Name</label>
                <input type="text" class="form-control" name="name" id="edit_pos_product_name">
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">SKU</label>
                <input type="text" class="form-control" name="sku" id="edit_pos_product_sku">
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Selling Price</label>
                <input type="number" step="0.01" min="0" class="form-control" name="price" id="edit_pos_product_price">
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Cost Price</label>
                <input type="number" step="0.01" min="0" class="form-control" name="cost_price" id="edit_pos_product_cost_price">
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Stock Quantity</label>
                <input type="number" min="0" class="form-control" name="stock_qty" id="edit_pos_product_stock_qty">
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Low Stock Threshold</label>
                <input type="number" min="0" class="form-control" name="low_stock_threshold" id="edit_pos_product_low_stock_threshold">
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                <select class="form-control form-select" name="status" id="edit_pos_product_status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Product Image (optional)</label>
                <input type="file" class="form-control" name="image" accept="image/jpeg,image/png,image/webp">
                <small class="text-secondary-light d-block mt-6">Upload a new image to replace the current one.</small>
                <img id="edit_pos_product_image_preview" src="" alt="" class="mt-10 radius-8" style="max-width:120px;display:none;">
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Description</label>
                <textarea class="form-control" name="description" id="edit_pos_product_description" rows="3"></textarea>
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <button type="button" class="close-edit-sidebar border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">Cancel</button>
                    <button type="submit" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8">Update Product</button>
                </div>
            </div>
        </div>
    </form>
</div>
