<div class="my-sidebar bg-white position-fixed end-0 top-0 h-100vh overflow-y-auto z-99 max-w-700-px w-100 translate-x-full duration-300 active-translate-0">
    <div class="px-20 py-12 border-bottom d-flex align-items-center justify-content-between gap-20">
        <h5 class="text-lg mb-0">Add Product</h5>
        <button type="button" class="close-my-sidebar text-danger-600 text-lg d-flex"><i class="ri-close-large-line"></i></button>
    </div>
    <form method="POST" action="{{ route('add-pos-product-process') }}" enctype="multipart/form-data" class="d-flex flex-column p-20">
        @csrf
        <div class="row g-3">
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Category</label>
                <select class="form-control form-select" name="pos_category_id" required>
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('pos_category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('pos_category_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Product Name</label>
                <input type="text" class="form-control" name="name" placeholder="e.g. JHS Uniform Shirt" value="{{ old('name') }}">
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">SKU</label>
                <input type="text" class="form-control" name="sku" placeholder="Auto-generated if blank" value="{{ old('sku') }}">
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Selling Price</label>
                <input type="number" step="0.01" min="0" class="form-control" name="price" value="{{ old('price', '0') }}">
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Cost Price</label>
                <input type="number" step="0.01" min="0" class="form-control" name="cost_price" value="{{ old('cost_price') }}">
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Stock Quantity</label>
                <input type="number" min="0" class="form-control" name="stock_qty" value="{{ old('stock_qty', '0') }}">
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Low Stock Threshold</label>
                <input type="number" min="0" class="form-control" name="low_stock_threshold" value="{{ old('low_stock_threshold', '5') }}">
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                <select class="form-control form-select" name="status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Product Image (optional)</label>
                <input type="file" class="form-control" name="image" accept="image/jpeg,image/png,image/webp">
                <small class="text-secondary-light d-block mt-6">Leave blank to use the default placeholder on the POS screen.</small>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Description</label>
                <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <button type="button" class="close-my-sidebar border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">Cancel</button>
                    <button type="submit" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8">Save Product</button>
                </div>
            </div>
        </div>
    </form>
</div>
