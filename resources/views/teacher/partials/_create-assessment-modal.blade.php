@php
    $courseOptionsForJs = collect($subjectAssignments ?? [])->map(function ($a) {
        return [
            'class_id' => (string) $a->school_class_id,
            'course_id' => (string) $a->course_id,
            'course_name' => $a->course?->name ?? 'Unknown subject',
        ];
    })->values();
    $defaultClassIdJs = isset($defaultClassId) ? (string) $defaultClassId : '';
    $defaultCourseIdJs = isset($defaultCourseId) ? (string) $defaultCourseId : '';
    $lockClass = (bool) ($lockClass ?? false);
    $lockCourse = (bool) ($lockCourse ?? false);

    $homeroomIds = collect($homeroomClasses ?? [])->pluck('id')->flip();
    $classOptions = collect();
    foreach ($homeroomClasses ?? [] as $class) {
        $classOptions->put($class->id, ['name' => $class->name, 'homeroom' => true]);
    }
    foreach ($subjectAssignments ?? [] as $a) {
        if ($a->schoolClass && ! $classOptions->has($a->school_class_id)) {
            $classOptions->put($a->school_class_id, [
                'name' => $a->schoolClass->name,
                'homeroom' => $homeroomIds->has($a->school_class_id),
            ]);
        }
    }
    if ($lockClass && isset($defaultClassId) && isset($schoolClass) && ! $classOptions->has($defaultClassId)) {
        $classOptions->put($defaultClassId, [
            'name' => $schoolClass->name,
            'homeroom' => $homeroomIds->has($defaultClassId),
        ]);
    }
@endphp

