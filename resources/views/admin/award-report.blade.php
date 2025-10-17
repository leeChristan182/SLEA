@extends('layouts.app')

@section('title', 'Award Report')

@section('content')
<div class="container">
    @include('partials.sidebar')

    <main class="main-content">
        <div class="page-with-back-button">
            <div class="page-content">
                <!-- Back Button -->
                <div class="rubric-header-nav">
                    <a href="{{ route('admin.profile') }}" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <h2 class="manage-title">Award Report</h2>

                {{-- Messages --}}
                @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="m-0 pl-4">
                        @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Filter Section --}}
                <div class="filter-section">
                    <form method="GET" action="{{ route('admin.award-report') }}" id="filterForm">
                        <div class="filter-row">
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
                                <label for="batch">Graduation Batch</label>
                                <select name="batch" class="filter-select">
                                    <option value="">All Batches</option>
                                    <option value="2021" @selected(request('batch')==='2021' )>2021</option>
                                    <option value="2022" @selected(request('batch')==='2022' )>2022</option>
                                    <option value="2023" @selected(request('batch')==='2023' )>2023</option>
                                    <option value="2024" @selected(request('batch')==='2024' )>2024</option>
                                </select>
                            </div>

                            <div class="filter-item">
                                <label for="min_score">Minimum Score</label>
                                <select name="min_score" class="filter-select">
                                    <option value="">All Scores</option>
                                    <option value="75" @selected(request('min_score')==='75' )>75+</option>
                                    <option value="80" @selected(request('min_score')==='80' )>80+</option>
                                    <option value="85" @selected(request('min_score')==='85' )>85+</option>
                                    <option value="90" @selected(request('min_score')==='90' )>90+</option>
                                </select>
                            </div>

                            <div class="filter-actions">
                                <button type="submit" class="btn-filter">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.award-report') }}" class="btn-clear">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Program Sections --}}
                <div class="program-section">
                    <h3 class="program-title">BTVTED Program</h3>
                    <div class="pagination-info">
                        Showing <span id="showingStart">1</span>-<span id="showingEnd">10</span> of <span id="totalEntries">0</span> entries
                    </div>
                    <div class="table-wrap">
                        <table class="award-table">
                            <thead>
                                <tr>
                                    <th class="col-name">Student Name</th>
                                    <th class="col-id">Student ID</th>
                                    <th class="col-program">Program</th>
                                    <th class="col-points">Total Points</th>
                                    <th class="col-status">Leadership Status</th>
                                    <th class="col-action">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                // Sample data for BTVTED - Limited to 10 entries per page
                                $btvtedStudents = [
                                ['id' => 1, 'name' => 'John Smith', 'student_id' => '2021-001', 'program' => 'BTVTED', 'points' => 85, 'status' => 'Tracking'],
                                ['id' => 2, 'name' => 'Sarah Wilson', 'student_id' => '2021-002', 'program' => 'BTVTED', 'points' => 92, 'status' => 'For Final Review'],
                                ['id' => 3, 'name' => 'Michael Brown', 'student_id' => '2021-003', 'program' => 'BTVTED', 'points' => 78, 'status' => 'SLEA Qualified'],
                                ['id' => 4, 'name' => 'Lisa Garcia', 'student_id' => '2021-004', 'program' => 'BTVTED', 'points' => 89, 'status' => 'Tracking'],
                                ['id' => 5, 'name' => 'David Lee', 'student_id' => '2021-005', 'program' => 'BTVTED', 'points' => 76, 'status' => 'For Final Review'],
                                ['id' => 6, 'name' => 'Maria Rodriguez', 'student_id' => '2021-006', 'program' => 'BTVTED', 'points' => 94, 'status' => 'SLEA Qualified'],
                                ['id' => 7, 'name' => 'James Wilson', 'student_id' => '2021-007', 'program' => 'BTVTED', 'points' => 82, 'status' => 'Tracking'],
                                ['id' => 8, 'name' => 'Anna Martinez', 'student_id' => '2021-008', 'program' => 'BTVTED', 'points' => 87, 'status' => 'For Final Review'],
                                ['id' => 9, 'name' => 'Robert Kim', 'student_id' => '2021-009', 'program' => 'BTVTED', 'points' => 91, 'status' => 'SLEA Qualified'],
                                ['id' => 10, 'name' => 'Jennifer Chen', 'student_id' => '2021-010', 'program' => 'BTVTED', 'points' => 83, 'status' => 'Tracking']
                                ];
                                @endphp

                                @foreach ($btvtedStudents as $student)
                                <tr>
                                    <td>{{ $student['name'] }}</td>
                                    <td>{{ $student['student_id'] }}</td>
                                    <td>{{ $student['program'] }}</td>
                                    <td>{{ $student['points'] }}/100</td>
                                    <td>
                                        @php
                                        $statusClass = '';
                                        switch($student['status']) {
                                        case 'Tracking':
                                        $statusClass = 'badge--blue';
                                        break;
                                        case 'For Final Review':
                                        $statusClass = 'badge--yellow';
                                        break;
                                        case 'SLEA Qualified':
                                        $statusClass = 'badge--green';
                                        break;
                                        }
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            {{ $student['status'] }}
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <button type="button" class="btn btn-view" onclick="openDocumentModal('{{ $student['id'] }}')">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination for BTVTED --}}
                    <div class="unified-pagination">
                        <button class="btn-nav" disabled>Previous</button>
                        <button class="btn-page active">1</button>
                        <button class="btn-page">2</button>
                        <button class="btn-page">3</button>
                        <button class="btn-nav">Next</button>
                    </div>
                </div>

                <div class="program-section">
                    <h3 class="program-title">BPED Program</h3>
                    <div class="table-wrap">
                        <table class="award-table">
                            <thead>
                                <tr>
                                    <th class="col-name">Student Name</th>
                                    <th class="col-id">Student ID</th>
                                    <th class="col-program">Program</th>
                                    <th class="col-points">Total Points</th>
                                    <th class="col-status">Leadership Status</th>
                                    <th class="col-action">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                // Sample data for BPED - Limited to 10 entries per page
                                $bpedStudents = [
                                ['id' => 11, 'name' => 'Emily Davis', 'student_id' => '2021-011', 'program' => 'BPED', 'points' => 88, 'status' => 'Tracking'],
                                ['id' => 12, 'name' => 'David Miller', 'student_id' => '2021-012', 'program' => 'BPED', 'points' => 95, 'status' => 'SLEA Qualified'],
                                ['id' => 13, 'name' => 'Lisa Anderson', 'student_id' => '2021-013', 'program' => 'BPED', 'points' => 82, 'status' => 'For Final Review'],
                                ['id' => 14, 'name' => 'Mark Thompson', 'student_id' => '2021-014', 'program' => 'BPED', 'points' => 90, 'status' => 'SLEA Qualified'],
                                ['id' => 15, 'name' => 'Rachel Green', 'student_id' => '2021-015', 'program' => 'BPED', 'points' => 79, 'status' => 'Tracking'],
                                ['id' => 16, 'name' => 'Kevin White', 'student_id' => '2021-016', 'program' => 'BPED', 'points' => 86, 'status' => 'For Final Review'],
                                ['id' => 17, 'name' => 'Amanda Taylor', 'student_id' => '2021-017', 'program' => 'BPED', 'points' => 93, 'status' => 'SLEA Qualified'],
                                ['id' => 18, 'name' => 'Chris Johnson', 'student_id' => '2021-018', 'program' => 'BPED', 'points' => 81, 'status' => 'Tracking'],
                                ['id' => 19, 'name' => 'Nicole Brown', 'student_id' => '2021-019', 'program' => 'BPED', 'points' => 89, 'status' => 'For Final Review'],
                                ['id' => 20, 'name' => 'Ryan Davis', 'student_id' => '2021-020', 'program' => 'BPED', 'points' => 84, 'status' => 'Tracking']
                                ];
                                @endphp

                                @foreach ($bpedStudents as $student)
                                <tr>
                                    <td>{{ $student['name'] }}</td>
                                    <td>{{ $student['student_id'] }}</td>
                                    <td>{{ $student['program'] }}</td>
                                    <td>{{ $student['points'] }}/100</td>
                                    <td>
                                        @php
                                        $statusClass = '';
                                        switch($student['status']) {
                                        case 'Tracking':
                                        $statusClass = 'badge--blue';
                                        break;
                                        case 'For Final Review':
                                        $statusClass = 'badge--yellow';
                                        break;
                                        case 'SLEA Qualified':
                                        $statusClass = 'badge--green';
                                        break;
                                        }
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            {{ $student['status'] }}
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <button type="button" class="btn btn-view" onclick="openDocumentModal('{{ $student['id'] }}')">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination for BPED --}}
                    <div class="unified-pagination">
                        <button class="btn-nav" disabled>Previous</button>
                        <button class="btn-page active">1</button>
                        <button class="btn-page">2</button>
                        <button class="btn-page">3</button>
                        <button class="btn-nav">Next</button>
                    </div>
                </div>

                {{-- Action Section --}}
                <div class="action-section">
                    <button type="button" class="btn-export-pdf" onclick="exportReport()">
                        <i class="fas fa-file-pdf"></i> Export as PDF
                    </button>
                    <button type="button" class="btn-generate-preview" onclick="generatePreview()" title="Generate a preview of the award report before final export">
                        <i class="fas fa-eye"></i> Generate Preview
                    </button>
                </div>
            </div>
        </div>

        <!-- Document Review Modal -->
        <div id="documentModal" class="modal">
            <div class="modal-content document-modal">
                <div class="modal-header">
                    <h3>Document Review</h3>
                    <span class="close" onclick="closeDocumentModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="document-details">
                        <div class="detail-section">
                            <h4>Student Information</h4>
                            <div class="detail-row">
                                <span class="detail-label">Name:</span>
                                <span class="detail-value" id="modalStudentName"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Student ID:</span>
                                <span class="detail-value" id="modalStudentId"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Program:</span>
                                <span class="detail-value" id="modalProgram"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">College:</span>
                                <span class="detail-value" id="modalCollege"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Graduation Batch:</span>
                                <span class="detail-value" id="modalBatch"></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Final Qualification Score:</span>
                                <span class="detail-value" id="modalScore"></span>
                            </div>
                        </div>

                        <div class="points-breakdown">
                            <h4>Points Breakdown</h4>
                            <table class="breakdown-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Points Earned</th>
                                        <th>Maximum Points</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Academic Excellence</td>
                                        <td>25</td>
                                        <td>30</td>
                                        <td>83.3%</td>
                                    </tr>
                                    <tr>
                                        <td>Leadership Activities</td>
                                        <td>20</td>
                                        <td>25</td>
                                        <td>80.0%</td>
                                    </tr>
                                    <tr>
                                        <td>Community Service</td>
                                        <td>15</td>
                                        <td>20</td>
                                        <td>75.0%</td>
                                    </tr>
                                    <tr>
                                        <td>Research & Innovation</td>
                                        <td>10</td>
                                        <td>15</td>
                                        <td>66.7%</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td><strong>Total</strong></td>
                                        <td><strong>70</strong></td>
                                        <td><strong>90</strong></td>
                                        <td><strong>77.8%</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-done" onclick="closeDocumentModal()">
                        <i class="fas fa-times"></i> Done
                    </button>
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

                // Clear existing options
                programSelect.innerHTML = '<option value="">All Programs</option>';

                // Add programs based on selected college
                if (selectedCollege && collegePrograms[selectedCollege]) {
                    collegePrograms[selectedCollege].forEach(program => {
                        const option = document.createElement('option');
                        option.value = program;
                        option.textContent = program;
                        programSelect.appendChild(option);
                    });
                }
            }

            function openDocumentModal(studentId) {
                // Sample data - replace with actual data fetching
                const studentData = {
                    1: {
                        name: 'John Smith',
                        studentId: '2021-001',
                        program: 'BTVTED',
                        college: 'College of Education',
                        batch: '2021',
                        score: '85/100'
                    },
                    2: {
                        name: 'Sarah Wilson',
                        studentId: '2021-002',
                        program: 'BTVTED',
                        college: 'College of Education',
                        batch: '2021',
                        score: '92/100'
                    },
                    3: {
                        name: 'Michael Brown',
                        studentId: '2021-003',
                        program: 'BTVTED',
                        college: 'College of Education',
                        batch: '2021',
                        score: '78/100'
                    },
                    4: {
                        name: 'Emily Davis',
                        studentId: '2021-004',
                        program: 'BPED',
                        college: 'College of Education',
                        batch: '2021',
                        score: '88/100'
                    },
                    5: {
                        name: 'David Miller',
                        studentId: '2021-005',
                        program: 'BPED',
                        college: 'College of Education',
                        batch: '2021',
                        score: '95/100'
                    },
                    6: {
                        name: 'Lisa Anderson',
                        studentId: '2021-006',
                        program: 'BPED',
                        college: 'College of Education',
                        batch: '2021',
                        score: '82/100'
                    }
                };

                const student = studentData[studentId];
                if (student) {
                    document.getElementById('modalStudentName').textContent = student.name;
                    document.getElementById('modalStudentId').textContent = student.studentId;
                    document.getElementById('modalProgram').textContent = student.program;
                    document.getElementById('modalCollege').textContent = student.college;
                    document.getElementById('modalBatch').textContent = student.batch;
                    document.getElementById('modalScore').textContent = student.score;

                    document.getElementById('documentModal').style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeDocumentModal() {
                document.getElementById('documentModal').style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            function exportReport() {
                // Sample export functionality - replace with actual export logic
                alert('Export functionality will be implemented here. This would typically generate a PDF file.');
            }

            function generatePreview() {
                // Sample preview functionality - replace with actual preview logic
                alert('Preview functionality will be implemented here. This would typically show a preview of the award report.');
            }

            // Initialize programs on page load
            document.addEventListener('DOMContentLoaded', function() {
                updatePrograms();
            });

            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('documentModal');
                if (event.target === modal) {
                    closeDocumentModal();
                }
            }
        </script>

        {{-- Include Admin Pagination Script --}}
        <script src="{{ asset('js/admin_pagination.js') }}"></script>

</div>
</main>
</div>

@endsection