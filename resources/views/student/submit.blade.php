@extends('layouts.app')

@section('title', 'Submit Record')

@section('content')
<div class="submit-record-page">
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">

            <!-- 🔹 Upload Dropzone -->
            <section class="sr-card sr-drop">
                <input id="fileInput" type="file" name="document_file" accept=".jpg,.jpeg,.png,.pdf" multiple hidden>
                <div id="dropzone" class="sr-dropzone" role="button" tabindex="0" aria-label="Click to upload">
                    <i class="fa-solid fa-upload"></i>
                    <div class="sr-drop-title">Click to Upload</div>
                    <div class="sr-drop-sub">(JPEG, PDF, and PNG, up to 5MB)</div>
                    <div class="sr-drop-hint">
                        Please rename your file:<br>
                        <code>TitleOfActivity_DocumentType_Lastname</code><br>
                        <small>LeadershipTraining2024_CertificateOfParticipation_DelaCruz</small>
                    </div>
                </div>
                <ul id="fileList" class="sr-filelist"></ul>
            </section>

            <!-- 🔹 Submission Form -->
            <form id="submitForm"
                class="sr-card sr-form"
                method="POST"
                action="{{ route('student.submissions.store') }}"
                enctype="multipart/form-data">
                @csrf

                <h3>Activity Details</h3>
                <div class="sr-grid">
                    <div class="sr-field">
                        <label for="activity_title">Title of Activity <span class="text-danger">*</span></label>
                        <input id="activity_title" name="activity_title" type="text" placeholder="e.g., Leadership Training" required>
                    </div>
                    <div class="sr-field">
                        <label for="activity_type">Type of Activity <span class="text-danger">*</span></label>
                        <input id="activity_type" name="activity_type" type="text" placeholder="e.g., Seminar / Workshop" required>
                    </div>
                    <div class="sr-field">
                        <label for="activity_role">Role in Activity <span class="text-danger">*</span></label>
                        <input id="activity_role" name="activity_role" type="text" placeholder="e.g., Participant / Speaker" required>
                    </div>
                    <div class="sr-field">
                        <label for="activity_date">Date of Activity <span class="text-danger">*</span></label>
                        <input id="activity_date" name="activity_date" type="date" required>
                    </div>
                    <div class="sr-field">
                        <label for="organizing_body">Organizing Body <span class="text-danger">*</span></label>
                        <input id="organizing_body" name="organizing_body" type="text" placeholder="e.g., OSAS / Department" required>
                    </div>
                    <div class="sr-field">
                        <label for="term">Term / Semester</label>
                        <input id="term" name="term" type="text" placeholder="e.g., 1st Semester AY 2024–2025">
                    </div>
                    <div class="sr-field">
                        <label for="issued_by">Issued By</label>
                        <input id="issued_by" name="issued_by" type="text" placeholder="e.g., OSAS Director">
                    </div>
                    <div class="sr-field">
                        <label for="note">Note (optional)</label>
                        <input id="note" name="note" type="text" placeholder="Any additional info">
                    </div>
                </div>

                <h3 style="margin-top:18px;">SLEA Classification</h3>
                <div class="sr-grid">
                    <div class="sr-field">
                        <label for="document_type">Document Type</label>
                        <select id="document_type" name="document_type">
                            <option value="">Select document type</option>
                            <option value="Certificate">Certificate</option>
                            <option value="Appointment">Appointment</option>
                            <option value="Report">Report</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>

                    <!-- Category Dropdown -->
                    <div class="sr-field">
                        <label for="category_id">SLEA Category</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->category_id }}">{{ $cat->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Section Dropdown -->
                    <div class="sr-field">
                        <label for="section_id">Section</label>
                        <select id="section_id" name="section_id">
                            <option value="">Select section</option>
                        </select>
                    </div>

                    <!-- Subsection Dropdown -->
                    <div class="sr-field">
                        <label for="sub_items">Subsection</label>
                        <select id="sub_items" name="sub_items">
                            <option value="">Select subsection</option>
                        </select>
                    </div>
                </div>

                <div class="sr-actions">
                    <button type="submit" class="sr-btn sr-btn-primary" id="btnSubmit">Submit</button>
                    <button type="reset" class="sr-btn sr-btn-ghost" id="btnReset">Clear</button>
                </div>
            </form>

            <!-- 🔹 Success Modal -->
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

<!-- 🔹 Scoped CSS and JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