<div class="modal fade" id="createAssessmentModal" tabindex="-1"
    data-courses='@json($courseOptionsForJs)'
    data-default-class="{{ $defaultClassIdJs }}"
    data-default-course="{{ $defaultCourseIdJs }}"
    data-lock-class="{{ $lockClass ? '1' : '0' }}"
    data-lock-course="{{ $lockCourse ? '1' : '0' }}"
    data-setup-url="{{ route('teacher-assessment-setup-options') }}"
    data-year-id="{{ $period['year_id'] ?? '' }}"
    data-term-id="{{ $period['term_id'] ?? '' }}">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('teacher-assessments-process') }}" method="POST" class="modal-content">
            @csrf
            @if(!empty($period['year_id']))
                <input type="hidden" name="academic_year_id" value="{{ $period['year_id'] }}">
            @endif
            @if(!empty($period['term_id']))
                <input type="hidden" name="academic_term_id" value="{{ $period['term_id'] }}">
            @endif
            <div class="modal-header">
                <h5 class="modal-title">Create Assessment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if(!empty($period['year_name']) && !empty($period['term_name']))
                    <div class="alert alert-info py-10 px-14 mb-16 d-flex align-items-center gap-8">
                        <i class="ri-calendar-line"></i>
                        <span>This assessment will be recorded for <strong>{{ $period['year_name'] }} · {{ $period['term_name'] }}</strong></span>
                    </div>
                @endif
                <div id="assessment_setup_hint" class="alert alert-warning py-10 px-14 mb-16 d-none"></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Class</label>
                        @if($lockClass && $defaultClassIdJs)
                            <input type="hidden" name="school_class_id" value="{{ $defaultClassIdJs }}">
                            <select class="form-select" id="assessment_class_id" disabled>
                                @foreach($classOptions as $id => $meta)
                                    @if((string) $id === $defaultClassIdJs)
                                        <option value="{{ $id }}" selected>{{ $meta['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        @else
                            <select name="school_class_id" class="form-select" required id="assessment_class_id">
                                @foreach($classOptions as $id => $meta)
                                    <option value="{{ $id }}" @selected(($defaultClassId ?? null) == $id)>{{ $meta['name'] }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Course</label>
                        @if($lockCourse && $defaultCourseIdJs)
                            <input type="hidden" name="course_id" value="{{ $defaultCourseIdJs }}">
                            <select class="form-select" id="assessment_course_id" disabled></select>
                        @else
                            <select name="course_id" class="form-select" required id="assessment_course_id"></select>
                        @endif
                        <div class="form-text" id="assessment_course_hint">Courses update when you change the class.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select name="type" id="assessment_type_id" class="form-select" required>
                            <option value="" disabled selected>Select class and subject first</option>
                        </select>
                        <div class="form-text" id="assessment_type_hint">Types follow the class category. Set marks before creating an assessment.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach(\App\Models\AcademicAssessment::STATUSES as $status)
                                <option value="{{ $status }}" @selected($status === 'published')>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required maxlength="200">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Score</label>
                        <input type="number" name="max_score" id="assessment_max_score" class="form-control" min="1" readonly>
                        <div class="form-text">Filled from the marks you set for this type.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Assessment Date</label>
                        <input type="date" name="assessment_date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary-600" id="assessment_create_submit">Create Assessment</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    function parseJsonAttr(el, name, fallback) {
        try {
            return JSON.parse(el.getAttribute(name) || fallback);
        } catch (e) {
            return JSON.parse(fallback);
        }
    }

    function initAssessmentClassCourseFilter() {
        const modal = document.getElementById('createAssessmentModal');
        const classSelect = document.getElementById('assessment_class_id');
        const courseSelect = document.getElementById('assessment_course_id');
        const typeSelect = document.getElementById('assessment_type_id');
        const maxScoreInput = document.getElementById('assessment_max_score');
        const hint = document.getElementById('assessment_course_hint');
        const typeHint = document.getElementById('assessment_type_hint');
        const setupHint = document.getElementById('assessment_setup_hint');
        const submitBtn = document.getElementById('assessment_create_submit');

        if (! modal || ! classSelect || ! courseSelect || ! typeSelect) {
            return;
        }

        const allCourses = parseJsonAttr(modal, 'data-courses', '[]');
        const defaultClass = modal.getAttribute('data-default-class') || '';
        const defaultCourse = modal.getAttribute('data-default-course') || '';
        const lockClass = modal.getAttribute('data-lock-class') === '1';
        const lockCourse = modal.getAttribute('data-lock-course') === '1';
        const setupUrl = modal.getAttribute('data-setup-url');
        const yearId = modal.getAttribute('data-year-id') || '';
        const termId = modal.getAttribute('data-term-id') || '';
        let setupRequest = 0;

        function currentClassId() {
            if (lockClass) {
                return defaultClass || classSelect.value;
            }
            return classSelect.value;
        }

        function currentCourseId() {
            if (lockCourse) {
                return defaultCourse || courseSelect.value;
            }
            return courseSelect.value;
        }

        function coursesForClass(classId) {
            return allCourses.filter(function (c) {
                return c.class_id === String(classId);
            });
        }

        function rebuildCourseSelect(preferredCourseId) {
            const classId = currentClassId();
            const courses = coursesForClass(classId);

            if (lockCourse) {
                courseSelect.innerHTML = '';
                const locked = allCourses.find(function (c) {
                    return c.class_id === String(classId) && c.course_id === String(defaultCourse);
                });
                const opt = document.createElement('option');
                opt.value = defaultCourse;
                opt.textContent = locked ? locked.course_name : 'Selected subject';
                opt.selected = true;
                courseSelect.appendChild(opt);
                hint.textContent = 'Subject is fixed for this workspace.';
                loadTypeOptions();
                return;
            }

            courseSelect.innerHTML = '';

            if (! classId) {
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Select a class first';
                placeholder.disabled = true;
                placeholder.selected = true;
                courseSelect.appendChild(placeholder);
                courseSelect.required = false;
                hint.textContent = 'Select a class first to see available courses.';
                clearTypes('Select a class first.');
                return;
            }

            if (courses.length === 0) {
                const emptyOpt = document.createElement('option');
                emptyOpt.value = '';
                emptyOpt.textContent = '— No subjects —';
                emptyOpt.disabled = true;
                emptyOpt.selected = true;
                courseSelect.appendChild(emptyOpt);
                courseSelect.required = false;
                hint.textContent = 'No subject assignments for this class.';
                clearTypes('No subject selected.');
                return;
            }

            courses.forEach(function (course) {
                const opt = document.createElement('option');
                opt.value = course.course_id;
                opt.textContent = course.course_name;
                courseSelect.appendChild(opt);
            });

            courseSelect.required = true;

            const pick = preferredCourseId || defaultCourse;
            const options = Array.from(courseSelect.options);

            if (pick && options.some(function (o) { return o.value === String(pick); })) {
                courseSelect.value = String(pick);
            } else {
                courseSelect.value = courses[0].course_id;
            }

            hint.textContent = courses.length + ' subject' + (courses.length === 1 ? '' : 's') + ' available for this class.';
            loadTypeOptions();
        }

        function clearTypes(message) {
            typeSelect.innerHTML = '';
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = message || 'Select class and subject first';
            opt.disabled = true;
            opt.selected = true;
            typeSelect.appendChild(opt);
            typeSelect.disabled = true;
            if (maxScoreInput) {
                maxScoreInput.value = '';
            }
            if (typeHint) {
                typeHint.textContent = message || '';
            }
            if (setupHint) {
                setupHint.classList.add('d-none');
                setupHint.innerHTML = '';
            }
            if (submitBtn) {
                submitBtn.disabled = true;
            }
        }

        function applyTypeDefaults() {
            const option = typeSelect.options[typeSelect.selectedIndex];
            const totalScore = option?.dataset?.totalScore;

            if (maxScoreInput) {
                maxScoreInput.value = totalScore || '';
            }
        }

        function loadTypeOptions() {
            const classId = currentClassId();
            const courseId = currentCourseId();

            if (! classId || ! courseId || ! setupUrl) {
                clearTypes('Select class and subject first.');
                return;
            }

            const requestId = ++setupRequest;
            const params = new URLSearchParams({
                school_class_id: classId,
                course_id: courseId,
            });
            if (yearId) params.set('academic_year_id', yearId);
            if (termId) params.set('academic_term_id', termId);

            fetch(setupUrl + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (requestId !== setupRequest) {
                        return;
                    }

                    const types = data.types || [];
                    typeSelect.innerHTML = '';

                    if (types.length === 0) {
                        clearTypes('No assessment types for this class category.');
                        if (setupHint) {
                            setupHint.classList.remove('d-none');
                            setupHint.innerHTML = 'Ask an administrator to add assessment types for this class category.';
                        }
                        return;
                    }

                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = 'Select assessment type';
                    placeholder.disabled = true;
                    placeholder.selected = true;
                    typeSelect.appendChild(placeholder);

                    let firstUsable = null;
                    let unsetCount = 0;

                    types.forEach(function (type) {
                        const opt = document.createElement('option');
                        opt.value = type.slug;
                        opt.dataset.totalScore = type.total_score || '';
                        opt.dataset.maxNumber = type.max_number || '';
                        opt.dataset.remaining = type.remaining;

                        if (! type.marks_set) {
                            opt.textContent = type.name + ' — set marks first';
                            opt.disabled = true;
                            unsetCount += 1;
                        } else if (type.remaining <= 0) {
                            opt.textContent = type.name + ' — limit reached (' + type.max_number + ')';
                            opt.disabled = true;
                        } else {
                            opt.textContent = type.name + ' / ' + type.total_score;
                            if (! firstUsable) {
                                firstUsable = type.slug;
                            }
                        }

                        typeSelect.appendChild(opt);
                    });

                    typeSelect.disabled = ! firstUsable;

                    if (firstUsable) {
                        typeSelect.value = firstUsable;
                        if (submitBtn) {
                            submitBtn.disabled = false;
                        }
                    } else if (submitBtn) {
                        submitBtn.disabled = true;
                    }

                    applyTypeDefaults();

                    if (typeHint) {
                        typeHint.textContent = firstUsable
                            ? 'Max score is taken from the marks you set for this class and subject.'
                            : 'Set assessment marks for this subject before creating an assessment.';
                    }

                    if (setupHint) {
                        if (unsetCount > 0 && data.marks_url) {
                            setupHint.classList.remove('d-none');
                            setupHint.innerHTML = 'Set assessment marks for this subject first. <a href="' + data.marks_url + '" class="fw-semibold">Open marks setup</a>.';
                        } else {
                            setupHint.classList.add('d-none');
                            setupHint.innerHTML = '';
                        }
                    }
                })
                .catch(function () {
                    if (requestId !== setupRequest) {
                        return;
                    }
                    clearTypes('Could not load assessment types.');
                });
        }

        if (! lockClass) {
            classSelect.addEventListener('change', function () {
                rebuildCourseSelect('');
            });
        }

        if (! lockCourse) {
            courseSelect.addEventListener('change', loadTypeOptions);
        }

        typeSelect.addEventListener('change', applyTypeDefaults);

        if (! modal.dataset.filterBound) {
            modal.dataset.filterBound = '1';
            modal.addEventListener('shown.bs.modal', function () {
                rebuildCourseSelect(defaultCourse);
            });
        }

        rebuildCourseSelect(defaultCourse);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAssessmentClassCourseFilter);
    } else {
        initAssessmentClassCourseFilter();
    }
})();
</script>
