@extends('layouts.app')
@section('title', 'Submit Record')

@section('content')
<div class="submit-record-page">
    <div class="container submit-record-container">
        @include('partials.sidebar')

        <main class="main-content">
            <form
                id="submitForm"
                class="sr-form"
                method="POST"
                action="{{ route('student.submissions.store') }}"
                enctype="multipart/form-data"
                onsubmit="return false;">
                @csrf

                <!-- Step Header / Progress -->
                <div class="sr-steps-header sr-card">
                    <div class="sr-step-indicator-item active" data-step="1">
                        <div class="sr-step-number">1</div>
                        <div class="sr-step-label">SLEA Classification</div>
                    </div>
                    <div class="sr-step-indicator-item" data-step="2">
                        <div class="sr-step-number">2</div>
                        <div class="sr-step-label">Activity Details</div>
                    </div>
                    <div class="sr-step-indicator-item" data-step="3">
                        <div class="sr-step-number">3</div>
                        <div class="sr-step-label">Upload & Submit</div>
                    </div>
                </div>

                <!-- STEP 1: SLEA CLASSIFICATION FIRST -->
                <section class="sr-card sr-step sr-step-active" data-step="1">
                    <h3>SLEA Classification</h3>
                    <div class="sr-grid">
                        <div class="sr-field">
                            <label for="docType">Document Type</label>
                            <select id="docType" name="document_type">
                                <option value="">Select document type</option>
                                <option value="certificate">Certificate</option>
                                <option value="appointment">Appointment</option>
                                <option value="moa">Memorandum of Agreement</option>
                                <option value="training">Training / Seminar</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        {{-- Rubric Category --}}
                        <div class="sr-field">
                            <label for="sleacat">SLEA Category <span style="color: red;">*</span></label>
                            <select id="sleacat" name="rubric_category_id" required>
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Rubric Section --}}
                        <div class="sr-field">
                            <label for="sectionSelect">Section</label>
                            <select id="sectionSelect" name="rubric_section_id" disabled>
                                <option value="">Select section</option>
                            </select>
                        </div>

                        {{-- Rubric Subsection --}}
                        <div class="sr-field">
                            <label for="subSection">Subsection</label>
                            <select id="subSection" name="rubric_subsection_id" disabled>
                                <option value="">Select subsection</option>
                            </select>
                        </div>

                        {{-- Cluster and Organization (shown only for Student Clubs and Organizations) --}}
                        <div class="sr-field" id="clusterField" style="display: none;">
                            <label for="clusterSelect">Cluster <span style="color: red;">*</span></label>
                            <select id="clusterSelect" name="cluster_id">
                                <option value="">Select Cluster</option>
                            </select>
                        </div>

                        <div class="sr-field" id="organizationField" style="display: none;">
                            <label for="organizationSelect">Organization <span style="color: red;">*</span></label>
                            <select id="organizationSelect" name="organization_id">
                                <option value="">Select Organization</option>
                            </select>
                        </div>
                    </div>

                    <div class="sr-actions sr-actions-steps">
                        <button type="button" class="sr-btn sr-btn-primary" id="btnStep1Next">
                            Next: Activity Details
                        </button>
                    </div>
                </section>

                <!-- STEP 2: ACTIVITY DETAILS SECOND -->
                <section class="sr-card sr-step" data-step="2">
                    <h3>Activity Details</h3>
                    <div class="sr-grid">
                        <div class="sr-field">
                            <label for="title">Title of Activity <span style="color:red">*</span></label>
                            <input
                                id="title"
                                name="activity_title"
                                type="text"
                                placeholder="e.g., Leadership Training"
                                required>
                        </div>
                        <div class="sr-field">
                            <label for="type">Type of Activity</label>
                            <input
                                id="type"
                                name="activity_type"
                                type="text"
                                placeholder="e.g., Seminar / Workshop">
                        </div>
                        <div class="sr-field">
                            <label for="role">Role in Activity</label>
                            {{-- Dropdown for SCO and Community Based subsections --}}
                            <select
                                id="role"
                                name="role_in_activity"
                                style="display: none;">
                                <option value="">Select Position</option>
                            </select>
                            {{-- Text field for Trainings/Seminars --}}
                            <input
                                id="roleText"
                                name="role_in_activity_text"
                                type="text"
                                placeholder="e.g., Participant / Speaker"
                                style="display: none;">
                        </div>
                        <div class="sr-field">
                            <label for="date">Date of Activity</label>
                            <input
                                id="date"
                                name="date_of_activity"
                                type="date">
                        </div>
                        <div class="sr-field">
                            <label for="orgBody">Organizing Body</label>
                            <input
                                id="orgBody"
                                name="organizing_body"
                                type="text"
                                placeholder="e.g., OSAS">
                        </div>
                        <div class="sr-field">
                            <label for="note">Note (optional)</label>
                            <input
                                id="note"
                                name="note"
                                type="text"
                                placeholder="Any additional info">
                        </div>
                        <div class="sr-field">
                            <label for="term">Term (School Year)</label>
                            <input
                                id="term"
                                name="term"
                                type="text"
                                placeholder="2023 - 2024"
                                pattern="^20\d{2}\s-\s20\d{2}$"
                                title="Format: YYYY - YYYY (e.g., 2023 - 2024). Include spaces before and after the hyphen.">
                            <small class="sr-field-hint" style="display: block; margin-top: 4px; color: #666; font-size: 0.875rem;">
                                Format: YYYY - YYYY (e.g., 2023 - 2024)
                            </small>
                        </div>
                        <div class="sr-field">
                            <label for="issuedBy">Issued by</label>
                            <input
                                id="issuedBy"
                                name="issued_by"
                                type="text"
                                placeholder="e.g., OSAS">
                        </div>
                        <div class="sr-field">
                            <label for="applicationStatus">Application Status <span style="color:red">*</span></label>
                            <select
                                id="applicationStatus"
                                name="application_status"
                                required>
                                <option value="">Select application status</option>
                                <option value="for_final_application">For Final Application</option>
                                <option value="for_tracking">For Tracking</option>
                            </select>
                        </div>
                    </div>

                    <div class="sr-actions sr-actions-steps">
                        <button type="button" class="sr-btn sr-btn-ghost" id="btnStep2Prev">
                            Back to SLEA Classification
                        </button>
                        <button type="button" class="sr-btn sr-btn-primary" id="btnStep2Next">
                            Next: Upload & Submit
                        </button>
                    </div>
                </section>

                <!-- STEP 3: UPLOAD + SUBMISSION LAST -->
                <section class="sr-card sr-step" data-step="3">
                    <h3>Upload Documents & Submit</h3>

                    <!-- Upload Dropzone (last step) -->
                    <section class="sr-card sr-drop sr-drop-inner">
                        <input
                            id="fileInput"
                            name="attachments[]"
                            type="file"
                            accept=".jpg,.jpeg,.png,.pdf"
                            multiple
                            hidden>
                        <div id="dropzone" class="sr-dropzone" role="button" tabindex="0" aria-label="Click to upload">
                            <i class="fa-solid fa-upload"></i>
                            <div class="sr-drop-title">Upload your supporting documents here</div>
                            <div class="sr-drop-sub">
                                JPEG, PNG, or PDF • Maximum size: 5MB each
                            </div>
                            <div class="sr-drop-hint">
                                <strong>Rename your file using this format:</strong><br>
                                <code>TitleOfActivity_DocumentType_Lastname</code><br>
                                <small>Example: <strong>LeadershipTraining2024_CertificateOfParticipation_DelaCruz</strong></small>
                            </div>
                        </div>
                        <ul id="fileList" class="sr-filelist"></ul>
                    </section>

                    <div class="sr-actions">
                        <button type="button" class="sr-btn sr-btn-ghost" id="btnStep3Prev">
                            Back to Activity Details
                        </button>
                        <button type="button" class="sr-btn sr-btn-primary" id="btnProceed">
                            Review & Proceed
                        </button>
                    </div>
                </section>
            </form>

            <!-- Draft Modal -->
            <div id="modalDraft" class="sr-modal" aria-hidden="true">
                <div class="sr-modal-backdrop"></div>
                <div class="sr-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="draftTitle">
                    <div class="sr-modal-body">
                        <h4 id="draftTitle" class="sr-modal-title">Draft</h4>
                        <p class="sr-modal-subtitle">Please review your submission!</p>
                        <ul id="draftList" class="sr-draft-list"></ul>
                        <div class="sr-modal-actions">
                            <button class="sr-btn sr-btn-primary" id="btnSubmitDraft">Submit</button>
                            <button class="sr-btn sr-btn-ghost" data-close="modalDraft">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirm Modal -->
            <div id="modalConfirm" class="sr-modal" aria-hidden="true">
                <div class="sr-modal-backdrop"></div>
                <div class="sr-modal-dialog sr-modal-sm" role="dialog" aria-modal="true">
                    <div class="sr-modal-body">
                        <p class="sr-confirm-text">Are you sure you want to submit?</p>
                        <div class="sr-modal-actions">
                            <button class="sr-btn sr-btn-primary" id="btnConfirmOk">Okay</button>
                            <button class="sr-btn sr-btn-ghost" data-close="modalConfirm">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Modal -->
            <div id="modalSuccess" class="sr-modal" aria-hidden="true">
                <div class="sr-modal-backdrop"></div>
                <div class="sr-modal-dialog sr-modal-sm" role="alertdialog" aria-modal="true">
                    <div class="sr-modal-body sr-success">
                        <div class="sr-success-title">Submitted<br>Successfully!</div>
                        <div class="sr-success-icon"><i class="fa-solid fa-check"></i></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- SCOPED CSS + JS (inline so it always loads) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

