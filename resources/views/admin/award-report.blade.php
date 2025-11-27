@extends('layouts.app')

@section('title', 'Award Report')

@section('content')
<div class="page-wrapper">
    <div class="manage-container award-report-box">
                <h2 class="manage-title">Award Report</h2>

                {{-- Messages --}}
                @if (session('status'))
        <div class="alert alert-success mt-2">
            {{ session('status') }}
        </div>
                @endif
                @if ($errors->any())
        <div class="alert alert-danger mt-2">
            <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

        <!-- Back button -->
        <div class="rubric-header-nav mb-2">
            <a href="{{ route('admin.profile') }}" class="btn-back-maroon">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

                {{-- Filter Section --}}
        <div class="filter-section mb-3">
                    <form method="GET" action="{{ route('admin.award-report') }}" id="filterForm">
                <div class="filter-row d-flex justify-content-between align-items-end flex-wrap gap-2">
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                            <div class="filter-item">
                                <label for="college">College</label>
                                <select name="college" id="college" class="filter-select" onchange="updatePrograms()">
                                    <option value="">All Colleges</option>
                                    <option value="College of Education" @selected(request('college')==='College of Education' )>College of Education</option>
                                    <option value="College of Engineering" @selected(request('college')==='College of Engineering' )>College of Engineering</option>
                                    <option value="College of Information and Computing" @selected(request('college')==='College of Information and Computing' )>College of Information and Computing</option>
                                    <option value="College of Business Administration" @selected(request('college')==='College of Business Administration' )>College of Business Administration</option>
                                    <option value="College of Arts and Science" @selected(request('college')==='College of Arts and Science' )>College of Arts and Science</option>
                                    <option value="College of Applied Economics" @selected(request('college')==='College of Applied Economics' )>College of Applied Economics</option>
                                    <option value="College of Technology" @selected(request('college')==='College of Technology' )>College of Technology</option>
                                </select>
                            </div>

                            <div class="filter-item">
                                <label for="program">Program</label>
                                <select name="program" id="program" class="filter-select">
                                    <option value="">All Programs</option>
                                    <!-- Programs will be populated dynamically based on selected college -->
                                </select>
                            </div>

                            <div class="filter-item">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="filter-input" placeholder="Search by name or ID..." value="{{ request('search') }}">
                            </div>
                            </div>

                    <div class="filter-actions d-flex align-items-center gap-2">
                        <button type="submit" class="btn-search-maroon">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" class="btn-export-pdf" onclick="exportReport()" id="exportBtn">
                            <i class="fas fa-file-pdf"></i> Export
                                </button>
                        <a href="{{ route('admin.award-report') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

        {{-- Unified Table for All SLEA Recipients --}}
                <div class="program-section">
            <h3 class="program-title">SLEA Recipients</h3>
            <div class="pagination-info mb-2">
                @if($students->total() > 0)
                    Showing {{ $students->firstItem() ?? 0 }} – {{ $students->lastItem() ?? 0 }} of {{ $students->total() }} entries
                @else
                    Showing 0 – 0 of 0 entries
                @endif
                    </div>
            <div class="table-wrap compact-table">
                <table class="manage-table">
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
                                    <td>{{ $student['points_display'] ?? number_format($student['points'] ?? 0, 2) . '/' . number_format($student['max_points'] ?? 100, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No SLEA recipients found matching the selected filters.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

            {{-- Pagination --}}
            @if($students->hasPages())
            <div class="pagination-wrapper mt-4">
                {{-- Previous --}}
                @if($students->onFirstPage())
                <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
                @else
                <a href="{{ $students->previousPageUrl() }}" class="page-btn">
                    <i class="fas fa-chevron-left"></i>
                </a>
                @endif

                {{-- Page numbers --}}
                @foreach($students->links()->elements ?? [] as $element)
                    @if(is_string($element))
                    <span class="page-btn disabled">{{ $element }}</span>
                    @endif

                    @if(is_array($element))
                        @foreach($element as $page => $url)
                            @if($page == $students->currentPage())
                            <span class="page-btn active">{{ $page }}</span>
                            @else
                            <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                @if($students->hasMorePages())
                <a href="{{ $students->nextPageUrl() }}" class="page-btn">
                    <i class="fas fa-chevron-right"></i>
                </a>
                @else
                <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
                @endif
            </div>
            @endif
                </div>
            </div>
        </div>

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
                const selectedCollege = collegeSelect.value;
        const currentProgram = '{{ request('program') }}';

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

            // Initialize programs on page load
            document.addEventListener('DOMContentLoaded', function() {
                updatePrograms();
            });
</script>

<style>
    .page-wrapper {
        padding-top: 60px;
    }

    .award-report-box {
        width: 80%;
        margin: 0 auto 40px;
        margin-top: 20px;
        background: var(--card-bg, #fff);
        border-radius: 14px;
        padding: 30px;
        transition: background 0.3s, color 0.3s;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    .award-report-box .manage-title {
        margin-bottom: 10px;
    }

    .award-report-box .rubric-header-nav {
        margin-bottom: 10px;
    }

    .program-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 12px;
        color: #7b0000;
    }

    .pagination-info {
        font-size: 14px;
        color: #6b7280;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 18px;
        flex-wrap: wrap;
    }

    .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid var(--border-color, #ddd);
        background: var(--card-bg, #fff);
        color: var(--text-color, #333);
        text-decoration: none;
        transition: all .15s ease;
        cursor: pointer;
    }

    .page-btn:hover {
        transform: scale(1.05);
        background: rgba(126, 3, 8, 0.1);
        color: #7E0308;
        border-color: #7E0308;
    }

    .page-btn.active {
        background: #7E0308;
        color: #fff;
        border-color: #7E0308;
    }

    .page-btn.disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    /* Dark-mode adjustments */
    body.dark-mode .award-report-box {
        background: #1f1f1f;
        color: #f0f0f0;
        box-shadow: 0 0 12px rgba(255, 255, 255, 0.03);
    }

    body.dark-mode .page-btn {
        background: #2b2b2b;
        border-color: #444;
        color: #eaeaea;
    }

    body.dark-mode .page-btn.active {
        background: #7E0308;
        border-color: #7E0308;
    }

    body.dark-mode .page-btn:hover {
        background: rgba(126, 3, 8, 0.2);
        color: #fff;
        border-color: #7E0308;
    }

    .filter-section {
        background: var(--card-bg, #f9fafb);
        padding: 20px;
        border-radius: 8px;
        border: 1px solid var(--border-color, #e5e7eb);
    }

    body.dark-mode .filter-section {
        background: #2a2a2a;
        border-color: #444;
    }

    .filter-section .filter-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-section .filter-item label {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-color, #333);
        margin-bottom: 0;
    }

    .filter-section .filter-row {
        align-items: flex-end !important;
    }

    .filter-section .filter-actions {
        align-items: flex-end;
        display: flex;
        gap: 8px;
    }

    .filter-section .filter-actions button,
    .filter-section .filter-actions a {
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    body.dark-mode .filter-section .filter-item label {
        color: #f0f0f0;
    }

    .btn-search-maroon {
        background-color: #7E0308;
        color: #fff;
        border: 1px solid #7E0308;
        border-radius: 6px;
        padding: 8px 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .btn-search-maroon:hover {
        background-color: #5a0206;
        border-color: #5a0206;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(126, 3, 8, 0.3);
    }

    .btn-search-maroon:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(126, 3, 8, 0.3);
    }

    .btn-search-maroon i {
        font-size: 16px;
    }

    body.dark-mode .btn-search-maroon {
        background-color: #7E0308;
        border-color: #7E0308;
    }

    body.dark-mode .btn-search-maroon:hover {
        background-color: #9a040a;
        border-color: #9a040a;
    }

    .btn-back-maroon {
        background-color: #7E0308;
        color: #fff;
        border: 1px solid #7E0308;
        border-radius: 6px;
        padding: 8px 16px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .btn-back-maroon:hover {
        background-color: #5a0206;
        border-color: #5a0206;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(126, 3, 8, 0.3);
        text-decoration: none;
    }

    .btn-back-maroon:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(126, 3, 8, 0.3);
    }

    .btn-back-maroon i {
        font-size: 14px;
    }

    body.dark-mode .btn-back-maroon {
        background-color: #7E0308;
        border-color: #7E0308;
    }

    body.dark-mode .btn-back-maroon:hover {
        background-color: #9a040a;
        border-color: #9a040a;
    }
</style>
@endsection