<style>
    /* === Scoped ONLY to Submit Record page === */
    .submit-record-page .sr-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 1px 6px rgba(0, 0, 0, .06);
    }

    body.dark-mode .submit-record-page .sr-card {
        background: #333;
        border-color: #555;
        color: #f1f1f1;
    }

    .submit-record-page .sr-drop {
        padding: 0;
        margin-bottom: 16px;
    }

    .submit-record-page .sr-dropzone {
        padding: 28px 16px;
        border: 2px dashed #c7c7c7;
        border-radius: 14px;
        text-align: center;
        color: #666;
        cursor: pointer;
    }

    .submit-record-page .sr-dropzone i {
        font-size: 20px;
    }

    .submit-record-page .sr-drop-title {
        margin-top: 6px;
        font-weight: 700;
        color: #333;
    }

    .submit-record-page .sr-drop-sub {
        font-size: 12px;
        color: #888;
        margin-top: 2px;
    }

    .submit-record-page .sr-drop-hint {
        margin-top: 10px;
        font-size: 12px;
        color: #777;
    }

    .submit-record-page .sr-dropzone.sr-drag {
        background: #f8fafc;
        border-color: #7b0000;
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
    }

    .submit-record-page .sr-remove {
        border: none;
        background: #dc3545;
        color: #fff;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
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
    }

    .submit-record-page .sr-btn {
        border: none;
        border-radius: 22px;
        padding: 10px 18px;
        cursor: pointer;
        font-weight: 600;
    }

    .submit-record-page .sr-btn-primary {
        background: #d9534f;
        color: #fff;
    }

    .submit-record-page .sr-btn-primary:hover {
        background: #c73f3b;
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
        background: rgba(0, 0, 0, .25);
    }

    .submit-record-page .sr-modal-dialog {
        position: relative;
        background: #fff;
        border: 2px solid #222;
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
        border-color: #eee;
        color: #fff;
    }

    .submit-record-page .sr-modal-body {
        padding: 22px 24px;
    }

    .submit-record-page .sr-modal-title {
        text-align: center;
        color: #c04a47;
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
    }

    .submit-record-page .sr-draft-list li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-bottom: 1px dashed #d9d9d9;
    }

    .submit-record-page .sr-draft-list li:last-child {
        border-bottom: none;
    }

    .submit-record-page .sr-draft-actions {
        display: flex;
        gap: 8px;
    }

    .submit-record-page .sr-pill {
        border: none;
        border-radius: 16px;
        padding: 6px 12px;
        font-size: 13px;
        cursor: pointer;
    }

    .submit-record-page .sr-pill-edit {
        background: #e17673;
        color: #fff;
    }

    .submit-record-page .sr-pill-x {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eee;
        color: #444;
    }

    .submit-record-page .sr-pill-x:hover {
        background: #f3b6b4;
        color: #7b0000;
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
        .submit-record-page .sr-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const form = document.getElementById('submitForm');
        const modalSuccess = document.getElementById('modalSuccess');

        let files = [];

        // --- Dropzone ---
        dropzone.addEventListener('click', () => fileInput.click());
        ['dragenter', 'dragover'].forEach(evn => dropzone.addEventListener(evn, e => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('sr-drag');
        }));
        ['dragleave', 'drop'].forEach(evn => dropzone.addEventListener(evn, e => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('sr-drag');
        }));
        dropzone.addEventListener('drop', e => {
            files = [...files, ...e.dataTransfer.files];
            renderFileList();
        });
        fileInput.addEventListener('change', e => {
            files = [...files, ...e.target.files];
            renderFileList();
        });
        const renderFileList = () => {
            fileList.innerHTML = files.map((f, i) => `
            <li>${f.name}
                <button class="sr-remove" data-index="${i}"><i class="fa-solid fa-xmark"></i></button>
            </li>
        `).join('');
        };
        fileList.addEventListener('click', e => {
            const idx = e.target.closest('[data-index]')?.dataset.index;
            if (idx !== undefined) {
                files.splice(idx, 1);
                renderFileList();
            }
        });

        // --- Dependent Dropdowns for Rubric ---
        const sectionSelect = document.getElementById('section_id');
        const subSelect = document.getElementById('sub_items');

        document.getElementById('category_id').addEventListener('change', e => {
            const catId = e.target.value;
            sectionSelect.innerHTML = `<option value="">Loading...</option>`;
            fetch(`/rubric/${catId}/sections`)
                .then(r => r.json())
                .then(sections => {
                    sectionSelect.innerHTML = '<option value="">Select section</option>';
                    sections.forEach(sec => {
                        sectionSelect.insertAdjacentHTML('beforeend',
                            `<option value="${sec.section_id}">${sec.section_title}</option>`);
                    });
                    subSelect.innerHTML = '<option value="">Select subsection</option>';
                });
        });

        sectionSelect.addEventListener('change', e => {
            const secId = e.target.value;
            subSelect.innerHTML = `<option value="">Loading...</option>`;
            fetch(`/rubric/section/${secId}/subsections`)
                .then(r => r.json())
                .then(subs => {
                    subSelect.innerHTML = '<option value="">Select subsection</option>';
                    subs.forEach(s => {
                        subSelect.insertAdjacentHTML('beforeend',
                            `<option value="${s.sub_items}">${s.subsection_title}</option>`);
                    });
                });
        });

        // --- AJAX Submission (with FormData) ---
        form.addEventListener('submit', e => {
            e.preventDefault();
            const data = new FormData(form);
            if (files.length) data.append('document_file', files[0]);

            fetch(form.action, {
                    method: 'POST',
                    body: data,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    }
                })
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(resp => {
                    if (resp.success) {
                        openModal(modalSuccess);
                        form.reset();
                        files = [];
                        renderFileList();
                        setTimeout(() => closeModal(modalSuccess), 1500);
                    } else {
                        alert(resp.message || 'Error occurred.');
                    }
                })
                .catch(() => alert('Submission failed.'));
        });

        const openModal = el => el.setAttribute('aria-hidden', 'false');
        const closeModal = el => el.setAttribute('aria-hidden', 'true');
    });
</script>
@endsection