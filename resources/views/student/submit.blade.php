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
                            <label for="docType">Document Type <span style="color: red;">*</span></label>
                            <select id="docType" name="document_type" required>
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
                            <label for="sectionSelect">Section <span style="color: red;">*</span></label>
                            <select id="sectionSelect" name="rubric_section_id" disabled required>
                                <option value="">Select section</option>
                            </select>
                        </div>

                        {{-- Rubric Subsection --}}
                        <div class="sr-field">
                            <label for="subSection">Subsection <span style="color: red;">*</span></label>
                            <select id="subSection" name="rubric_subsection_id" disabled required>
                                <option value="">Select subsection</option>
                            </select>
                        </div>

                        {{-- Cluster Field (only shown when "Student Clubs and Organizations" is selected) --}}
                        <div class="sr-field" id="clusterField" style="display: none;">
                            <label for="clusterSelect">Cluster <span style="color: red;">*</span></label>
                            <select id="clusterSelect" name="cluster_id">
                                <option value="">Select cluster</option>
                            </select>
                        </div>

                        {{-- Organization Field (only shown when "Student Clubs and Organizations" is selected) --}}
                        <div class="sr-field" id="organizationField" style="display: none;">
                            <label for="organizationSelect">Organization <span style="color: red;">*</span></label>
                            <select id="organizationSelect" name="organization_id" disabled>
                                <option value="">Select organization</option>
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
                            <div style="position: relative;">
                                <select id="role" name="role_in_activity" style="width: 100%;">
                                    <option value="">Select role or enter custom</option>
                                </select>
                                <input
                                    id="roleCustom"
                                    name="role_in_activity_custom"
                                    type="text"
                                    placeholder="Enter custom role"
                                    style="margin-top: 8px; display: none; width: 100%; height: 42px; border-radius: 10px; border: 1px solid #ccd1d7; padding: 0 12px; padding-right: 100px; background: #fff; color: #111;">
                                <button
                                    type="button"
                                    id="roleBackToDropdown"
                                    style="display: none; position: absolute; right: 8px; top: 50px; padding: 6px 12px; background: #8B0000; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;"
                                    title="Back to dropdown">
                                    ← Back to List
                                </button>
                            </div>
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
                            <label for="term">Term</label>
                            <input
                                id="term"
                                name="term"
                                type="text"
                                placeholder="AY 2024–2025">
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

            <!-- Validation Error Modal -->
            <div id="modalValidation" class="sr-modal" aria-hidden="true">
                <div class="sr-modal-backdrop"></div>
                <div class="sr-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="validationTitle">
                    <div class="sr-modal-body">
                        <h4 id="validationTitle" class="sr-modal-title">Incomplete Fields</h4>
                        <p class="sr-modal-subtitle">Please complete all required fields before proceeding:</p>
                        <ul id="validationList" class="sr-validation-list"></ul>
                        <div class="sr-modal-actions">
                            <button class="sr-btn sr-btn-primary" data-close="modalValidation">Okay</button>
                        </div>
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
        border-radius: 0;
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

    .submit-record-page .sr-modal-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 16px;
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

    .submit-record-page .sr-validation-list {
        list-style: none;
        margin: 10px 0 14px;
        padding: 0;
        border: 2px dotted #cfcfcf;
        border-radius: 8px;
        max-height: 260px;
        overflow-y: auto;
        background: #fff7f7;
    }

    body.dark-mode .submit-record-page .sr-validation-list {
        background: #3b1b1b;
        border-color: #555;
    }

    .submit-record-page .sr-validation-list li {
        padding: 10px 14px;
        border-bottom: 1px dashed #d9d9d9;
        color: #dc3545;
        font-weight: 500;
    }

    .submit-record-page .sr-validation-list li:last-child {
        border-bottom: none;
    }

    body.dark-mode .submit-record-page .sr-validation-list li {
        color: #fca5a5;
        border-bottom-color: #555;
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
</style>

<script>
    (() => {
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
            sleaSectionSelect.removeAttribute('required');
            sleaSectionSelect.setAttribute('required', 'required');
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
                sleaSubSelect.appendChild(opt);
            });

            sleaSubSelect.disabled = false;
            sleaSubSelect.removeAttribute('required');
            sleaSubSelect.setAttribute('required', 'required');
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
                    // Reset cluster and organization fields when section changes
                    const clusterField = document.getElementById('clusterField');
                    const clusterSelect = document.getElementById('clusterSelect');
                    const orgField = document.getElementById('organizationField');
                    const orgSelect = document.getElementById('organizationSelect');
                    
                    if (clusterField) clusterField.style.display = 'none';
                    if (clusterSelect) {
                        clusterSelect.innerHTML = '<option value="">Select cluster</option>';
                        clusterSelect.removeAttribute('required');
                    }
                    
                    if (orgField) orgField.style.display = 'none';
                    if (orgSelect) {
                        orgSelect.innerHTML = '<option value="">Select organization</option>';
                        orgSelect.removeAttribute('required');
                        orgSelect.disabled = true;
                    }
                } catch (err) {
                    console.error('Error in populateSubsections:', err);
                }
            });
        }

        // ======== CLUSTER AND ORGANIZATION FIELD LOGIC ========
        const clusterField = document.getElementById('clusterField');
        const clusterSelect = document.getElementById('clusterSelect');
        const orgField = document.getElementById('organizationField');
        const orgSelect = document.getElementById('organizationSelect');
        const clustersUrl = '{{ route("ajax.clusters") }}';
        const organizationsUrl = '{{ route("ajax.organizations") }}';

        const loadClusters = async () => {
            if (!clusterSelect) return;
            
            clusterSelect.innerHTML = '<option value="">Loading clusters...</option>';
            clusterSelect.disabled = true;

            try {
                const response = await fetch(`${clustersUrl}?_=${Date.now()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                clusterSelect.innerHTML = '<option value="">Select cluster</option>';
                
                if (Array.isArray(data)) {
                    data.forEach(cluster => {
                        const opt = document.createElement('option');
                        opt.value = cluster.id || cluster.value;
                        opt.textContent = cluster.name || cluster.label || cluster.id;
                        clusterSelect.appendChild(opt);
                    });
                } else if (typeof data === 'object' && data !== null) {
                    Object.entries(data).forEach(([id, name]) => {
                        const opt = document.createElement('option');
                        opt.value = id;
                        opt.textContent = name;
                        clusterSelect.appendChild(opt);
                    });
                }
                
                clusterSelect.disabled = false;
            } catch (err) {
                console.error('Error loading clusters:', err);
                clusterSelect.innerHTML = '<option value="">Error loading clusters</option>';
            }
        };

        const loadOrganizations = async (clusterId) => {
            if (!orgSelect) return;
            
            orgSelect.innerHTML = '<option value="">Loading organizations...</option>';
            orgSelect.disabled = true;

            if (!clusterId) {
                orgSelect.innerHTML = '<option value="">Select cluster first</option>';
                return;
            }

            try {
                const response = await fetch(`${organizationsUrl}?cluster_id=${encodeURIComponent(clusterId)}&_=${Date.now()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                orgSelect.innerHTML = '<option value="">Select organization</option>';
                
                if (Array.isArray(data)) {
                    data.forEach(org => {
                        const opt = document.createElement('option');
                        opt.value = org.id || org.value;
                        opt.textContent = org.name || org.label || org.id;
                        orgSelect.appendChild(opt);
                    });
                } else if (typeof data === 'object' && data !== null) {
                    Object.entries(data).forEach(([id, name]) => {
                        const opt = document.createElement('option');
                        opt.value = id;
                        opt.textContent = name;
                        orgSelect.appendChild(opt);
                    });
                }
                
                orgSelect.disabled = false;
            } catch (err) {
                console.error('Error loading organizations:', err);
                orgSelect.innerHTML = '<option value="">Error loading organizations</option>';
            }
        };

        // Handle subsection change to show/hide cluster and organization fields
        if (sleaSubSelect) {
            sleaSubSelect.addEventListener('change', () => {
                const selectedOption = sleaSubSelect.options[sleaSubSelect.selectedIndex];
                const subsectionText = selectedOption ? selectedOption.textContent.trim() : '';
                const subsectionId = selectedOption ? selectedOption.value : null;
                
                // Store selected subsection text and ID for Step 2
                selectedSubsectionText = subsectionText;
                selectedSubsectionId = subsectionId;
                
                // Check if subsection is "Student Clubs and Organizations"
                const isStudentClubsOrg = /student clubs and organizations/i.test(subsectionText);
                
                if (isStudentClubsOrg) {
                    // Show cluster field first
                    if (clusterField && clusterSelect) {
                        clusterField.style.display = '';
                        clusterSelect.setAttribute('required', 'required');
                        loadClusters();
                    }
                    // Show organization field (but disabled until cluster is selected)
                    if (orgField && orgSelect) {
                        orgField.style.display = '';
                        orgSelect.setAttribute('required', 'required');
                        orgSelect.disabled = true;
                        orgSelect.innerHTML = '<option value="">Select cluster first</option>';
                    }
                } else {
                    // Hide both fields
                    if (clusterField) {
                        clusterField.style.display = 'none';
                        if (clusterSelect) {
                            clusterSelect.removeAttribute('required');
                            clusterSelect.value = '';
                        }
                    }
                    if (orgField) {
                        orgField.style.display = 'none';
                        if (orgSelect) {
                            orgSelect.removeAttribute('required');
                            orgSelect.value = '';
                            orgSelect.disabled = true;
                        }
                    }
                }
            });
        }

        // Handle cluster change to load organizations
        if (clusterSelect) {
            clusterSelect.addEventListener('change', () => {
                const clusterId = clusterSelect.value;
                if (orgSelect) {
                    if (clusterId) {
                        loadOrganizations(clusterId);
                    } else {
                        orgSelect.innerHTML = '<option value="">Select cluster first</option>';
                        orgSelect.disabled = true;
                        orgSelect.value = '';
                        selectedOrganizationId = null;
                    }
                }
            });
        }

        // Handle organization change to store organization ID for Step 2
        if (orgSelect) {
            orgSelect.addEventListener('change', () => {
                selectedOrganizationId = orgSelect.value || null;
            });
        }

        // ======== ROLE IN ACTIVITY DROPDOWN LOGIC ========
        const roleSelect = document.getElementById('role');
        const roleCustomInput = document.getElementById('roleCustom');
        const roleBackToDropdown = document.getElementById('roleBackToDropdown');
        const positionsUrl = '{{ route("ajax.positions") }}';
        const councilPositionsUrl = '{{ route("ajax.council.positions") }}';
        
        // Store selected subsection text and ID for Step 2
        let selectedSubsectionText = '';
        let selectedSubsectionId = null;
        let selectedOrganizationId = null;
        let selectedSectionText = ''; // Store section text to check if it's Section A

        // Map subsection text to leadership type key
        const subsectionToLeadershipType = {
            'student government (university / campus)': 'usg',
            'student government (university / campu': 'usg', // truncated version
            'campus student council': 'osc',
            'local councils (college level)': 'lc',
            'local council': 'lc',
            'student clubs and organizations': 'sco',
            'local government unit (lgu)': 'lgu',
            'other recognized organizations (non-lgu)': 'eap',
            'elective/appointive position': 'eap',
            'league of class mayors': 'lcm',
            'council of clubs and organizations': 'cco'
        };

        const getLeadershipTypeFromSubsection = (subsectionText) => {
            if (!subsectionText) return null;
            const normalized = subsectionText.toLowerCase().trim();
            for (const [key, value] of Object.entries(subsectionToLeadershipType)) {
                if (normalized.includes(key) || key.includes(normalized)) {
                    return value;
                }
            }
            return null;
        };

        const loadPositionsByLeadershipType = async (leadershipTypeKey) => {
            if (!leadershipTypeKey || !roleSelect) return;
            
            roleSelect.innerHTML = '<option value="">Loading positions...</option>';
            roleSelect.disabled = true;

            // Try to load from API first, but use hardcoded as primary source
            try {
                const response = await fetch(`${councilPositionsUrl}?leadership_type_key=${encodeURIComponent(leadershipTypeKey)}&_=${Date.now()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                let data = [];
                try {
                    data = await response.json();
                } catch (e) {
                    console.warn('Could not parse positions from API, using hardcoded positions');
                }
                
                roleSelect.innerHTML = '<option value="">Select role</option>';
                
                // Use API data if available, otherwise use hardcoded positions
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(pos => {
                        const opt = document.createElement('option');
                        opt.value = pos.id || pos.value || pos.name;
                        opt.textContent = pos.name || pos.label || pos.alias || pos.id;
                        roleSelect.appendChild(opt);
                    });
                } else {
                    // Use hardcoded positions from PositionSeeder
                    const hardcodedPositions = getHardcodedPositions(leadershipTypeKey);
                    if (hardcodedPositions && hardcodedPositions.length > 0) {
                        hardcodedPositions.forEach(pos => {
                            const opt = document.createElement('option');
                            opt.value = pos;
                            opt.textContent = pos;
                            roleSelect.appendChild(opt);
                        });
                    }
                }
                
                // Add option for custom input (only if NOT Section A)
                const isSectionA = /campus-based student government/i.test(selectedSectionText) || 
                                  /^a\./i.test(selectedSectionText);
                if (!isSectionA) {
                    const customOpt = document.createElement('option');
                    customOpt.value = '__custom__';
                    customOpt.textContent = '--- Enter Custom Role ---';
                    roleSelect.appendChild(customOpt);
                }
                
                roleSelect.disabled = false;
            } catch (err) {
                console.error('Error loading positions by leadership type:', err);
                // Fallback to hardcoded positions
                const hardcodedPositions = getHardcodedPositions(leadershipTypeKey);
                roleSelect.innerHTML = '<option value="">Select role</option>';
                if (hardcodedPositions && hardcodedPositions.length > 0) {
                    hardcodedPositions.forEach(pos => {
                        const opt = document.createElement('option');
                        opt.value = pos;
                        opt.textContent = pos;
                        roleSelect.appendChild(opt);
                    });
                }
                // Add option for custom input (only if NOT Section A)
                const isSectionA = /campus-based student government/i.test(selectedSectionText) || 
                                  /^a\./i.test(selectedSectionText);
                if (!isSectionA) {
                    const customOpt = document.createElement('option');
                    customOpt.value = '__custom__';
                    customOpt.textContent = '--- Enter Custom Role ---';
                    roleSelect.appendChild(customOpt);
                }
                roleSelect.disabled = false;
            }
        };

        const getHardcodedPositions = (leadershipTypeKey) => {
            const positionsMap = {
                'usg': [
                    'Student Regent/President',
                    'Vice President for Internal and External Affairs',
                    'Vice President for Business Correspondence and Records',
                    'Vice President for Finance, Audit and Logistics',
                    'Vice President for Publication and Information',
                    'Committee Member'
                ],
                'osc': [
                    'OSC President',
                    'OSC Vice President for Internal Affairs',
                    'OSC Vice President for External Affairs',
                    'OSC General Secretary',
                    'OSC General Treasurer',
                    'OSC General Auditor',
                    'OSC Public Information Officer',
                    'Committee Member'
                ],
                'lc': [
                    'Governor',
                    'Vice Governor',
                    'Secretary',
                    'Treasurer',
                    'Auditor',
                    'College House Representative (1st)',
                    'College House Representative (2nd)',
                    'Committee Member'
                ],
                'lcm': [
                    'Mayor',
                    'Vice Mayor',
                    'Secretary',
                    'Treasurer',
                    'Auditor'
                ],
                'sco': [
                    'President',
                    'Internal Vice President',
                    'External Vice President',
                    'Secretary',
                    'Assistant Secretary',
                    'Treasurer',
                    'Auditor',
                    'Public Information Officer (PIO)',
                    '1st Year Representative',
                    '2nd Year Representative',
                    '3rd Year Representative',
                    '4th Year Representative',
                    'Committee Member'
                ],
                'cco': [
                    'President',
                    'Vice President for Internal Affairs',
                    'Vice President for External Affairs',
                    'Vice President for Secretariat and Communications',
                    'Associate Vice President for Secretariat and Communications',
                    'Vice President for Audit',
                    'Vice President for Finance',
                    'Vice President for Business and Events Management',
                    'Vice President for Logistics and Property Superintendent',
                    'Campus Ministry Cluster Director',
                    'Legislative Board Chairperson',
                    'Academic Cluster Director',
                    'Socio-Civic Cluster Director',
                    'Culture and Arts Cluster Director',
                    'Sports Cluster Director',
                    'Inter-Fraternity and Sorority Cluster Director',
                    'Committee Member'
                ],
                'lgu': [
                    'Municipal Councilor / SK Federated President',
                    'Barangay Councilor / SK Chairperson',
                    'Barangay Secretary / Treasurer',
                    'SK Councilor',
                    'Indigenous People / Youth Leader'
                ],
                'eap': [
                    'President',
                    'Vice President',
                    'Indigenous People',
                    'Youth Leader',
                    'Secretary',
                    'Treasurer'
                ]
            };
            return positionsMap[leadershipTypeKey] || null;
        };

        const populateRoleDropdown = async (positions) => {
            if (!roleSelect) return;
            
            roleSelect.innerHTML = '<option value="">Select role</option>';
            
            if (Array.isArray(positions)) {
                positions.forEach(pos => {
                    const opt = document.createElement('option');
                    opt.value = typeof pos === 'string' ? pos : (pos.name || pos.label || pos);
                    opt.textContent = typeof pos === 'string' ? pos : (pos.name || pos.label || pos);
                    roleSelect.appendChild(opt);
                });
            }
            
            // Add option for custom input (only if NOT Section A)
            const isSectionA = /campus-based student government/i.test(selectedSectionText) || 
                              /^a\./i.test(selectedSectionText);
            if (!isSectionA) {
                const customOpt = document.createElement('option');
                customOpt.value = '__custom__';
                customOpt.textContent = '--- Enter Custom Role ---';
                roleSelect.appendChild(customOpt);
            }
        };

        const loadPositionsForOrganization = async (orgId) => {
            if (!orgId || !roleSelect) return;
            
            roleSelect.innerHTML = '<option value="">Loading positions...</option>';
            roleSelect.disabled = true;

            try {
                const response = await fetch(`${positionsUrl}?organization_id=${encodeURIComponent(orgId)}&_=${Date.now()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                roleSelect.innerHTML = '<option value="">Select role</option>';
                
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(pos => {
                        const opt = document.createElement('option');
                        opt.value = pos.id || pos.value || pos.name;
                        opt.textContent = pos.name || pos.label || pos.id;
                        roleSelect.appendChild(opt);
                    });
                } else {
                    // If no positions found, show custom input option
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'No positions available - use custom input';
                    roleSelect.appendChild(opt);
                }
                
                // Add option for custom input (only if NOT Section A)
                const isSectionA = /campus-based student government/i.test(selectedSectionText) || 
                                  /^a\./i.test(selectedSectionText);
                if (!isSectionA) {
                    const customOpt = document.createElement('option');
                    customOpt.value = '__custom__';
                    customOpt.textContent = '--- Enter Custom Role ---';
                    roleSelect.appendChild(customOpt);
                }
                
                roleSelect.disabled = false;
            } catch (err) {
                console.error('Error loading positions:', err);
                roleSelect.innerHTML = '<option value="">Error loading positions</option>';
            }
        };

        // Update role dropdown when moving to Step 2
        const updateRoleDropdownForStep2 = () => {
            if (!roleSelect) return;
            
            // Get leadership type from subsection
            const leadershipTypeKey = getLeadershipTypeFromSubsection(selectedSubsectionText);
            
            if (leadershipTypeKey) {
                // Load positions by leadership type (this includes SCO with hardcoded positions)
                loadPositionsByLeadershipType(leadershipTypeKey);
            } else {
                // Default: show empty dropdown with custom option (only if NOT Section A)
                const isSectionA = /campus-based student government/i.test(selectedSectionText) || 
                                  /^a\./i.test(selectedSectionText);
                roleSelect.innerHTML = '<option value="">Select role' + (isSectionA ? '' : ' or enter custom') + '</option>';
                if (!isSectionA) {
                    const customOpt = document.createElement('option');
                    customOpt.value = '__custom__';
                    customOpt.textContent = '--- Enter Custom Role ---';
                    roleSelect.appendChild(customOpt);
                }
            }
        };

        // Handle role dropdown change to show/hide custom input
        if (roleSelect) {
            roleSelect.addEventListener('change', () => {
                if (roleSelect.value === '__custom__') {
                    roleSelect.style.display = 'none';
                    if (roleCustomInput) {
                        roleCustomInput.style.display = 'block';
                        roleCustomInput.focus();
                    }
                    if (roleBackToDropdown) {
                        roleBackToDropdown.style.display = 'block';
                    }
                } else {
                    if (roleCustomInput) {
                        roleCustomInput.style.display = 'none';
                        roleCustomInput.value = '';
                    }
                    if (roleBackToDropdown) {
                        roleBackToDropdown.style.display = 'none';
                    }
                }
            });
        }

        // Handle back to dropdown button
        if (roleBackToDropdown) {
            roleBackToDropdown.addEventListener('click', () => {
                if (roleSelect) {
                    roleSelect.style.display = 'block';
                    roleSelect.value = '';
                }
                if (roleCustomInput) {
                    roleCustomInput.style.display = 'none';
                    roleCustomInput.value = '';
                }
                if (roleBackToDropdown) {
                    roleBackToDropdown.style.display = 'none';
                }
                // Reload the dropdown options
                updateRoleDropdownForStep2();
            });
        }

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

        // ======== VALIDATION MODAL LOGIC ========
        const modalValidation = document.getElementById('modalValidation');
        const validationList = document.getElementById('validationList');
        
        const openValidationModal = (missingFields) => {
            if (!validationList || !modalValidation) return;
            
            validationList.innerHTML = '';
            missingFields.forEach(field => {
                const li = document.createElement('li');
                li.textContent = field;
                validationList.appendChild(li);
            });
            
            modalValidation.setAttribute('aria-hidden', 'false');
        };

        const closeValidationModal = () => {
            if (modalValidation) {
                modalValidation.setAttribute('aria-hidden', 'true');
            }
        };

        btnStep1Next?.addEventListener('click', () => {
            const missingFields = [];
            
            // Validate Document Type
            const docType = document.getElementById('docType');
            if (!docType || !docType.value) {
                missingFields.push('Document Type');
            }
            
            // Validate SLEA Category
            const category = sleaCatSelect.value;
            if (!category) {
                missingFields.push('SLEA Category');
            }
            
            // Validate Section (only if enabled)
            if (!sleaSectionSelect.disabled && !sleaSectionSelect.value) {
                missingFields.push('Section');
            } else if (sleaSectionSelect.disabled) {
                missingFields.push('Section (please select a category first)');
            }
            
            // Validate Subsection (only if enabled)
            if (!sleaSubSelect.disabled && !sleaSubSelect.value) {
                missingFields.push('Subsection');
            } else if (sleaSubSelect.disabled && !sleaSectionSelect.disabled) {
                missingFields.push('Subsection (please select a section first)');
            }
            
            // Validate Cluster (if visible)
            if (clusterField && clusterField.style.display !== 'none') {
                if (!clusterSelect || !clusterSelect.value) {
                    missingFields.push('Cluster');
                }
            }
            
            // Validate Organization (if visible)
            if (orgField && orgField.style.display !== 'none') {
                if (!orgSelect || !orgSelect.value || orgSelect.disabled) {
                    missingFields.push('Organization');
                }
            }
            
            // If there are missing fields, show modal and prevent proceeding
            if (missingFields.length > 0) {
                openValidationModal(missingFields);
                return;
            }
            
            // All fields are complete, proceed to next step
            // Update role dropdown before showing Step 2
            updateRoleDropdownForStep2();
            showStep(2);
        });

        btnStep2Prev?.addEventListener('click', () => {
            // Reset role dropdown when going back
            if (roleSelect) {
                const isSectionA = /campus-based student government/i.test(selectedSectionText) || 
                                  /^a\./i.test(selectedSectionText);
                roleSelect.innerHTML = '<option value="">Select role' + (isSectionA ? '' : ' or enter custom') + '</option>';
                if (!isSectionA) {
                    const customOpt = document.createElement('option');
                    customOpt.value = '__custom__';
                    customOpt.textContent = '--- Enter Custom Role ---';
                    roleSelect.appendChild(customOpt);
                }
                roleSelect.disabled = false;
            }
            if (roleCustomInput) {
                roleCustomInput.style.display = 'none';
                roleCustomInput.value = '';
            }
            if (roleBackToDropdown) {
                roleBackToDropdown.style.display = 'none';
            }
            showStep(1);
        });

        btnStep2Next?.addEventListener('click', () => {
            const missingFields = [];
            
            // Validate Activity Title
            const title = document.getElementById('title');
            if (!title || !title.value.trim()) {
                missingFields.push('Title of Activity');
            }
            
            // Validate Application Status
            const appStatus = document.getElementById('applicationStatus');
            if (appStatus && appStatus.hasAttribute('required') && !appStatus.value) {
                missingFields.push('Application Status');
            }
            
            // If there are missing fields, show modal and prevent proceeding
            if (missingFields.length > 0) {
                openValidationModal(missingFields);
                return;
            }
            
            // All required fields are complete, proceed to next step
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

        const openModal = el => el.setAttribute('aria-hidden', 'false');
        const closeModal = el => el.setAttribute('aria-hidden', 'true');

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
                alert('Only JPG, PNG, or PDF allowed.');
                return;
            }
            if (f.size > maxSize) {
                alert('File exceeds 5MB.');
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
                alert('Please add at least one document before proceeding.');
                return;
            }
            // Validate required fields
            const title = document.getElementById('title').value.trim();
            const category = document.getElementById('sleacat').value;
            if (!title) {
                alert('Please enter the title of activity.');
                showStep(2);
                return;
            }
            if (!category) {
                alert('Please select a SLEA category.');
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
                    if (!acceptExt.test(nf.name)) return alert('Only JPG, PNG, or PDF allowed.');
                    if (nf.size > maxSize) return alert('File exceeds 5MB.');
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

            // Handle role_in_activity: use custom input if selected, otherwise use dropdown value
            if (roleSelect && roleSelect.value === '__custom__' && roleCustomInput && roleCustomInput.value) {
                fd.set('role_in_activity', roleCustomInput.value);
            } else if (roleSelect && roleSelect.value && roleSelect.value !== '__custom__') {
                fd.set('role_in_activity', roleSelect.value);
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
                            const firstMsg = payload.errors[firstField][0];
                            alert(firstMsg);
                        } else if (payload && payload.message) {
                            alert(payload.message);
                        } else if (typeof payload === 'string' && payload.includes('application_status')) {
                            alert('Database migration required. Please contact the administrator to run: php artisan migrate');
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
                            alert(errorMsg);
                        }
                        return;
                    }

                    // success
                    closeModal(modalDraft);
                    openModal(modalSuccess);

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
                    alert('Network error while submitting.');
                });
        });

        document.addEventListener('click', e => {
            const closer = e.target.closest('[data-close]');
            if (closer) {
                const modalId = closer.getAttribute('data-close');
                if (modalId === 'modalValidation') {
                    closeValidationModal();
                } else {
                    closeModal(document.getElementById(modalId));
                }
            }
        });

        // Initialize step 1
        showStep(1);
    })();
</script>

@endsection