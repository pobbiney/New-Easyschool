@php $pageName = "student"; $subpageName = "list-students"; @endphp

@extends('layouts.app')

@section('css')
<style>
    .student-list-dataTable-wrapper,
    .student-list-dataTable-wrapper .dt-container,
    .student-list-dataTable-wrapper .dt-layout-cell {
        overflow: visible !important;
    }

    .student-list-table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .student-list-dataTable-wrapper table.dataTable,
    .student-list-table-scroll table {
        min-width: 980px;
    }

    .student-list-dataTable-wrapper .table-action-cell {
        position: relative !important;
    }

    .student-list-dataTable-wrapper .table-action-cell .dropdown-menu {
        z-index: 1060;
    }

    @media (max-width: 767px) {
        .student-list-dataTable-wrapper .dt-search {
            width: 100%;
        }

        .student-list-dataTable-wrapper .dt-search .dt-input {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">STUDENT MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Student List</span>
            </div>
        </div>
        <a href="{{ route('add-student') }}" class="btn btn-primary-600 d-flex align-items-center gap-6">
            <span class="d-flex text-md"><i class="ri-add-large-line"></i></span>
            Register New Student
        </a>
    </div>

    <div class="mt-24">
        <div class="card h-100">
            <div class="card-body p-0 dataTable-wrapper student-list-dataTable-wrapper">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                    <div class="d-flex flex-wrap align-items-center gap-16">
                        <form class="navbar-search dt-search m-0">
                            <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search students...">
                            <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                        </form>
                        <div class="dropdown" id="studentFilterDropdown">
                            <button type="button"
                                class="px-12 py-5-px border border-neutral-300 radius-8 d-flex align-items-center gap-20"
                                data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <span class="d-flex align-items-center gap-1 text-secondary-light text-sm">
                                    <i class="ri-filter-3-line text-md line-height-1"></i>
                                    Filter
                                </span>
                                <span>
                                    <i class="ri-arrow-down-s-line"></i>
                                </span>
                            </button>
                            <div class="dropdown-menu border bg-base shadow dropdown-menu-lg p-0">
                                <div class="d-flex align-items-center justify-content-between border-bottom py-8 px-16">
                                    <span class="fw-semibold text-lg text-primary-light">Filter</span>
                                    <button type="button" class="btn btn-sm p-0 border-0 bg-transparent text-secondary-light" id="closeStudentFilterDropdown">
                                        <i class="ri-close-large-line"></i>
                                    </button>
                                </div>
                                <form action="#" class="p-16 d-grid grid-cols-2 gap-16" id="studentFilterForm">
                                    <div>
                                        <label for="filter_class" class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Class</label>
                                        <select id="filter_class" class="form-control form-select">
                                            <option value="">All Classes</option>
                                            @foreach($schoolClasses as $className)
                                                <option value="{{ $className }}">{{ $className }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filter_category" class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Category</label>
                                        <select id="filter_category" class="form-control form-select">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category }}">{{ $category }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filter_gender" class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Gender</label>
                                        <select id="filter_gender" class="form-control form-select">
                                            <option value="">All Genders</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filter_status" class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                                        <select id="filter_status" class="form-control form-select">
                                            <option value="">All Statuses</option>
                                            <option value="Active">Active</option>
                                            <option value="Draft">Draft</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div>
                                        <button type="reset" class="btn btn-danger-200 text-danger-600 w-100" id="resetStudentFilters">Reset</button>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary-600 w-100">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-8 text-secondary-light">
                        <span>Rows per page:</span>
                        <div class="dt-length">
                            <select name="dataTable_length" aria-controls="dataTable" class="dt-input form-control form-select">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="p-0 table-responsive student-list-table-scroll">
                    <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Gender</th>
                                <th>Category</th>
                                <th>Dormitory</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr
                                data-class="{{ $student->class_name }}"
                                data-category="{{ $student->category ?? '' }}"
                                data-gender="{{ $student->gender }}"
                                data-status="{{ $student->status }}"
                            >
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="text-primary-600 fw-semibold">{{ $student->student_id }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $studentPhoto = trim((string) ($student->picture ?? ''));
                                            $hasStudentPhoto = $studentPhoto !== '' && is_file(public_path($studentPhoto));
                                            $studentGender = strtolower(trim((string) $student->gender));
                                            $fallbackPhoto = match (true) {
                                                in_array($studentGender, ['male', 'm', 'boy'], true) => 'assets/images/avatars/student-male.svg',
                                                in_array($studentGender, ['female', 'f', 'girl'], true) => 'assets/images/avatars/student-female.svg',
                                                default => 'assets/images/student-placeholder.svg',
                                            };
                                        @endphp
                                        <img src="{{ asset($hasStudentPhoto ? $studentPhoto : $fallbackPhoto) }}" alt="{{ $student->full_name }}" class="flex-shrink-0 me-12 radius-8" width="44" height="44" style="object-fit:cover;">
                                        <div>
                                            <h6 class="text-md mb-0 fw-medium">{{ $student->full_name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->class_name }}</td>
                                <td>{{ $student->gender }}</td>
                                <td>{{ $student->category ?: '-' }}</td>
                                <td>
                                    @if($student->house && $student->dormitory && $student->bed)
                                        <span class="text-sm">{{ $student->house->name }}</span><br>
                                        <span class="text-secondary-light text-xs">{{ $student->dormitory->name }} · {{ $student->bed->bed_label }}</span>
                                    @else
                                        <span class="text-secondary-light">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($student->status == 'Active')
                                        <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                    @elseif($student->status == 'Draft')
                                        <span class="bg-warning-100 text-warning-600 px-24 py-4 radius-4 fw-medium text-sm">Draft</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $student->status }}</span>
                                    @endif
                                </td>
                                <td class="table-action-cell">
                                    <div class="dropdown">
                                        <button type="button" class="text-primary-light text-xl" data-bs-toggle="dropdown" aria-expanded="false">
                                            <iconify-icon icon="tabler:dots-vertical"></iconify-icon>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg-end border p-12">
                                            <li>
                                                <a href="{{ route('view-student-details', Crypt::encrypt($student->id)) }}"
                                                    class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
                                                    <i class="ri-user-3-line"></i> View Student
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('edit-student', Crypt::encrypt($student->id)) }}"
                                                    class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
                                                    <i class="ri-edit-2-line"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <button type="button"
                                                    class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6 assign-dormitory-btn"
                                                    data-student-id="{{ $student->id }}"
                                                    data-url="{{ route('get-student-dormitory', $student->id) }}">
                                                    <i class="ri-hotel-bed-line"></i> Assign Dormitory
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

