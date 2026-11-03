<!-- page title -->
@php $pageName = "hr"; $subpageName = "list-staff"; @endphp

@extends('layouts.app')

@section('css')
<style>
    .staff-list-dataTable-wrapper,
    .staff-list-dataTable-wrapper .dt-container,
    .staff-list-dataTable-wrapper .dt-layout-cell {
        overflow: visible !important;
    }

    .staff-list-table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .staff-list-dataTable-wrapper table.dataTable,
    .staff-list-table-scroll table {
        min-width: 1020px;
    }

    .staff-list-dataTable-wrapper .table-action-cell {
        position: relative !important;
    }

    .staff-list-dataTable-wrapper .table-action-cell .dropdown-menu {
        z-index: 1060;
    }

    @media (max-width: 767px) {
        .staff-list-dataTable-wrapper .dt-search {
            width: 100%;
        }

        .staff-list-dataTable-wrapper .dt-search .dt-input {
            width: 100%;
        }
    }
</style>
@endsection
 
@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div class="">
            <h1 class="fw-semibold mb-4 h6 text-primary-light">HR</h1>
            <div class="">
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Staff List</span>
            </div>
        </div>
        <a href="{{ route('add-staff') }}" class="btn btn-primary-600 d-flex align-items-center gap-6">
            <span class="d-flex text-md">
                <i class="ri-add-large-line"></i>
            </span>
            Add Employee
        </a>
    </div>
              
    <div class="mt-24">
        <div class="card h-100">
            <div class="card-body p-0 dataTable-wrapper staff-list-dataTable-wrapper">

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                    <div class="d-flex flex-wrap align-items-center gap-16">
                        <form class="navbar-search dt-search m-0">
                            <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable"
                                name="search" placeholder="Search...">
                            <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                        </form>
                    </div>
                    <div class="d-flex align-items-center gap-8 text-secondary-light">
                        <span>Rows per page:</span>
                        <div class="dt-length">
                            <select name="dataTable_length" aria-controls="dataTable"
                                class="dt-input form-control form-select">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="p-0 table-responsive staff-list-table-scroll">
                    <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length='10'>
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Staff ID</th>
                                <th scope="col">Staff Name</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Date of Birth</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Position</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liststaff as $list)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="text-primary-600 fw-semibold">{{ $list->employee_id }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(!empty($list->picture))
                                            <img src="{{ asset($list->picture) }}"
                                                alt="{{ $list->surname . ' ' . $list->firstname }}"
                                                class="flex-shrink-0 me-12 radius-8" width="44" height="44">
                                        @elseif(strtolower($list->gender) == 'male')
                                            <img src="{{ asset('assets/images/thumbs/guardian-img1.png') }}"
                                                alt="Male Staff"
                                                class="flex-shrink-0 me-12 radius-8" width="44" height="44">
                                        @else
                                            <img src="{{ asset('assets/images/thumbs/guardian-img2.png') }}"
                                                alt="Female Staff"
                                                class="flex-shrink-0 me-12 radius-8" width="44" height="44">
                                        @endif
                                        <div>
                                            <h6 class="text-md mb-0 fw-medium">{{ $list->surname . ' ' . $list->firstname }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $list->gender }}</td>
                                <td>{{ $list->dob }}</td>
                                <td>{{ $list->email }}</td>
                                <td>{{ $list->mobile }}</td>
                                <td>{{ $list->position }}</td>
                                <td>
                                    @if($list->status == 'Active')
                                        <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $list->status }}</span>
                                    @endif
                                </td>
                                <td class="table-action-cell">
                                    <div class="btn-group">
                                        <button type="button" class="text-primary-light text-xl"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <iconify-icon icon="tabler:dots-vertical"></iconify-icon>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg-end border p-12">
                                            <li>
                                                <a href="{{ route('view-staff-details', Crypt::encrypt($list->id)) }}"
                                                    class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
                                                    <i class="ri-user-3-line"></i>
                                                    View Staff
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('edit-staff', Crypt::encrypt($list->id)) }}"
                                                    class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
                                                    <i class="ri-edit-2-line"></i>
                                                    Edit
                                                </a>
                                            </li>
                                            <li>
                                                <button class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6 showmodal"
                                                    data-url="{{ route('staff-id', $list->id) }}"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModalDelete">
                                                    <i class="ri-delete-bin-6-line"></i>
                                                    Delete
                                                </button>
                                            </li>
                                            <li>
                                                <button class="edit-sidebar-btn dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6 showmodalupload"
                                                    data-url="{{ route('staff-id', $list->id) }}">
                                                    <i class="ri-upload-cloud-2-line"></i>
                                                    Upload Documents
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('staff.delete-staff-modal')
@include('staff.staff-document-modal')

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('body').on('click', '.showmodal', function () {
            var userUrl = $(this).data('url');

            $.get(userUrl, function (data) {
                $('#staffID').val(data.id);
                $('#staffsurname').text(data.surname);
                $('#stafffirstname').text(data.firstname);
            });
        });

        $('body').on('click', '.showmodalupload', function () {
            var userUrl = $(this).data('url');

            $.get(userUrl, function (data) {
                $('#staffIDs').val(data.id);
            });
        });
    });

    $('.edit-sidebar-btn').on('click', function () {
        $('.edit-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });
    $('.close-edit-sidebar, .overlay').on('click', function () {
        $('.edit-sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });

    let counter = 0;

    $('#addDocument').click(function () {
        let year = $('#year').val();
        let level = $('#level').val();
        let qualification = $('#qualification').val();
        let institution = $('#institution').val();
        let fileInput = $('#document')[0];
        let file = fileInput.files[0];

        if (!level) {
            alert('Select Level');
            return;
        }

        if (year == '') {
            alert('Enter Year');
            return;
        }

        if (qualification == '') {
            alert('Enter Qualification');
            return;
        }

        if (fileInput.files.length == 0) {
            alert('Choose a document to upload');
            return;
        }

        let fileName = file ? file.name : 'No file';

        let row = `
            <tr id="row_${counter}">
                <td>${level}</td>
                <td>${year}</td>
                <td>${qualification}</td>
                <td>${institution || '—'}</td>
                <td>${fileName}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm removeDoc" data-id="${counter}">
                        Remove
                    </button>
                </td>
            </tr>
        `;

        $('#documentTable tbody').append(row);

        $('#documentContainer').append(`
            <div id="doc_${counter}" style="display:none">
                <input type="hidden" name="documents[${counter}][level]" value="${level}">
                <input type="hidden" name="documents[${counter}][year]" value="${year}">
                <input type="hidden" name="documents[${counter}][qualification]" value="${qualification}">
                <input type="hidden" name="documents[${counter}][institution]" value="${institution}">
                <input type="file" name="documents[${counter}][document]" class="realFileInput">
            </div>
        `);

        if (file) {
            let realInput = $('#doc_' + counter + ' .realFileInput')[0];
            const dt = new DataTransfer();
            dt.items.add(file);
            realInput.files = dt.files;
        }

        $('#level').val('');
        $('#year').val('');
        $('#qualification').val('');
        $('#institution').val('');
        $('#document').val('');

        counter++;
        toggleSaveButton();
    });

    $(document).on('click', '.removeDoc', function () {
        let id = $(this).data('id');
        $('#row_' + id).remove();
        $('#doc_' + id).remove();
        toggleSaveButton();
    });

    function toggleSaveButton() {
        let rowCount = $('#documentTable tbody tr').length;
        $('#saveBtn').prop('disabled', rowCount === 0);
    }

    toggleSaveButton();

    document.querySelectorAll('.staff-list-dataTable-wrapper .table-action-cell [data-bs-toggle="dropdown"]').forEach(function (toggle) {
        bootstrap.Dropdown.getOrCreateInstance(toggle, {
            popperConfig: {
                strategy: 'fixed'
            }
        });
    });
</script>
@endsection
