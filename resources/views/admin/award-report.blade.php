@extends('layouts.app')

@section('title', 'Award Report')

@section('content')
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <div class="page-header">
                <h1>Award Report</h1>
            </div>

            {{-- Flash Messages --}}
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Controls Section --}}
            <div class="controls-section">
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="college">College</label>
                        <select name="college" id="college" class="form-select" onchange="updatePrograms()">
                            <option value="">All Colleges</option>
                            <option value="College of Education" @selected(request('college') === 'College of Education')>
                                College of Education</option>
                            <option value="College of Engineering" @selected(request('college') === 'College of Engineering')>
                                College of Engineering</option>
                            <option value="College of Information and Computing" @selected(request('college') === 'College of Information and Computing')>College of Information and Computing</option>
                            <option value="College of Business Administration" @selected(request('college') === 'College of Business Administration')>College of Business Administration</option>
                            <option value="College of Arts and Science" @selected(request('college') === 'College of Arts and Science')>College of Arts and Science</option>
                            <option value="College of Applied Economics" @selected(request('college') === 'College of Applied Economics')>College of Applied Economics</option>
                            <option value="College of Technology" @selected(request('college') === 'College of Technology')>
                                College of Technology</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="program">Program</label>
                        <select name="program" id="program" class="form-select">
                            <option value="">All Programs</option>
                            <!-- Programs will be populated dynamically based on selected college -->
                        </select>
                    </div>
                </div>

                <div class="search-controls">
                    <div class="search-group">
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Search by name or ID..." value="{{ request('search') }}">
                        <button type="button" class="btn-search-maroon search-btn-attached" id="searchBtn" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" class="btn-export-pdf" onclick="exportReport()" id="exportBtn"
                            title="Export PDF">
                            <i class="fas fa-file-pdf"></i> Export
                        </button>
                        <a href="{{ route('admin.award-report') }}" class="btn-clear" title="Clear filters">
                            Clear
                        </a>
                    </div>
                </div>
            </div>

            {{-- Filter Form (used by search button) --}}
            <form method="GET" action="{{ route('admin.award-report') }}" id="filterForm" style="display: none;">
                <input type="hidden" name="college" id="collegeHidden" value="{{ request('college') }}">
                <input type="hidden" name="program" id="programHidden" value="{{ request('program') }}">
                <input type="hidden" name="search" id="searchHidden" value="{{ request('search') }}">
            </form>

            {{-- SLEA Recipients Section --}}
            <div class="submissions-table-container">
                <h3 class="program-title">SLEA Recipients</h3>

                <table class="table submissions-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>College</th>
                            <th>Program</th>
                            <th>Total Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>{{ $student['student_id'] }}</td>
                                <td>{{ $student['name'] }}</td>
                                <td>{{ $student['college'] }}</td>
                                <td>{{ $student['program'] }}</td>
                                <td>{{ $student['points_display'] ?? number_format($student['points'] ?? 0, 2) . '/' . number_format($student['max_points'] ?? 100, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No SLEA recipients found matching the
                                    selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($students->hasPages())
                <div class="pagination-container" data-pagination-container>
                    <div class="pagination-info">
                        Showing {{ $students->firstItem() ?? 0 }} – {{ $students->lastItem() ?? 0 }}
                        of {{ $students->total() }} entries
                    </div>

                    <div class="unified-pagination">
                        @if($students->onFirstPage())
                            <button class="btn-nav" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                        @else
                            <a href="{{ $students->previousPageUrl() }}" class="btn-nav">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        @endif

                        <span class="pagination-pages">
                            @php
                                $currentPage = $students->currentPage();
                                $lastPage = $students->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                            @endphp

                            @if($start > 1)
                                <a href="{{ $students->url(1) }}" class="page-btn">1</a>
                                @if($start > 2)
                                    <span class="page-btn disabled">...</span>
                                @endif
                            @endif

                            @for($i = $start; $i <= $end; $i++)
                                @if($i == $currentPage)
                                    <span class="page-btn active">{{ $i }}</span>
                                @else
                                    <a href="{{ $students->url($i) }}" class="page-btn">{{ $i }}</a>
                                @endif
                            @endfor

                            @if($end < $lastPage)
                                @if($end < $lastPage - 1)
                                    <span class="page-btn disabled">...</span>
                                @endif
                                <a href="{{ $students->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
                            @endif
                        </span>

                        @if($students->hasMorePages())
                            <a href="{{ $students->nextPageUrl() }}" class="btn-nav">
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
    <style>
        /* Award Report Specific Styles */
        .program-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #7E0308;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #7E0308;
        }

        body.dark-mode .program-title {
            color: #F9BD3D;
            border-bottom-color: #F9BD3D;
        }

        .btn-export-pdf {
            background-color: #7E0308;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1rem;
            height: 38px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .btn-export-pdf:hover {
            background-color: #5a0206;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(126, 3, 8, 0.3);
        }

        .btn-export-pdf:active {
            transform: translateY(0);
        }

        body.dark-mode .btn-export-pdf {
            background-color: #7E0308;
        }

        body.dark-mode .btn-export-pdf:hover {
            background-color: #9a040a;
        }

        /* Filter group width adjustments */
        .filter-group select {
            width: 200px;
            max-width: 200px;
            min-width: 150px;
        }

        /* Search input width */
        .search-group .form-control {
            max-width: 300px;
        }

        /* Table adjustments for sidebar */
        .submissions-table-container {
            margin-top: 1.5rem;
        }

        /* Pagination info styling */
        .pagination-info {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
        }

        body.dark-mode .pagination-info {
            color: #9ca3af;
        }
    </style>

    <script>
        // College-Program mapping
        const collegePrograms = {
            'College of Education': ['BTVTED', 'BPED', 'BSED', 'MAED', 'PhD Education'],
            'College of Engineering': ['BSCE', 'BSEE', 'BSME', 'BSIE', 'BSCpE', 'BSArch'],
            'College of Information and Computing': ['BSIT', 'BSCS', 'BSIS', 'BSEMC'],
            'College of Business Administration': ['BSBA', 'BSA', 'BSMA', 'BSHRM', 'BSHM'],
            'College of Arts and Science': ['BS Biology', 'BS Chemistry', 'BS Physics', 'BS Mathematics', 'BA English', 'BA History', 'BA Political Science'],
            'College of Applied Economics': ['BS Economics', 'BS Agricultural Economics', 'BS Development Economics'],
            'College of Technology': ['BS Industrial Technology', 'BS Food Technology', 'BS Electronics Technology']
        };

        function updatePrograms() {
            const collegeSelect = document.getElementById('college');
            const programSelect = document.getElementById('program');
            const collegeHidden = document.getElementById('collegeHidden');
            const programHidden = document.getElementById('programHidden');
            const selectedCollege = collegeSelect.value;
            const currentProgram = '{{ request('program') }}';

            // Update hidden input
            if (collegeHidden) {
                collegeHidden.value = selectedCollege;
            }

            // Clear existing options
            programSelect.innerHTML = '<option value="">All Programs</option>';

            // Add programs based on selected college
            if (selectedCollege && collegePrograms[selectedCollege]) {
                collegePrograms[selectedCollege].forEach(program => {
                    const option = document.createElement('option');
                    option.value = program;
                    option.textContent = program;
                    if (program === currentProgram) {
                        option.selected = true;
                    }
                    programSelect.appendChild(option);
                });
            }

            // Update hidden program input
            if (programHidden) {
                programHidden.value = programSelect.value;
            }
        }

        function exportReport() {
            // Get current filter values
            const college = document.getElementById('college').value;
            const program = document.getElementById('program').value;
            const search = document.getElementById('search').value;

            // Build export URL with filters
            let exportUrl = '{{ route("admin.award-report.export") }}?';
            const params = new URLSearchParams();

            if (college) params.append('college', college);
            if (program) params.append('program', program);
            if (search) params.append('search', search);

            exportUrl += params.toString();

            // Open export in new window/tab
            window.open(exportUrl, '_blank');
        }

        // Handle form submission
        document.addEventListener('DOMContentLoaded', function () {
            updatePrograms();

            // Sync visible inputs with hidden form inputs
            const collegeSelect = document.getElementById('college');
            const programSelect = document.getElementById('program');
            const searchInput = document.getElementById('search');
            const filterForm = document.getElementById('filterForm');

            if (collegeSelect) {
                collegeSelect.addEventListener('change', function () {
                    updatePrograms();
                });
            }

            if (programSelect) {
                programSelect.addEventListener('change', function () {
                    const programHidden = document.getElementById('programHidden');
                    if (programHidden) {
                        programHidden.value = programSelect.value;
                    }
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const searchHidden = document.getElementById('searchHidden');
                    if (searchHidden) {
                        searchHidden.value = searchInput.value;
                    }
                });
            }

            // Handle search button click
            const searchBtn = document.getElementById('searchBtn');
            if (searchBtn && filterForm) {
                searchBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Update hidden inputs with current values
                    const collegeHidden = document.getElementById('collegeHidden');
                    const programHidden = document.getElementById('programHidden');
                    const searchHidden = document.getElementById('searchHidden');

                    if (collegeHidden && collegeSelect) collegeHidden.value = collegeSelect.value;
                    if (programHidden && programSelect) programHidden.value = programSelect.value;
                    if (searchHidden && searchInput) searchHidden.value = searchInput.value;

                    filterForm.submit();
                });
            }

            // Handle filter dropdown changes - auto-submit
            if (collegeSelect) {
                collegeSelect.addEventListener('change', function () {
                    updatePrograms();
                    // Optionally auto-submit on college change
                    // const collegeHidden = document.getElementById('collegeHidden');
                    // if (collegeHidden) collegeHidden.value = collegeSelect.value;
                    // filterForm.submit();
                });
            }
        });
    </script>
    <script src="{{ asset('js/admin_pagination.js') }}"></script>
@endsection