@extends('layouts.app')

@section('title', 'Complete Requirements')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta id="slea-routes"
          data-programs="{{ route('ajax.programs') }}"
          data-majors="{{ route('ajax.majors') }}"
          data-clusters="{{ route('ajax.clusters') }}"
          data-organizations="{{ route('ajax.organizations') }}"
          data-positions="{{ route('ajax.positions') }}"
          @if(\Illuminate\Support\Facades\Route::has('ajax.council.positions'))
              data-council-positions="{{ route('ajax.council.positions') }}"
          @endif
    >
@endsection

@section('content')
@php
    /** @var \App\Models\User $user */
    $user = auth()->user();
    $acad = $user->studentAcademic;
@endphp

<div class="container">
    @include('partials.sidebar')

    <main class="main-content">

        <div class="alert alert-warning mb-3">
            <strong>Account Limited.</strong><br>
            Please complete the academic and leadership requirements below.
            Once submitted, wait for <strong>Admin validation</strong>.
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- MAIN SUBMISSION FORM --}}
        <form method="POST"
              action="{{ route('profile.complete.student.store') }}"
              enctype="multipart/form-data">
            @csrf

            {{-- =========================
               ACADEMIC INFORMATION
               ========================= --}}
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Academic Information</h5></div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Student ID</label>
                            <input type="text"
                                   name="student_id"
                                   id="student_id"
                                   class="form-control"
                                   value="{{ old('student_id', $acad->student_number ?? '') }}"
                                   placeholder="e.g. 2022-00001"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">College</label>
                            <select name="college_id"
                                    id="college_id"
                                    class="form-select"
                                    required
                                    data-old="{{ old('college_id', $acad->college_id ?? '') }}">
                                <option value="">Select College</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}"
                                        {{ (string) old('college_id', $acad->college_id ?? '') === (string) $college->id ? 'selected' : '' }}>
                                        {{ $college->college_name ?? $college->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Program</label>
                            <select name="program_id"
                                    id="program_id"
                                    class="form-select"
                                    required
                                    data-old="{{ old('program_id', $acad->program_id ?? '') }}">
                                <option value="">Select Program</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Major (if any)</label>
                            <select name="major_id"
                                    id="major_id"
                                    class="form-select"
                                    data-old="{{ old('major_id', $acad->major_id ?? '') }}">
                                <option value="">Select Major</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Year Level</label>
                            <select name="year_level"
                                    id="year_level"
                                    class="form-select"
                                    required
                                    data-old="{{ old('year_level', $acad->year_level ?? '') }}">
                                <option value="">Select</option>
                                @foreach([1, 2, 3, 4, 5] as $yr)
                                    <option value="{{ $yr }}"
                                        {{ (string) old('year_level', $acad->year_level ?? '') === (string) $yr ? 'selected' : '' }}>
                                        {{ $yr }}{{ $yr === 1 ? 'st' : ($yr === 2 ? 'nd' : ($yr === 3 ? 'rd' : 'th')) }} Year
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Expected Graduation Year</label>
                            <input type="text"
                                   id="expected_grad_year"
                                   name="expected_grad_year"
                                   class="form-control"
                                   value="{{ old('expected_grad_year', $acad->expected_grad_year ?? '') }}"
                                   readonly>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================
               COR UPLOAD
               ========================= --}}
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Certificate of Registration (COR)</h5></div>
                <div class="card-body">
                    <input type="file"
                           name="cor"
                           class="form-control"
                           accept=".jpg,.jpeg,.png,.pdf"
                           {{ empty($acad?->certificate_of_registration_path) ? 'required' : '' }}>

                    <small class="text-muted d-block mt-1">JPG, PNG, or PDF • Max 5MB</small>

                    @if(!empty($acad?->certificate_of_registration_path))
                        <p class="mt-2 mb-0">
                            <a href="{{ route('student.cor.view') }}" target="_blank">View uploaded COR</a>
                        </p>
                    @endif
                </div>
            </div>

            {{-- =========================
               LEADERSHIP INFORMATION
               ========================= --}}
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Leadership Information</h5></div>
                <div class="card-body">

                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label" for="leadership_type_id">
                                Leadership Type <span class="text-danger">*</span>
                            </label>
                            <select id="leadership_type_id" name="leadership_type_id" class="form-select" required>
                                <option value="">Select Leadership Type</option>
                                @foreach($leadershipTypes ?? [] as $type)
                                    <option value="{{ $type->id }}"
                                            data-requires-org="{{ (int) ($type->requires_org ?? 0) }}"
                                            data-key="{{ $type->key ?? '' }}"
                                            {{ old('leadership_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-6" id="cluster_wrap" style="display:none;">
                            <label class="form-label" for="cluster_id">
                                Cluster <span class="text-danger">*</span>
                            </label>
                            <select id="cluster_id" name="cluster_id" class="form-select">
                                <option value="">Select Cluster</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="org_wrap" style="display:none;">
                            <label class="form-label" for="organization_id">
                                Organization <span class="text-danger">*</span>
                            </label>
                            <select id="organization_id" name="organization_id" class="form-select">
                                <option value="">Select Organization</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label" for="position_id">
                                Position Held <span class="text-danger">*</span>
                            </label>
                            <select id="position_id" name="position_id" class="form-select" required>
                                <option value="">Select Position</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="leadership_status">
                                Leadership Status <span class="text-danger">*</span>
                            </label>
                            <select id="leadership_status" name="leadership_status" class="form-select" required>
                                <option value="">Select your leadership status</option>
                                <option value="Active" {{ old('leadership_status') === 'Active' ? 'selected' : '' }}>Active (Current)</option>
                                <option value="Inactive" {{ old('leadership_status') === 'Inactive' ? 'selected' : '' }}>Inactive (Former)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label" for="term">
                                Leadership Term (School Year) <span class="text-danger">*</span>
                            </label>
                            <input id="term" type="text" name="term" class="form-control"
                                   value="{{ old('term') }}"
                                   placeholder="e.g., 2023-2024" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="issued_by">
                                Issued By <span class="text-danger">*</span>
                            </label>
                            <input id="issued_by" type="text" name="issued_by" class="form-control"
                                   value="{{ old('issued_by') }}"
                                   required>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success flex-fill">
                    Submit for Validation
                </button>
            </div>
        </form>

        {{-- LOGOUT (SEPARATE FORM - NOT NESTED) --}}
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100">
                Logout
            </button>
        </form>

    </main>
</div>

{{-- Logout form for waiting modal --}}
<form id="logout-form" method="POST" action="{{ route('logout') }}">
    @csrf
</form>

{{-- Waiting modal (optional) --}}
@if(session('show_waiting_modal'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'info',
                title: 'Submission Under Review',
                html: `
                    <p>Your submitted information is currently being reviewed by the administrator.</p>
                    <p>Please wait for approval before accessing other features.</p>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Logout',
                cancelButtonColor: '#dc3545'
            }).then(result => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    document.getElementById('logout-form').submit();
                }
            });
        });
    </script>
@endif

<script>
(function () {
    const routesMeta = document.getElementById('slea-routes');
    const routes = routesMeta?.dataset || {};
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const collegeEl = document.getElementById('college_id');
    const programEl = document.getElementById('program_id');
    const majorEl = document.getElementById('major_id');
    const studentIdEl = document.getElementById('student_id');
    const expectedGradEl = document.getElementById('expected_grad_year');

    const typeEl = document.getElementById('leadership_type_id');
    const clusterWrap = document.getElementById('cluster_wrap');
    const orgWrap = document.getElementById('org_wrap');
    const clusterEl = document.getElementById('cluster_id');
    const orgEl = document.getElementById('organization_id');
    const positionEl = document.getElementById('position_id');


    const requiresOrgOf = () => parseInt(typeEl?.options[typeEl.selectedIndex]?.dataset?.requiresOrg || '0', 10) === 1;

    async function fetchJSON(url) {
        const res = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrf ? {'X-CSRF-TOKEN': csrf} : {})
            }
        });
        return res.ok ? res.json() : [];
    }

    function setOptions(select, items, placeholder, oldValue) {
        select.innerHTML = '';
        const opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = placeholder;
        select.appendChild(opt0);

        (items || []).forEach((it) => {
            const opt = document.createElement('option');
            opt.value = it.id;
            opt.textContent = it.name;
            if (oldValue && String(oldValue) === String(it.id)) opt.selected = true;
            select.appendChild(opt);
        });
    }

    // ✅ FIXED: compute expected grad based on Student ID only (YYYY-xxxxx)
    function computeExpectedGrad() {
        const sid = (studentIdEl?.value || '').trim();
        let entryYear = null;

        const m = sid.match(/^(\d{4})-/);
        if (m) entryYear = parseInt(m[1], 10);

        if (!entryYear) {
            expectedGradEl.value = '';
            return;
        }

        expectedGradEl.value = entryYear + 4;
    }

    async function loadPrograms() {
        const collegeId = collegeEl.value;
        if (!collegeId || !routes.programs) {
            setOptions(programEl, [], 'Select Program');
            setOptions(majorEl, [], 'Select Major');
            return;
        }

        const oldProgram = programEl.dataset.old || '';
        const url = `${routes.programs}?college_id=${encodeURIComponent(collegeId)}`;
        const data = await fetchJSON(url);
        setOptions(programEl, data, 'Select Program', oldProgram);

        if (programEl.value) await loadMajors();
    }

    async function loadMajors() {
        const programId = programEl.value;
        if (!programId || !routes.majors) {
            setOptions(majorEl, [], 'Select Major');
            return;
        }

        const oldMajor = majorEl.dataset.old || '';
        const url = `${routes.majors}?program_id=${encodeURIComponent(programId)}`;
        const data = await fetchJSON(url);
        setOptions(majorEl, data, 'Select Major', oldMajor);
    }

    async function loadClusters() {
        if (!routes.clusters) return;
        const data = await fetchJSON(routes.clusters);
        setOptions(clusterEl, data, 'Select Cluster');
    }

    async function loadOrganizations() {
        const clusterId = clusterEl.value;
        if (!clusterId || !routes.organizations) {
            setOptions(orgEl, [], 'Select Organization');
            return;
        }
        const url = `${routes.organizations}?cluster_id=${encodeURIComponent(clusterId)}`;
        const data = await fetchJSON(url);
        setOptions(orgEl, data, 'Select Organization');
    }

async function loadPositions() {
    const typeId = typeEl.value;

    if (!typeId || !routes.councilPositions) {
        setOptions(positionEl, [], 'Select Position');
        return;
    }

    const url = `${routes.councilPositions}?leadership_type_id=${encodeURIComponent(typeId)}`;
    const data = await fetchJSON(url);
    setOptions(positionEl, data, 'Select Position');
}


function handleLeadershipTypeUI() {
    const opt = typeEl.options[typeEl.selectedIndex];
    const requiresOrg = parseInt(opt?.dataset?.requiresOrg || '0', 10) === 1;

    clusterWrap.style.display = requiresOrg ? '' : 'none';
    orgWrap.style.display = requiresOrg ? '' : 'none';

    if (!requiresOrg) {
        clusterEl.value = '';
        orgEl.value = '';
    }

    setOptions(positionEl, [], 'Select Position');
    loadPositions();
}

    // Events
    collegeEl?.addEventListener('change', loadPrograms);
    programEl?.addEventListener('change', loadMajors);
    studentIdEl?.addEventListener('input', computeExpectedGrad);
typeEl?.addEventListener('change', async () => {
    handleLeadershipTypeUI();

    if (requiresOrgOf()) {
        await loadClusters();
        setOptions(orgEl, [], 'Select Organization');
    } else {
        setOptions(clusterEl, [], 'Select Cluster');
        setOptions(orgEl, [], 'Select Organization');
    }
});


    clusterEl?.addEventListener('change', async () => {
        await loadOrganizations();
        await loadPositions();
    });

    orgEl?.addEventListener('change', loadPositions);

    // Initial boot
    (async function init() {
        computeExpectedGrad();

        if (collegeEl?.value) await loadPrograms();

        handleLeadershipTypeUI();

        // only pre-load clusters if current type needs org-flow

if (requiresOrgOf()) {
    await loadClusters();
}



        await loadPositions();
    })();
})();
</script>
@endsection
