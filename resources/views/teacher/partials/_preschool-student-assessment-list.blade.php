@php
    $totalStudents = $preschoolStudents->count();
    $maleStudents = $preschoolStudents->filter(
        fn ($student) => in_array(strtolower(trim((string) $student->gender)), ['male', 'm', 'boy'], true)
    )->count();
    $femaleStudents = $preschoolStudents->filter(
        fn ($student) => in_array(strtolower(trim((string) $student->gender)), ['female', 'f', 'girl'], true)
    )->count();
@endphp

<div class="row gy-4 mb-24">
    <div class="col-sm-6 col-xl-3">
        <div class="ac-stat-card h-100">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <p class="text-secondary-light text-sm mb-4">Total Students</p>
                    <h4 class="fw-semibold mb-0">{{ $totalStudents }}</h4>
                </div>
                <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-group-line"></i></span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="ac-stat-card h-100">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <p class="text-secondary-light text-sm mb-4">Boys</p>
                    <h4 class="fw-semibold mb-0 text-info-600">{{ $maleStudents }}</h4>
                </div>
                <span class="stat-icon bg-info-100 text-info-600"><i class="ri-men-line"></i></span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="ac-stat-card h-100">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <p class="text-secondary-light text-sm mb-4">Girls</p>
                    <h4 class="fw-semibold mb-0 text-danger-600">{{ $femaleStudents }}</h4>
                </div>
                <span class="stat-icon bg-danger-100 text-danger-600"><i class="ri-women-line"></i></span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="ac-stat-card h-100">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <p class="text-secondary-light text-sm mb-4">Preschool Classes</p>
                    <h4 class="fw-semibold mb-0 text-success-600">{{ $preschoolClasses->count() }}</h4>
                </div>
                <span class="stat-icon bg-success-100 text-success-600"><i class="ri-school-line"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="card h-100">
    <div class="card-header border-bottom bg-base py-16 px-24">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-16">
            <div>
                <h6 class="text-lg fw-semibold mb-4">Preschool Students</h6>
                <p class="text-sm text-secondary-light mb-0">
                    {{ $preschoolClasses->pluck('name')->join(', ') }} · {{ $preschoolStudents->count() }} student{{ $preschoolStudents->count() === 1 ? '' : 's' }}
                </p>
            </div>
            <form class="navbar-search dt-search m-0">
                <input type="text" class="dt-input bg-transparent radius-4" aria-controls="preschoolStudentTable" name="search" placeholder="Search students...">
                <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
            </form>
        </div>
    </div>

    <div class="card-body p-0 dataTable-wrapper">
        <div class="table-responsive">
            <table class="table bordered-table mb-0 data-table preschool-student-table" id="preschoolStudentTable" data-page-length="10">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Gender</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($preschoolStudents as $student)
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
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="text-primary-600 fw-semibold">{{ $student->student_id }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img
                                        src="{{ asset($hasStudentPhoto ? $studentPhoto : $fallbackPhoto) }}"
                                        alt="{{ $student->full_name }}"
                                        class="preschool-student-photo flex-shrink-0 me-12 radius-8"
                                    >
                                    <h6 class="text-md mb-0 fw-medium">{{ $student->full_name }}</h6>
                                </div>
                            </td>
                            <td>{{ $student->schoolClass?->name ?? $student->class_name }}</td>
                            <td>{{ $student->gender ?: '—' }}</td>
                            <td>{{ $student->schoolClass?->category?->name ?? $student->category ?? '—' }}</td>
                            <td>
                                <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $student->status }}</span>
                            </td>
                            <td>
                                <a
                                    href="{{ route('teacher-preschool-student-assessment', $student) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-sm btn-primary-600 d-inline-flex align-items-center gap-6"
                                >
                                    <i class="ri-arrow-right-line"></i> Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-40 text-secondary-light">
                                No active students are assigned to this preschool class.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
