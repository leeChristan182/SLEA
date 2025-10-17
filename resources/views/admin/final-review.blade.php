@extends('layouts.app')

@section('title', 'Final Review')

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

                <h2 class="manage-title">Final Review</h2>

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

                {{-- Controls: Filter / Sort / Search --}}
                <form class="controls" method="GET" action="{{ route('admin.final-review') }}">
                    <div class="controls-left">
                        <div class="dropdowns">
                            <label>
                                <span>Graduation Batch</span>
                                <select name="batch">
                                    <option value="" @selected(request('batch')==='' )>None</option>
                                    <option value="2024" @selected(request('batch')==='2024' )>2024</option>
                                    <option value="2025" @selected(request('batch')==='2025' )>2025</option>
                                    <option value="2026" @selected(request('batch')==='2026' )>2026</option>
                                </select>
                            </label>

                            <label>
                                <span>Sort by</span>
                                <select name="sort">
                                    <option value="" @selected(request('sort')==='' )>None</option>
                                    <option value="name" @selected(request('sort')==='name' )>Student Name</option>
                                    <option value="id" @selected(request('sort')==='id' )>Student ID</option>
                                    <option value="points" @selected(request('sort')==='points' )>Total Points</option>
                                    <option value="status" @selected(request('sort')==='status' )>Submission Status</option>
                                </select>
                            </label>
                        </div>
                    </div>

                    <div class="search-box">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search...">
                        <button class="search-btn" type="submit" aria-label="Search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-wrap">
                    <table class="review-table">
                        <thead>
                            <tr>
                                <th class="col-name">Student Name</th>
                                <th class="col-id">Student ID</th>
                                <th class="col-points">Total Points</th>
                                <th class="col-status">Submission Status</th>
                                <th class="col-action text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $hasRows = isset($students) && (($students instanceof \Illuminate\Contracts\Pagination\Paginator && $students->count() > 0) || collect($students)->count() > 0);
                            @endphp

                            @if ($hasRows)
                            @foreach ($students ?? [] as $student)
                            <tr>
                                <td>{{ $student->name ?? 'Edryan Manocay' }}</td>
                                <td>{{ $student->student_id ?? '2022-00216' }}</td>
                                <td>{{ $student->total_points ?? '16' }}</td>
                                <td>
                                    <span class="badge {{ $student->status === 'approve' ? 'badge--green' : ($student->status === 'reject' ? 'badge--red' : 'badge--yellow') }}">
                                        {{ ucfirst($student->status ?? 'approve') }}
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-action btn-review" onclick="openReviewModal('{{ $student->id ?? 1 }}')" title="Review Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            {{-- Sample data for demonstration --}}
                            <tr>
                                <td>Edryan Manocay</td>
                                <td>2022-00216</td>
                                <td>16</td>
                                <td>
                                    <span class="badge badge--green">Approve</span>
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-action btn-review" onclick="openReviewModal('1')" title="Review Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Maria Santos</td>
                                <td>2022-00123</td>
                                <td>14</td>
                                <td>
                                    <span class="badge badge--green">Approve</span>
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-action btn-review" onclick="openReviewModal('2')" title="Review Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>John Dela Cruz</td>
                                <td>2022-00124</td>
                                <td>12</td>
                                <td>
                                    <span class="badge badge--yellow">Pending</span>
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-action btn-review" onclick="openReviewModal('3')" title="Review Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Sarah Johnson</td>
                                <td>2022-00125</td>
                                <td>18</td>
                                <td>
                                    <span class="badge badge--green">Approve</span>
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-action btn-review" onclick="openReviewModal('4')" title="Review Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Michael Rodriguez</td>
                                <td>2022-00126</td>
                                <td>10</td>
                                <td>
                                    <span class="badge badge--red">Reject</span>
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-action btn-review" onclick="openReviewModal('5')" title="Review Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Anna Garcia</td>
                                <td>2022-00127</td>
                                <td>15</td>
                                <td>
                                    <span class="badge badge--green">Approve</span>
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-action btn-review" onclick="openReviewModal('6')" title="Review Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>David Kim</td>
                                <td>2022-00128</td>
                                <td>13</td>
                                <td>
                                    <span class="badge badge--yellow">Pending</span>
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-action btn-review" onclick="openReviewModal('7')" title="Review Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Lisa Chen</td>
                                <td>2022-00129</td>
                                <td>17</td>
                                <td>
                                    <span class="badge badge--green">Approve</span>
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-action btn-review" onclick="openReviewModal('8')" title="Review Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Robert Wilson</td>
                                <td>2022-00130</td>
                                <td>9</td>
                                <td>
                                    <span class="badge badge--red">Reject</span>
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-action btn-review" onclick="openReviewModal('9')" title="Review Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Jennifer Lee</td>
                                <td>2022-00131</td>
                                <td>19</td>
                                <td>
                                    <span class="badge badge--green">Approve</span>
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-action btn-review" onclick="openReviewModal('10')" title="Review Submission">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if (isset($students) && method_exists($students, 'links'))
                <div class="unified-pagination">
                    {{ $students->withQueryString()->links() }}
                </div>
                @else
                {{-- Sample pagination for demonstration --}}
                <div class="unified-pagination">
                    <button class="btn-nav" disabled>Previous</button>
                    <button class="btn-page active">1</button>
                    <button class="btn-page">2</button>
                    <button class="btn-page">3</button>
                    <button class="btn-page">4</button>
                    <button class="btn-nav">Next</button>
                </div>
                @endif
            </div>
        </div>

        <!-- Review Modal -->
        <div id="reviewModal" class="modal">
            <div class="modal-content review-modal">
                <div class="modal-header">
                    <h3>Document Review</h3>
                    <span class="close" onclick="closeReviewModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="review-container">
                        <!-- Student Details Section -->
                        <div class="student-details-section">
                            <h4>Student Information</h4>
                            <div class="student-info">
                                <div class="info-row">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value" id="modal-student-name">Edryan Manocay</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Student ID:</span>
                                    <span class="info-value" id="modal-student-id">2022-00216</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Program:</span>
                                    <span class="info-value" id="modal-program">Bachelor of Science in Information Technology</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">College:</span>
                                    <span class="info-value" id="modal-college">College of Computer Studies</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Organization Role:</span>
                                    <span class="info-value" id="modal-role">Student Leader</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Organization Name:</span>
                                    <span class="info-value" id="modal-org">Student Council</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Expected Year to Graduate:</span>
                                    <span class="info-value" id="modal-grad-year">2024</span>
                                </div>
                            </div>

                            <!-- Final Point Summary -->
                            <div class="point-summary-section">
                                <h4>Final Point Summary</h4>
                                <div class="point-summary-table">
                                    <table class="summary-table">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Earned Points</th>
                                                <th>Max Points</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>I. Leadership</td>
                                                <td>20</td>
                                                <td>25</td>
                                            </tr>
                                            <tr>
                                                <td>II. Academic Excellence</td>
                                                <td>20</td>
                                                <td>25</td>
                                            </tr>
                                            <tr>
                                                <td>III. Community Service</td>
                                                <td>10</td>
                                                <td>15</td>
                                            </tr>
                                            <tr>
                                                <td>IV. Extracurricular Activities</td>
                                                <td>10</td>
                                                <td>15</td>
                                            </tr>
                                            <tr>
                                                <td>V. Special Recognition</td>
                                                <td>3</td>
                                                <td>10</td>
                                            </tr>
                                            <tr class="total-row">
                                                <td><strong>Total Score</strong></td>
                                                <td><strong>63</strong></td>
                                                <td><strong>90</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Remarks Section -->
                            <div class="remarks-section">
                                <h4>Remarks</h4>
                                <textarea id="review-remarks" class="remarks-textarea" placeholder="Enter your remarks here..."></textarea>
                            </div>

                            <!-- Action Buttons -->
                            <div class="review-actions">
                                <button type="button" class="btn btn-approve-qualification" onclick="approveQualification()">
                                    <i class="fas fa-check"></i> Approve Qualification
                                </button>
                                <button type="button" class="btn btn-reject-qualification" onclick="rejectQualification()">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div id="successModal" class="modal fade" style="display: none;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content success-modal-content">
                    <div class="modal-body text-center p-4">
                        <div class="success-icon mb-3">
                            <i class="fas fa-check-circle" style="color: #28a745; font-size: 3rem;"></i>
                        </div>
                        <h5 class="success-title mb-3">Success!</h5>
                        <p class="success-message mb-4" id="successMessage">Operation completed successfully!</p>
                        <button type="button" class="btn btn-success" onclick="closeSuccessModal()">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let currentStudentId = '';

            function openReviewModal(studentId) {
                currentStudentId = studentId;

                // Show the modal
                document.getElementById('reviewModal').style.display = 'block';
                document.body.style.overflow = 'hidden';

                // Load student data (in real app, fetch from server)
                loadStudentData(studentId);
            }

            function closeReviewModal() {
                document.getElementById('reviewModal').style.display = 'none';
                document.body.style.overflow = 'auto';
                currentStudentId = '';
            }

            function loadStudentData(studentId) {
                // In a real application, you would fetch student data from the server
                // For now, we'll use sample data
                console.log('Loading data for student ID:', studentId);

                // Update student information
                document.getElementById('modal-student-name').textContent = 'Edryan Manocay';
                document.getElementById('modal-student-id').textContent = '2022-00216';
                document.getElementById('modal-program').textContent = 'Bachelor of Science in Information Technology';
                document.getElementById('modal-college').textContent = 'College of Computer Studies';
                document.getElementById('modal-role').textContent = 'Student Leader';
                document.getElementById('modal-org').textContent = 'Student Council';
                document.getElementById('modal-grad-year').textContent = '2024';
            }

            function approveQualification() {
                const remarks = document.getElementById('review-remarks').value;

                // In a real application, you would send an AJAX request to the server
                console.log('Approving qualification for student:', currentStudentId);
                console.log('Remarks:', remarks);

                // Close review modal first
                closeReviewModal();

                // Show custom success modal
                showSuccessModal('Qualification approved successfully!');
            }

            function rejectQualification() {
                const remarks = document.getElementById('review-remarks').value;

                // In a real application, you would send an AJAX request to the server
                console.log('Rejecting qualification for student:', currentStudentId);
                console.log('Remarks:', remarks);

                // Close review modal first
                closeReviewModal();

                // Show custom success modal
                showSuccessModal('Qualification rejected successfully!');
            }

            // Success Modal Functions
            function showSuccessModal(message) {
                document.getElementById('successMessage').textContent = message;
                const modal = document.getElementById('successModal');
                modal.style.display = 'block';
                modal.classList.add('show');
                document.body.classList.add('modal-open');
            }

            function closeSuccessModal() {
                const modal = document.getElementById('successModal');
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
            }

            // Close modal when clicking outside of it
            window.onclick = function(event) {
                const reviewModal = document.getElementById('reviewModal');
                const successModal = document.getElementById('successModal');

                if (event.target === reviewModal) {
                    closeReviewModal();
                }

                if (event.target === successModal) {
                    closeSuccessModal();
                }
            }
        </script>

</div>
</main>
</div>

@endsection