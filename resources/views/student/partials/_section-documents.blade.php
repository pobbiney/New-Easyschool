<div class="row gy-3">
    <div class="col-sm-4">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Document Name</label>
        <input type="text" id="docName" class="form-control" placeholder="e.g. Birth Certificate">
    </div>
    <div class="col-sm-4">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Choose File</label>
        <input type="file" id="documentFile" class="form-control">
    </div>
    <div class="col-sm-4 d-flex align-items-end">
        <button type="button" id="addDocument" class="btn btn-outline-primary-600 w-100">Add Document</button>
    </div>
</div>
<div class="table-responsive mt-16">
    <table class="table bordered-table mb-0" id="documentTable">
        <thead>
            <tr>
                <th>Document Name</th>
                <th>File</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<div id="documentContainer"></div>
