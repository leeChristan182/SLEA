@extends('layouts.app')

@section('title', 'Initial Validation')

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="page-header">
                <h1>Initial Validation</h1>
            </div>

            {{-- Flash Messages --}}
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Controls Section --}}
            <div class="controls-section">
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="roleFilter">Role</label>
                        <select id="roleFilter" name="role" class="form-select" onchange="applyFilters()">
                            <option value="">All</option>
                            <option value="{{ \App\Models\User::ROLE_STUDENT }}"
                                {{ request('role') === \App\Models\User::ROLE_STUDENT ? 'selected' : '' }}>
                                Student (Limited)
                            </option>
                            <option value="{{ \App\Models\User::ROLE_ASSESSOR }}"
                                {{ request('role') === \App\Models\User::ROLE_ASSESSOR ? 'selected' : '' }}>
                                Assessor (Incomplete)
                            </option>
                        </select>
                    </div>
                </div>

                <div class="search-controls">
                    <div class="search-group">
                        <input type="text" id="searchInput" name="q" class="form-control"
                            placeholder="Search by name, email, contact"
                            value="{{ request('q') }}">
                        <button type="button" id="searchBtn" class="btn-search-maroon search-btn-attached" title="Search"
                            onclick="handleSearchClick(event)">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" id="clearBtn" class="btn-clear" title="Clear search"
                            onclick="handleClearClick(event)">
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="submissions-table-container">
                <table class="table submissions-table">
                    <thead>
                        <tr>
                            {{-- Unified info = register fields (minus password/privacy) --}}
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Birth Date</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $isStudent = $user->role === \App\Models\User::ROLE_STUDENT;

                                // Relations (make sure controller eager-loads these)
                                $acad = $user->studentAcademic;
                                $lead = $isStudent ? $user->studentLeaderships->sortByDesc('created_at')->first() : null;
                                $ass  = $user->assessorInfo;

                                $corUrl = ($isStudent && $acad && !empty($acad->certificate_of_registration_path))
                                    ? route('admin.students.cor', $user)
                                    : null;

                                // Build modal payload
                                $details = [
                                    'id' => $user->id,
                                    'name' => $user->full_name,
                                    'email' => $user->email,
                                    'contact' => $user->contact ?? null,
                                    'birth_date' => $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('F d, Y') : null,
                                    'role' => $user->role,

                                    'student' => [
                                        'student_number' => $acad->student_number ?? null,
                                        'program'        => $acad?->program?->code ?? $acad?->program?->name ?? null,
                                        'college'        => $acad?->program?->college?->code ?? $acad?->program?->college?->name ?? null,
                                        'year_level'     => $acad->year_level ?? null,
                                        'cor_url'        => $corUrl,
                                        'leadership'     => $lead ? [
                                            'type'         => $lead?->leadershipType?->name ?? null,
                                            'cluster'      => $lead?->cluster?->name ?? null,
                                            'organization' => $lead?->organization?->name ?? null,
                                            'position'     => $lead?->position?->name ?? null,
                                            'term'         => $lead->term ?? null,
                                        ] : null,
                                    ],

                                    'assessor' => [
                                        'office_unit' => $ass->office_unit ?? null,
                                        'position'    => $ass->position ?? null,
                                        'designation' => $ass->designation ?? null,
                                    ],
                                ];
                            @endphp

                            <tr>
                                <td>{{ $user->full_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->contact ?? '—' }}</td>
                                <td>
                                    {{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('F d, Y') : '—' }}
                                </td>
                                <td>{{ ucfirst($user->role) }}</td>

                                <td>
                                    <div class="action-buttons-group">

                                        {{-- VIEW DETAILS --}}
                                        <button type="button"
                                            class="btn btn-sm btn-outline-info view-details-btn"
                                            data-details='@json($details)'>
                                            <i class="fas fa-eye me-1"></i> View
                                        </button>

                                        {{-- APPROVE --}}
                                        <form action="{{ route('admin.initial-validation.approve', $user) }}"
                                            method="POST" class="d-inline approve-form"
                                            data-user-name="{{ $user->full_name }}">
                                            @csrf
                                            <button type="button" class="btn-toggle" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        {{-- REJECT --}}
                                        <form action="{{ route('admin.initial-validation.reject', $user) }}"
                                            method="POST" class="d-inline reject-form"
                                            data-user-name="{{ $user->full_name }}">
                                            @csrf
                                            <button type="button" class="btn-delete" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted" style="padding: 40px;">
                                    No users found for initial validation.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($users->hasPages())
                <div class="pagination-container" data-pagination-container>
                    <div class="pagination-info">
                        Showing {{ $users->firstItem() ?? 0 }} – {{ $users->lastItem() ?? 0 }}
                        of {{ $users->total() }} entries
                    </div>

                    <div class="unified-pagination">
                        @if ($users->onFirstPage())
                            <button class="btn-nav" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                        @else
                            <a href="{{ $users->previousPageUrl() }}" class="btn-nav">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        @endif

                        <span class="pagination-pages">
                            @php
                                $currentPage = $users->currentPage();
                                $lastPage = $users->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                            @endphp

                            @if ($start > 1)
                                <a href="{{ $users->url(1) }}" class="page-btn">1</a>
                                @if ($start > 2)
                                    <span class="page-btn disabled">...</span>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $currentPage)
                                    <span class="page-btn active">{{ $i }}</span>
                                @else
                                    <a href="{{ $users->url($i) }}" class="page-btn">{{ $i }}</a>
                                @endif
                            @endfor

                            @if ($end < $lastPage)
                                @if ($end < $lastPage - 1)
                                    <span class="page-btn disabled">...</span>
                                @endif
                                <a href="{{ $users->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                            @endif
                        </span>

                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}" class="btn-nav">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <button class="btn-nav" disabled>
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </main>
    </div>

    <link rel="stylesheet" href="{{ asset('css/pending-submissions.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function handleSearchClick(event) {
            event.preventDefault();
            const q = document.getElementById('searchInput').value.trim();
            const role = document.getElementById('roleFilter').value;

            const form = document.createElement('form');
            form.method = 'GET';
            form.action = window.location.pathname;

            if (q) addHidden(form, 'q', q);
            if (role) addHidden(form, 'role', role);

            document.body.appendChild(form);
            form.submit();
        }

        function handleClearClick(event) {
            event.preventDefault();
            document.getElementById('searchInput').value = '';
            applyFilters();
        }

        function applyFilters() {
            const q = document.getElementById('searchInput').value.trim();
            const role = document.getElementById('roleFilter').value;

            const form = document.createElement('form');
            form.method = 'GET';
            form.action = window.location.pathname;

            if (q) addHidden(form, 'q', q);
            if (role) addHidden(form, 'role', role);

            document.body.appendChild(form);
            form.submit();
        }

        function addHidden(form, name, value) {
            const i = document.createElement('input');
            i.type = 'hidden';
            i.name = name;
            i.value = value;
            form.appendChild(i);
        }

        // Auto-dismiss alerts
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alert').forEach(function (alert) {
                setTimeout(function () {
                    alert.style.transition = 'opacity 0.5s ease-out';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 1000);
            });
        });

        // View Details modal
        document.addEventListener('DOMContentLoaded', function () {
            const esc = (v) => {
                if (v === null || v === undefined || v === '') return '—';
                return String(v)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            };

            document.querySelectorAll('.view-details-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const data = JSON.parse(this.getAttribute('data-details'));
                    const isStudent = (data.role === 'student');

                    let html = `
                        <div style="text-align:left;">
                            <div><strong>Name:</strong> ${esc(data.name)}</div>
                            <div><strong>Email:</strong> ${esc(data.email)}</div>
                            <div><strong>Contact:</strong> ${esc(data.contact)}</div>
                            <div><strong>Birth Date:</strong> ${esc(data.birth_date)}</div>
                            <div><strong>Role:</strong> ${esc(data.role)}</div>
                            <hr style="margin:12px 0;">
                    `;

                    if (isStudent) {
                        const s = data.student || {};
                        const l = s.leadership || null;

                        html += `
                            <h6 style="margin:0 0 8px;"><strong>Student Academic Info</strong></h6>
                            <div><strong>Student id:</strong> ${esc(s.student_number)}</div>
                            <div><strong>Program:</strong> ${esc(s.program)}</div>
                            <div><strong>College:</strong> ${esc(s.college)}</div>
                            <div><strong>Year Level:</strong> ${esc(s.year_level)}</div>
                            <div style="margin-top:8px;">
                                <strong>COR:</strong>
                                ${s.cor_url ? `<a href="${s.cor_url}" target="_blank" rel="noopener">View COR</a>` : '—'}
                            </div>

                            <hr style="margin:12px 0;">
                            <h6 style="margin:0 0 8px;"><strong>Leadership (Latest)</strong></h6>
                        `;

                        if (l) {
                            html += `
                                <div><strong>Type:</strong> ${esc(l.type)}</div>
                                <div><strong>Cluster:</strong> ${esc(l.cluster)}</div>
                                <div><strong>Organization:</strong> ${esc(l.organization)}</div>
                                <div><strong>Position:</strong> ${esc(l.position)}</div>
                                <div><strong>Term:</strong> ${esc(l.term)}</div>
                            `;
                        } else {
                            html += `<div class="text-muted">No leadership submitted</div>`;
                        }
                    } else {
                        const a = data.assessor || {};
                        html += `
                            <h6 style="margin:0 0 8px;"><strong>Assessor Info</strong></h6>
                            <div><strong>Office/Unit:</strong> ${esc(a.office_unit)}</div>
                            <div><strong>Position:</strong> ${esc(a.position)}</div>
                            <div><strong>Designation:</strong> ${esc(a.designation)}</div>
                        `;
                    }

                    html += `</div>`;

                    Swal.fire({
                        title: 'Submitted Information',
                        html: html,
                        width: 720,
                        icon: 'info',
                        confirmButtonText: 'Close',
                        confirmButtonColor: '#6c757d'
                    });
                });
            });
        });

        // Approve/Reject confirm dialogs
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.approve-form').forEach(function (form) {
                const btn = form.querySelector('button');
                const name = form.getAttribute('data-user-name');

                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Approve Validation?',
                        html: `Approve initial validation for <strong>${name}</strong>?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Approve',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((r) => { if (r.isConfirmed) form.submit(); });
                });
            });

            document.querySelectorAll('.reject-form').forEach(function (form) {
                const btn = form.querySelector('button');
                const name = form.getAttribute('data-user-name');

                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Reject Validation?',
                        html: `Reject initial validation for <strong>${name}</strong>?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Reject',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((r) => { if (r.isConfirmed) form.submit(); });
                });
            });
        });
    </script>
@endsection
