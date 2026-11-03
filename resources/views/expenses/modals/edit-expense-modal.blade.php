<div class="edit-sidebar bg-white position-fixed end-0 top-0 h-100vh overflow-y-auto z-99 max-w-700-px w-100 translate-x-full duration-300 active-translate-0">
    <div class="px-20 py-12 border-bottom d-flex align-items-center justify-content-between gap-20">
        <h5 class="text-lg mb-0">Edit Expense</h5>
        <button type="button" class="close-edit-sidebar text-danger-600 text-lg d-flex"><i class="ri-close-large-line"></i></button>
    </div>
    <form method="POST" action="{{ route('update-expense-process') }}" class="d-flex flex-column p-20">
        @csrf
        <input type="hidden" name="expense_id" id="edit_expense_id">
        <div class="row g-3">
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Date</label>
                <input type="date" class="form-control" name="expense_date" id="edit_expense_date" required>
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Amount (GHS)</label>
                <input type="number" class="form-control" name="amount" id="edit_expense_amount" min="0.01" step="0.01" required>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Category</label>
                <select class="form-control form-select" name="expense_category_id" id="edit_expense_category_id" required>
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @disabled($category->status !== 'Active')>{{ $category->name }}{{ $category->status !== 'Active' ? ' (inactive)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Paid to</label>
                <input type="text" class="form-control" name="payee" id="edit_expense_payee" required>
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Payment method</label>
                <select class="form-control form-select" name="payment_method" id="edit_expense_payment_method" required>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Reference</label>
                <input type="text" class="form-control" name="reference" id="edit_expense_reference">
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Academic year</label>
                <select class="form-control form-select" name="academic_year_id" id="edit_expense_year_id">
                    <option value="">None</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-12">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Notes</label>
                <textarea class="form-control" name="notes" id="edit_expense_notes" rows="3"></textarea>
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <button type="button" class="close-edit-sidebar border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">Cancel</button>
                    <button type="submit" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8">Update Expense</button>
                </div>
            </div>
        </div>
    </form>
</div>
