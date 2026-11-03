<!-- Edit sidebar start -->
<div
    class="edit-sidebar bg-white position-fixed end-0 top-0 h-100vh overflow-y-auto z-99 max-w-700-px w-100 translate-x-full duration-300 active-translate-0">
    <div class="px-20 py-12 border-bottom d-flex align-items-center justify-content-between gap-20">
        <h5 class="text-lg mb-0">STAFF EDUCATIONAL BACKGROUND </h5>
        <button type="button" class="close-edit-sidebar text-danger-600 text-lg d-flex">
            <i class="ri-close-large-line"></i>
        </button>
    </div>
    <form method="POST" enctype="multipart/form-data" action="{{ route('add-staff-document-process')}}"  class="d-flex flex-column p-20">
            @csrf
        <div class="row g-3">
            <div class="col-sm-6">
                <div class="">
                    <label for="bookNameEdit" class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Educational 
                        Level
                    </label>
                   <select class="form-control" name="level" id="level">
                        <option value="" selected disabled>Choose Level</option>
                        @foreach(\App\Models\StaffDoc::educationLevels() as $level)
                            <option value="{{ $level }}">{{ $level }}</option>
                        @endforeach
                   </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="">
                    <label for="publisherEdit"
                        class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Year of Completion
                    </label>
                       <input type="text" class="form-control" name="year" id="year" placeholder="Enter Year">
                </div>
            </div>
        </div>
        <div class="row g-3" style="margin-top: 10px;">
               <div class="col-sm-6">
                    <div class="">
                        <label for="authorEdit" class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Qualification
                        </label>
                        <input type="text" class="form-control" name="qualification" id="qualification" placeholder="Enter Qualification">
                    </div>
                </div>
               <div class="col-sm-6">
                    <div class="">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Institution
                        </label>
                        <input type="text" class="form-control" name="institution" id="institution" placeholder="Enter Institution">
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="">
                        <label for="numberEdit" class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Upload Certificate
                        </label>
                        <input type="file" class="form-control" name="document" id="document" placeholder="Upload Certificate">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="">
                         <br/>
                        <button type="button" class="btn btn-success" id="addDocument">Add</button>
                    </div>
                </div>
                <input type="hidden" name="staff_number" id="staffIDs" class="form-control"/>
        </div>
        <div class="row g-3" style="margin-top: 40px;">
            <table class="table bordered-table"   id="documentTable">
                <thead>
                    <tr>
                         
                        <th scope="col">Level</th>
                        <th scope="col">Year</th>
                        <th scope="col">Qualification</th>
                        <th scope="col">Institution</th>
                        <th scope="col">Certificate</th>
                        
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                   
                </tbody>
            </table>
             <div id="documentContainer"></div>
        </div>
         
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <button type="reset"
                        class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">
                        Cancel
                    </button>
                    <button type="submit"
                        class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8"  id="saveBtn" disabled>
                        Update
                    </button>
                </div>
            </div>
        </div>
        
    </form>
</div>
<!-- Edit sidebar end -->