@include('student.modals.assign-dormitory-modal')

@endsection

@section('scripts')
<script>
    let studentTableFilters = {
        class: '',
        category: '',
        gender: '',
        status: ''
    };

    let studentFilterSearchRegistered = false;

    function getStudentDataTable() {
        if (!$.fn.DataTable || !$.fn.DataTable.isDataTable('#dataTable')) {
            return null;
        }

        return $('#dataTable').DataTable();
    }

    function registerStudentTableFilter() {
        if (studentFilterSearchRegistered) {
            return;
        }

        studentFilterSearchRegistered = true;

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (!settings.nTable || settings.nTable.id !== 'dataTable') {
                return true;
            }

            const row = settings.aoData[dataIndex]?.nTr;

            if (!row) {
                return true;
            }

            const rowClass = row.getAttribute('data-class') || '';
            const rowCategory = row.getAttribute('data-category') || '';
            const rowGender = row.getAttribute('data-gender') || '';
            const rowStatus = row.getAttribute('data-status') || '';

            if (studentTableFilters.class && rowClass !== studentTableFilters.class) {
                return false;
            }

            if (studentTableFilters.category && rowCategory !== studentTableFilters.category) {
                return false;
            }

            if (studentTableFilters.gender && rowGender !== studentTableFilters.gender) {
                return false;
            }

            if (studentTableFilters.status && rowStatus !== studentTableFilters.status) {
                return false;
            }

            return true;
        });
    }

    function applyStudentTableFilters() {
        studentTableFilters = {
            class: $('#filter_class').val() || '',
            category: $('#filter_category').val() || '',
            gender: $('#filter_gender').val() || '',
            status: $('#filter_status').val() || ''
        };

        registerStudentTableFilter();

        const table = getStudentDataTable();

        if (table) {
            table.draw();
        }

        bootstrap.Dropdown.getOrCreateInstance(document.querySelector('#studentFilterDropdown > button')).hide();
    }

    function resetStudentTableFilters() {
        $('#filter_class').val('');
        $('#filter_category').val('');
        $('#filter_gender').val('');
        $('#filter_status').val('');
        applyStudentTableFilters();
    }

    $('#studentFilterForm').on('submit', function (event) {
        event.preventDefault();
        applyStudentTableFilters();
    });

    $('#resetStudentFilters').on('click', function () {
        setTimeout(resetStudentTableFilters, 0);
    });

    $('#closeStudentFilterDropdown').on('click', function () {
        bootstrap.Dropdown.getOrCreateInstance(document.querySelector('#studentFilterDropdown > button')).hide();
    });

    registerStudentTableFilter();

    document.querySelectorAll('.student-list-dataTable-wrapper .table-action-cell [data-bs-toggle="dropdown"]').forEach(function (toggle) {
        bootstrap.Dropdown.getOrCreateInstance(toggle, {
            popperConfig: {
                strategy: 'fixed'
            }
        });
    });

    let assignModal = new bootstrap.Modal(document.getElementById('assignDormitoryModal'));
    let activeStudentId = null;

    function resetAssignSelects() {
        $('#assign_dormitory_id').html('<option value="">Select dormitory</option>').prop('disabled', true);
        $('#assign_bed_id').html('<option value="">Select bed</option>').prop('disabled', true);
    }

    function loadDormitories(houseId, selectedDormitoryId, selectedBedId) {
        resetAssignSelects();

        if (!houseId) {
            return;
        }

        $.get('{{ url('get-dormitories-by-house') }}/' + houseId, function (dormitories) {
            let options = '<option value="">Select dormitory</option>';

            dormitories.forEach(function (dormitory) {
                let selected = selectedDormitoryId == dormitory.id ? 'selected' : '';
                options += '<option value="' + dormitory.id + '" ' + selected + '>' + dormitory.name + ' (' + dormitory.bed_count + ' beds)</option>';
            });

            $('#assign_dormitory_id').html(options).prop('disabled', dormitories.length === 0);

            if (selectedDormitoryId) {
                loadBeds(selectedDormitoryId, selectedBedId);
            }
        });
    }

    function loadBeds(dormitoryId, selectedBedId) {
        $('#assign_bed_id').html('<option value="">Select bed</option>').prop('disabled', true);

        if (!dormitoryId) {
            return;
        }

        let url = '{{ url('get-available-beds') }}/' + dormitoryId;

        if (activeStudentId) {
            url += '?student_id=' + activeStudentId;
        }

        $.get(url, function (beds) {
            beds.sort(function (a, b) {
                const bedNumber = function (label) {
                    const match = String(label).match(/\d+/);
                    return match ? parseInt(match[0], 10) : Number.MAX_SAFE_INTEGER;
                };

                return bedNumber(a.bed_label) - bedNumber(b.bed_label);
            });

            let options = '<option value="">Select bed</option>';

            beds.forEach(function (bed) {
                let selected = selectedBedId == bed.id ? 'selected' : '';
                options += '<option value="' + bed.id + '" ' + selected + '>' + bed.bed_label + '</option>';
            });

            $('#assign_bed_id').html(options).prop('disabled', beds.length === 0);
        });
    }

    $('.assign-dormitory-btn').on('click', function () {
        activeStudentId = $(this).data('student-id');
        $('#assign_student_id').val(activeStudentId);
        $('#unassign_student_id').val(activeStudentId);
        resetAssignSelects();
        $('#assign_house_id').val('');
        $('#currentAssignmentBox').hide();
        $('#unassignDormitoryBtn').hide();

        $.get($(this).data('url'), function (data) {
            $('#assign_student_name').text(data.student_name);

            if (data.house_id) {
                $('#currentAssignmentText').text(
                    (data.house_name || '') + ' / ' + (data.dormitory_name || '') + ' / ' + (data.bed_label || '')
                );
                $('#currentAssignmentBox').show();
                $('#unassignDormitoryBtn').show();
                $('#assign_house_id').val(data.house_id);
                loadDormitories(data.house_id, data.dormitory_id, data.bed_id);
            }

            assignModal.show();
        });
    });

    $('#assign_house_id').on('change', function () {
        loadDormitories($(this).val(), null, null);
    });

    $('#assign_dormitory_id').on('change', function () {
        loadBeds($(this).val(), null);
    });

    $('#unassignDormitoryBtn').on('click', function () {
        confirmAppAction({
            title: 'Remove dormitory assignment?',
            text: 'This student will no longer be assigned to a dormitory bed.',
            confirmButtonText: 'Yes, remove',
        }).then(function (ok) {
            if (ok) {
                $('#unassignDormitoryForm').submit();
            }
        });
    });
</script>
@endsection