<style>
    /* Layout wrapper (sidebar + main) */
    .submit-record-page .submit-record-container {
        display: flex;
        gap: 18px;
        align-items: flex-start;
    }

    .submit-record-page .main-content {
        flex: 1 1 auto;
        min-width: 0;
    }

    .submit-record-page .sr-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 1px 6px rgba(0, 0, 0, .06);
        overflow: visible;
    }

    body.dark-mode .submit-record-page .sr-card {
        background: #333;
        border-color: #555;
        color: #f1f1f1;
    }

    /* Steps header */
    .submit-record-page .sr-steps-header {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 16px;
    }

    .submit-record-page .sr-step-indicator-item {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 999px;
        border: 1px solid #d1d5db;
        background: #f9fafb;
        font-size: 13px;
    }

    .submit-record-page .sr-step-number {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        background: #e5e7eb;
        color: #374151;
    }

    .submit-record-page .sr-step-label {
        font-weight: 600;
        color: #374151;
    }

    .submit-record-page .sr-step-indicator-item.active .sr-step-number {
        background: #7b0000;
        color: #fff;
    }

    .submit-record-page .sr-step-indicator-item.completed {
        border-color: #7b0000;
    }

    .submit-record-page .sr-step-indicator-item.completed .sr-step-number {
        background: #16a34a;
        color: #fff;
    }

    .submit-record-page .sr-step-indicator-item.completed .sr-step-label {
        color: #16a34a;
    }

    /* Steps */
    .submit-record-page .sr-step {
        display: none;
        margin-bottom: 16px;
    }

    .submit-record-page .sr-step-active {
        display: block;
    }

    .submit-record-page .sr-drop-inner {
        padding: 0;
        margin-bottom: 16px;
        box-shadow: none;
        border: none;
    }

    .submit-record-page .sr-dropzone {
        padding: 28px 16px;
        border: 2px dashed #7b0000;
        border-radius: 14px;
        text-align: center;
        cursor: pointer;
        background: #fff7f7;
    }

    .submit-record-page .sr-dropzone i {
        font-size: 24px;
        margin-bottom: 6px;
    }

    .submit-record-page .sr-drop-title {
        margin-top: 6px;
        font-weight: 800;
        color: #111827;
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .submit-record-page .sr-drop-sub {
        font-size: 14px;
        color: #1f2933;
        margin-top: 6px;
        font-weight: 600;
    }

    .submit-record-page .sr-drop-hint {
        margin-top: 12px;
        font-size: 13px;
        color: #111827;
        line-height: 1.5;
    }

    .submit-record-page .sr-drop-hint code {
        display: inline-block;
        padding: 3px 6px;
        border-radius: 6px;
        background: #111827;
        color: #f9fafb;
        font-size: 12px;
        margin: 4px 0;
    }

    .submit-record-page .sr-dropzone.sr-drag {
        background: #fef2f2;
        border-color: #b91c1c;
    }

    body.dark-mode .submit-record-page .sr-dropzone {
        background: #3b1b1b;
        border-color: #fca5a5;
    }

    body.dark-mode .submit-record-page .sr-drop-title,
    body.dark-mode .submit-record-page .sr-drop-sub,
    body.dark-mode .submit-record-page .sr-drop-hint {
        color: #f9fafb;
    }

    body.dark-mode .submit-record-page .sr-drop-hint code {
        background: #f9fafb;
        color: #111827;
    }

    .submit-record-page .sr-filelist {
        list-style: none;
        margin: 12px 0 0;
        padding: 0;
    }

    .submit-record-page .sr-filelist li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 10px;
        border: 1px dashed #ddd;
        border-radius: 8px;
        margin-top: 8px;
        font-size: 14px;
        word-break: break-all;
    }

    .submit-record-page .sr-remove {
        border: none;
        background: #8B0000;
        color: #fff;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
    }

    .submit-record-page .sr-remove:hover {
        background: #6B0000;
    }

    .submit-record-page .sr-form h3 {
        margin-bottom: 10px;
        color: #7b0000;
    }

    .submit-record-page .sr-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(2, minmax(240px, 1fr));
    }

    .submit-record-page .sr-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .submit-record-page .sr-field input,
    .submit-record-page .sr-field select {
        height: 42px;
        border-radius: 10px;
        border: 1px solid #ccd1d7;
        padding: 0 12px;
        background: #fff;
        color: #111;
        width: 100%;
    }

    .submit-record-page .sr-field select option {
        white-space: normal;
    }

    body.dark-mode .submit-record-page .sr-field input,
    body.dark-mode .submit-record-page .sr-field select {
        background: #262626;
        color: #fff;
        border-color: #666;
    }

    .submit-record-page .sr-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-start;
        margin-top: 18px;
        flex-wrap: wrap;
    }

    .submit-record-page .sr-actions-steps {
        justify-content: flex-end;
    }

    .submit-record-page .sr-btn {
        border: none;
        border-radius: 22px;
        padding: 10px 18px;
        cursor: pointer;
        font-weight: 600;
        white-space: nowrap;
    }

    .submit-record-page .sr-btn-primary {
        background: #8B0000;
        color: #fff;
    }

    .submit-record-page .sr-btn-primary:hover {
        background: #6B0000;
    }

    .submit-record-page .sr-btn-ghost {
        background: #e9ecef;
        color: #333;
    }

    body.dark-mode .submit-record-page .sr-btn-ghost {
        background: #555;
        color: #f0f0f0;
    }

    /* Modals */
    .submit-record-page .sr-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 3000;
    }
    
    /* Modal z-index hierarchy - ensure success modal is always on top */
    .submit-record-page .sr-modal {
        z-index: 3000;
    }
    
    .submit-record-page #modalDraft {
        z-index: 3000;
    }
    
    .submit-record-page #modalConfirm {
        z-index: 3500;
    }
    
    /* Success modal should appear on top of all other modals */
    .submit-record-page #modalSuccess {
        z-index: 4000 !important;
    }
    
    .submit-record-page #modalSuccess .sr-modal-backdrop {
        z-index: 3999;
    }
    
    .submit-record-page #modalSuccess .sr-modal-dialog {
        position: relative;
        z-index: 4001;
    }

    .submit-record-page .sr-modal[aria-hidden="false"] {
        display: block;
    }

    .submit-record-page .sr-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .4);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    .submit-record-page .sr-modal-dialog {
        position: relative;
        background: #fff;
        border: none;
        border-radius: 12px;
        margin: 100px auto 0;
        width: min(720px, 92%);
        box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
    }

    .submit-record-page .sr-modal-sm {
        width: min(420px, 92%);
    }

    body.dark-mode .submit-record-page .sr-modal-dialog {
        background: #2e2e2e;
        border: none;
        color: #fff;
    }

    .submit-record-page .sr-modal-body {
        padding: 22px 24px;
    }

    .submit-record-page .sr-modal-title {
        text-align: center;
        color: #8B0000;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .submit-record-page .sr-modal-subtitle {
        text-align: center;
        margin-bottom: 14px;
        color: #444;
    }

    body.dark-mode .submit-record-page .sr-modal-subtitle {
        color: #ddd;
    }

    .submit-record-page .sr-draft-list {
        list-style: none;
        margin: 10px 0 14px;
        padding: 0;
        border: 2px dotted #cfcfcf;
        border-radius: 8px;
        max-height: 260px;
        overflow-y: auto;
    }

    .submit-record-page .sr-draft-list li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-bottom: 1px dashed #d9d9d9;
        gap: 8px;
    }

    .submit-record-page .sr-draft-list li:last-child {
        border-bottom: none;
    }

    .submit-record-page .sr-draft-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .submit-record-page .sr-pill {
        border: none;
        border-radius: 16px;
        padding: 6px 12px;
        font-size: 13px;
        cursor: pointer;
    }

    .submit-record-page .sr-pill-edit {
        background: #8B0000;
        color: #fff;
    }

    .submit-record-page .sr-pill-edit:hover {
        background: #6B0000;
    }

    .submit-record-page .sr-pill-x {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #8B0000;
        color: #fff;
    }

    .submit-record-page .sr-pill-x:hover {
        background: #6B0000;
        color: #fff;
    }

    .submit-record-page .sr-confirm-text {
        text-align: center;
        font-weight: 700;
        margin: 8px 0 16px;
    }

    .submit-record-page .sr-success {
        text-align: center;
        padding: 28px 20px;
    }

    .submit-record-page .sr-success-title {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .submit-record-page .sr-success-icon {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #2ecc71;
        color: #fff;
        font-size: 26px;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .submit-record-page .submit-record-container {
            flex-direction: column;
        }

        .submit-record-page .sr-grid {
            grid-template-columns: 1fr;
        }

        .submit-record-page .sr-card {
            padding: 14px;
        }

        .submit-record-page .sr-steps-header {
            flex-direction: column;
        }
    }

    @media (max-width: 576px) {
        .submit-record-page .sr-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .submit-record-page .sr-btn {
            width: 100%;
            text-align: center;
        }
    }

    /* SweetAlert2 Modal Styling for Submit Page */
    .submit-error-modal {
        border-radius: 0 !important;
        -webkit-border-radius: 0 !important;
        -moz-border-radius: 0 !important;
    }

    .submit-error-backdrop,
    .swal2-backdrop-show {
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (() => {
        // Helper function to show error modals with consistent styling
        function showErrorModal(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#7E0308',
                    customClass: {
                        popup: 'submit-error-modal',
                        backdrop: 'submit-error-backdrop'
                    },
                    didOpen: () => {
                        const backdrop = document.querySelector('.swal2-backdrop-show');
                        if (backdrop) {
                            backdrop.style.backdropFilter = 'blur(10px)';
                            backdrop.style.webkitBackdropFilter = 'blur(10px)';
                            backdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                        }
                    }
                });
            } else {
                // Fallback to native alert if SweetAlert2 is not loaded
                alert(message);
            }
        }
        // ======== RUBRIC DROPDOWNS (Category → Section → Subsection) ========
        const sleaDataRaw = @json($categories);
        const sleaData = Array.isArray(sleaDataRaw) ? sleaDataRaw : Object.values(sleaDataRaw || {});

        console.log('SLEA raw data:', sleaDataRaw);
        console.log('SLEA normalized data:', sleaData);

        const sleaCatSelect = document.getElementById('sleacat');
        const sleaSectionSelect = document.getElementById('sectionSelect');
        const sleaSubSelect = document.getElementById('subSection');

        const clearSelect = (select, placeholder) => {
            select.innerHTML = '';
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = placeholder;
            select.appendChild(opt);
        };

        const populateSections = (categoryId) => {
            console.log('populateSections for categoryId =', categoryId);

            clearSelect(sleaSectionSelect, 'Select section');
            clearSelect(sleaSubSelect, 'Select subsection');
            sleaSectionSelect.disabled = true;
            sleaSubSelect.disabled = true;

            if (!categoryId) return;
            if (!Array.isArray(sleaData)) {
                console.warn('sleaData is not an array', sleaData);
                return;
            }

            const cat = sleaData.find(c => String(c.id) === String(categoryId));
            console.log('Selected category object:', cat);

            if (!cat || !Array.isArray(cat.sections) || !cat.sections.length) {
                console.warn('No sections for this category');
                return;
            }

            cat.sections.forEach(sec => {
                const opt = document.createElement('option');
                opt.value = sec.section_id; // PK in rubric_sections
                opt.textContent = sec.title; // label
                sleaSectionSelect.appendChild(opt);
            });

            sleaSectionSelect.disabled = false;
        };

        const populateSubsections = (categoryId, sectionId) => {
            console.log('populateSubsections for categoryId =', categoryId, 'sectionId =', sectionId);

            clearSelect(sleaSubSelect, 'Select subsection');
            sleaSubSelect.disabled = true;

            if (!categoryId || !sectionId) return;
            if (!Array.isArray(sleaData)) {
                console.warn('sleaData is not an array', sleaData);
                return;
            }

            const cat = sleaData.find(c => String(c.id) === String(categoryId));
            if (!cat || !Array.isArray(cat.sections)) {
                console.warn('No sections in category');
                return;
            }

            const sec = cat.sections.find(s => String(s.section_id) === String(sectionId));
            console.log('Selected section object:', sec);

            if (!sec || !Array.isArray(sec.subsections) || !sec.subsections.length) {
                console.warn('No subsections for this section');
                return;
            }

            sec.subsections.forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub.sub_section_id; // PK in rubric_subsections
                opt.textContent = sub.sub_section;
                opt.dataset.key = sub.key || ''; // Store subsection key for mapping
                sleaSubSelect.appendChild(opt);
            });

            sleaSubSelect.disabled = false;
        };

        if (sleaCatSelect) {
            sleaCatSelect.addEventListener('change', () => {
                try {
                    populateSections(sleaCatSelect.value);
                } catch (err) {
                    console.error('Error in populateSections:', err);
                }
            });
        }

        if (sleaSectionSelect) {
            sleaSectionSelect.addEventListener('change', () => {
                try {
                    populateSubsections(sleaCatSelect.value, sleaSectionSelect.value);
                    // Reset cluster/org fields when section changes
                    handleSubsectionChange();
                } catch (err) {
                    console.error('Error in populateSubsections:', err);
                }
            });
        }

        // ======== CLUSTER, ORGANIZATION, AND ROLE IN ACTIVITY LOGIC ========
        const clusterField = document.getElementById('clusterField');
        const organizationField = document.getElementById('organizationField');
        const clusterSelect = document.getElementById('clusterSelect');
        const organizationSelect = document.getElementById('organizationSelect');
        const roleSelect = document.getElementById('role');
        const roleText = document.getElementById('roleText');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // Mapping subsection keys to leadership type keys
        const subsectionToLeadershipType = {
            'leadership.campus_government.student_orgs': 'sco',
            'leadership.community_based.lgu': 'lgu',
            'leadership.community_based.non_lgu': 'lgu', // Use lgu for non-lgu as well
        };

        // Check if subsection is a training/seminar subsection
        const isTrainingSubsection = (key) => {
            return key && key.startsWith('leadership.trainings.');
        };

        // Check if subsection requires cluster/organization
        const requiresClusterOrg = (key) => {
            return key === 'leadership.campus_government.student_orgs';
        };

        // Check if subsection is campus-based (uses rubric_options instead of positions table)
        const isCampusBasedSubsection = (key) => {
            return key && key.startsWith('leadership.campus_government.');
        };

        // Load clusters
        async function loadClusters() {
            if (!clusterSelect) return;
            clearSelect(clusterSelect, 'Loading clusters...');
            
            try {
                const response = await fetch('{{ route("ajax.clusters") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const clusters = await response.json();
                
                clearSelect(clusterSelect, 'Select Cluster');
                clusters.forEach(cluster => {
                    const opt = document.createElement('option');
                    opt.value = cluster.id;
                    opt.textContent = cluster.name;
                    clusterSelect.appendChild(opt);
                });
            } catch (err) {
                console.error('Error loading clusters:', err);
                clearSelect(clusterSelect, 'Error loading clusters');
            }
        }

        // Load organizations based on cluster
        async function loadOrganizations(clusterId) {
            if (!organizationSelect) return;
            clearSelect(organizationSelect, 'Loading organizations...');
            
            if (!clusterId) {
                clearSelect(organizationSelect, 'Select Organization');
                return;
            }

            try {
                const response = await fetch(`{{ route("ajax.organizations") }}?cluster_id=${clusterId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const orgs = await response.json();
                
                clearSelect(organizationSelect, 'Select Organization');
                orgs.forEach(org => {
                    const opt = document.createElement('option');
                    opt.value = org.id;
                    opt.textContent = org.name;
                    organizationSelect.appendChild(opt);
                });
            } catch (err) {
                console.error('Error loading organizations:', err);
                clearSelect(organizationSelect, 'Error loading organizations');
            }
        }

        // Load positions based on leadership type or subsection
        async function loadPositions(leadershipTypeKey, subsectionId = null) {
            if (!roleSelect) return;
            clearSelect(roleSelect, 'Loading positions...');

            try {
                let positions = [];

                // If subsection ID is provided, load from rubric_options (for campus-based subsections)
                if (subsectionId) {
                    const posResponse = await fetch(`{{ route("ajax.rubric.options") }}?subsection_id=${subsectionId}&_=${Date.now()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    positions = await posResponse.json();
                } else if (leadershipTypeKey) {
                    // Otherwise, use the existing logic for leadership type-based positions
                    const ltResponse = await fetch('{{ route("ajax.leadership.types") }}?_=' + Date.now(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    const leadershipTypes = await ltResponse.json();
                    const leadershipType = leadershipTypes.find(lt => lt.key === leadershipTypeKey);
                    
                    if (!leadershipType) {
                        clearSelect(roleSelect, 'No positions available');
                        return;
                    }

                    // Get positions for this leadership type
                    const posResponse = await fetch(`{{ route("ajax.council.positions") }}?leadership_type_id=${leadershipType.id}&_=${Date.now()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    positions = await posResponse.json();
                } else {
                    clearSelect(roleSelect, 'Select Position');
                    return;
                }
                
                clearSelect(roleSelect, 'Select Position');
                positions.forEach(pos => {
                    const opt = document.createElement('option');
                    opt.value = pos.id;
                    opt.textContent = pos.name;
                    roleSelect.appendChild(opt);
                });
            } catch (err) {
                console.error('Error loading positions:', err);
                clearSelect(roleSelect, 'Error loading positions');
            }
        }

        // Handle subsection change
        function handleSubsectionChange() {
            if (!sleaSubSelect) return;
            
            const selectedOption = sleaSubSelect.options[sleaSubSelect.selectedIndex];
            const subsectionKey = selectedOption?.dataset.key || '';
            const subsectionId = sleaSubSelect.value || null;
            
            // Handle cluster/organization fields for SCO
            if (requiresClusterOrg(subsectionKey)) {
                clusterField.style.display = '';
                organizationField.style.display = '';
                clusterSelect.required = true;
                organizationSelect.required = true;
                loadClusters();
            } else {
                clusterField.style.display = 'none';
                organizationField.style.display = 'none';
                clusterSelect.required = false;
                organizationSelect.required = false;
                clusterSelect.value = '';
                organizationSelect.value = '';
            }

            // Handle role in activity field
            if (isTrainingSubsection(subsectionKey)) {
                // Show text field for trainings/seminars
                roleSelect.style.display = 'none';
                roleText.style.display = '';
                roleSelect.required = false;
                roleText.required = false;
                roleSelect.value = '';
            } else if (isCampusBasedSubsection(subsectionKey)) {
                // Show dropdown for campus-based subsections (load from rubric_options)
                roleSelect.style.display = '';
                roleText.style.display = 'none';
                roleSelect.required = true;
                roleText.required = false;
                roleText.value = '';
                // Load positions from rubric_options using subsection ID
                loadPositions(null, subsectionId);
            } else if (subsectionToLeadershipType[subsectionKey]) {
                // Show dropdown for SCO and Community Based (load from positions table)
                roleSelect.style.display = '';
                roleText.style.display = 'none';
                roleSelect.required = true;
                roleText.required = false;
                roleText.value = '';
                loadPositions(subsectionToLeadershipType[subsectionKey]);
            } else {
                // Default: show text field
                roleSelect.style.display = 'none';
                roleText.style.display = '';
                roleSelect.required = false;
                roleText.required = false;
                roleSelect.value = '';
            }
        }

        // Event listeners
        sleaSubSelect?.addEventListener('change', handleSubsectionChange);
        
        clusterSelect?.addEventListener('change', () => {
            loadOrganizations(clusterSelect.value);
        });

        // ======== STEP WIZARD LOGIC ========
        const steps = Array.from(document.querySelectorAll('.sr-step'));
        const stepIndicators = Array.from(document.querySelectorAll('.sr-step-indicator-item'));
        let currentStep = 1;

        const showStep = (step) => {
            currentStep = step;
            steps.forEach(s => {
                s.classList.toggle('sr-step-active', Number(s.dataset.step) === step);
            });
            stepIndicators.forEach(ind => {
                const s = Number(ind.dataset.step);
                ind.classList.toggle('active', s === step);
                ind.classList.toggle('completed', s < step);
            });
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        };

        const btnStep1Next = document.getElementById('btnStep1Next');
        const btnStep2Prev = document.getElementById('btnStep2Prev');
        const btnStep2Next = document.getElementById('btnStep2Next');
        const btnStep3Prev = document.getElementById('btnStep3Prev');

        btnStep1Next?.addEventListener('click', () => {
            const category = sleaCatSelect.value;
            if (!category) {
                showErrorModal('Please select a SLEA category before continuing.');
                return;
            }
            showStep(2);
        });

        btnStep2Prev?.addEventListener('click', () => showStep(1));

        btnStep2Next?.addEventListener('click', () => {
            const title = document.getElementById('title').value.trim();
            if (!title) {
                showErrorModal('Please enter the title of activity before continuing.');
                return;
            }
            showStep(3);
        });

        btnStep3Prev?.addEventListener('click', () => showStep(2));

        // ======== FILE + MODALS & SUBMIT LOGIC ========
        let files = [];
        const maxSize = 5 * 1024 * 1024;
        const acceptExt = /\.(pdf|jpg|jpeg|png)$/i;

        const fileInput = document.getElementById('fileInput');
        const dropzone = document.getElementById('dropzone');
        const fileList = document.getElementById('fileList');

        const btnProceed = document.getElementById('btnProceed');

        const modalDraft = document.getElementById('modalDraft');
        const modalConfirm = document.getElementById('modalConfirm');
        const modalSuccess = document.getElementById('modalSuccess');

        const draftList = document.getElementById('draftList');
        const btnSubmitDraft = document.getElementById('btnSubmitDraft');
        const btnConfirmOk = document.getElementById('btnConfirmOk');

        const openModal = (el) => {
            if (!el) return;
            // Close all other modals first
            [modalDraft, modalConfirm, modalSuccess].forEach(modal => {
                if (modal && modal !== el) {
                    modal.setAttribute('aria-hidden', 'true');
                }
            });
            // Then open the requested modal
            el.setAttribute('aria-hidden', 'false');
            // Ensure it's on top by updating z-index
            if (el.id === 'modalSuccess') {
                el.style.zIndex = '4000';
            } else if (el.id === 'modalConfirm') {
                el.style.zIndex = '3500';
            } else {
                el.style.zIndex = '3000';
            }
        };
        
        const closeModal = (el) => {
            if (!el) return;
            el.setAttribute('aria-hidden', 'true');
        };

        const renderQuickList = () => {
            fileList.innerHTML = files.map((f, i) => `
                <li>
                    <span>${f.name}</span>
                    <button class="sr-remove" data-remove="${i}" title="Remove">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </li>
            `).join('');
        };

        const renderDraftList = () => {
            if (!files.length) {
                draftList.innerHTML = `<li><em>No files added yet.</em></li>`;
                return;
            }
            draftList.innerHTML = files.map((f, i) => `
                <li>
                    <span>${f.name}</span>
                    <div class="sr-draft-actions">
                        <button class="sr-pill sr-pill-x" data-remove="${i}" title="Remove">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <button class="sr-pill sr-pill-edit" data-edit="${i}">Edit</button>
                    </div>
                </li>
            `).join('');
        };

        const tryAddFile = (f) => {
                if (!acceptExt.test(f.name)) {
                    showErrorModal('Only JPG, PNG, or PDF allowed.');
                    return;
                }
                if (f.size > maxSize) {
                    showErrorModal('File exceeds 5MB.');
                    return;
                }
            files.push({
                name: f.name,
                file: f
            });
        };

        dropzone.addEventListener('click', () => fileInput.click());
        dropzone.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') fileInput.click();
        });

        ['dragenter', 'dragover'].forEach(evt => {
            dropzone.addEventListener(evt, e => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('sr-drag');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropzone.addEventListener(evt, e => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('sr-drag');
            });
        });

        dropzone.addEventListener('drop', e => {
            for (const f of e.dataTransfer.files) tryAddFile(f);
            renderQuickList();
        });

        fileInput.addEventListener('change', e => {
            for (const f of e.target.files) tryAddFile(f);
            fileInput.value = '';
            renderQuickList();
        });

        fileList.addEventListener('click', e => {
            const rm = e.target.closest('[data-remove]');
            if (rm) {
                files.splice(+rm.getAttribute('data-remove'), 1);
                renderQuickList();
            }
        });

        btnProceed.addEventListener('click', () => {
            // Ensure we are on last step
            if (currentStep !== 3) {
                showStep(3);
                return;
            }

            // Validate that at least one file is added
            if (!files.length) {
                showErrorModal('Please add at least one document before proceeding.');
                return;
            }
            // Validate required fields
            const title = document.getElementById('title').value.trim();
            const category = document.getElementById('sleacat').value;
            if (!title) {
                showErrorModal('Please enter the title of activity.');
                showStep(2);
                return;
            }
            if (!category) {
                showErrorModal('Please select a SLEA category.');
                showStep(1);
                return;
            }
            renderDraftList();
            openModal(modalDraft);
        });

        draftList.addEventListener('click', e => {
            const rm = e.target.closest('[data-remove]');
            const ed = e.target.closest('[data-edit]');
            if (rm) {
                files.splice(+rm.getAttribute('data-remove'), 1);
                renderQuickList();
                renderDraftList();
            }
            if (ed) {
                const idx = +ed.getAttribute('data-edit');
                const tmp = document.createElement('input');
                tmp.type = 'file';
                tmp.accept = '.jpg,.jpeg,.png,.pdf';
                tmp.onchange = ev => {
                    const nf = ev.target.files[0];
                    if (!nf) return;
                    if (!acceptExt.test(nf.name)) return showErrorModal('Only JPG, PNG, or PDF allowed.');
                    if (nf.size > maxSize) return showErrorModal('File exceeds 5MB.');
                    files[idx] = {
                        name: nf.name,
                        file: nf
                    };
                    renderQuickList();
                    renderDraftList();
                };
                tmp.click();
            }
        });

        btnSubmitDraft.addEventListener('click', () => openModal(modalConfirm));

        btnConfirmOk.addEventListener('click', () => {
            const form = document.getElementById('submitForm');
            const fd = new FormData(form);

            // Handle role_in_activity: use dropdown value if visible, otherwise use text field value
            // Remove the text field name to avoid conflicts
            fd.delete('role_in_activity_text');
            if (roleSelect && roleSelect.style.display !== 'none' && roleSelect.value) {
                // Store the position name (text) instead of ID for better readability
                const selectedOption = roleSelect.options[roleSelect.selectedIndex];
                fd.set('role_in_activity', selectedOption ? selectedOption.textContent : roleSelect.value);
            } else if (roleText && roleText.style.display !== 'none' && roleText.value) {
                fd.set('role_in_activity', roleText.value);
            } else {
                // If neither is visible or has value, ensure the field is set to empty
                fd.set('role_in_activity', '');
            }

            files.forEach(f => {
                fd.append('attachments[]', f.file);
            });

            closeModal(modalConfirm);

            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: fd,
                })
                .then(async res => {
                    if (!res.ok) {
                        let payload;
                        try {
                            payload = await res.json();
                        } catch (e) {
                            payload = await res.text();
                        }
                        console.error('Submit error (status ' + res.status + '):', payload);

                        if (payload && payload.errors) {
                            const firstField = Object.keys(payload.errors)[0];
                            const errorArray = payload.errors[firstField];
                            // Safely get the first error message
                            const firstMsg = Array.isArray(errorArray) && errorArray.length > 0 
                                ? errorArray[0] 
                                : (typeof errorArray === 'string' ? errorArray : 'Validation error occurred.');
                            showErrorModal(firstMsg);
                        } else if (payload && payload.message) {
                            showErrorModal(payload.message);
                        } else if (typeof payload === 'string' && payload.includes('application_status')) {
                            showErrorModal('Database migration required. Please contact the administrator to run: php artisan migrate');
                        } else {
                            // Extract a user-friendly error message
                            let errorMsg = 'There was a problem submitting your record.';
                            if (typeof payload === 'string') {
                                // Try to extract a cleaner error message
                                const match = payload.match(/SQLSTATE\[.*?\]:\s*(.+?)(?:\s*\(Connection:|$)/);
                                if (match && match[1]) {
                                    errorMsg = 'Database error: ' + match[1].trim();
                                } else if (payload.length < 200) {
                                    errorMsg = payload;
                                }
                            }
                            showErrorModal(errorMsg);
                        }
                        return;
                    }

                    // success - close all modals first, then show success
                    closeModal(modalDraft);
                    closeModal(modalConfirm);
                    // Small delay to ensure modals are closed before showing success
                    setTimeout(() => {
                        openModal(modalSuccess);
                    }, 100);

                    setTimeout(() => {
                        closeModal(modalSuccess);
                        // Optional: reset after success
                        const form = document.getElementById('submitForm');
                        form.reset();
                        files = [];
                        renderQuickList();
                        clearSelect(sleaSectionSelect, 'Select section');
                        clearSelect(sleaSubSelect, 'Select subsection');
                        sleaSectionSelect.disabled = true;
                        sleaSubSelect.disabled = true;
                        showStep(1);
                        window.location.href = '{{ route("student.submit") }}';
                    }, 1100);
                })
                .catch(err => {
                    console.error(err);
                    showErrorModal('Network error while submitting.');
                });
        });

        document.addEventListener('click', e => {
            const closer = e.target.closest('[data-close]');
            if (closer) closeModal(document.getElementById(closer.getAttribute('data-close')));
        });

        // Initialize step 1
        showStep(1);
    })();
</script>

@endsection