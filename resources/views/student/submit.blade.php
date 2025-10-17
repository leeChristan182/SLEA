@extends('layouts.app')
@section('title', 'Submit Record')

@section('content')
<div class="submit-record-page">
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">
            <!-- Upload Dropzone -->
            <section class="sr-card sr-drop">
                <input id="fileInput" type="file" accept=".jpg,.jpeg,.png,.pdf" multiple hidden>
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

            <!-- Activity + SLEA Classification -->
            <form id="submitForm" class="sr-card sr-form" onsubmit="return false;">
                <h3>Activity</h3>
                <div class="sr-grid">
                    <div class="sr-field">
                        <label for="title">Title of Activity</label>
                        <input id="title" type="text" placeholder="e.g., Leadership Training">
                    </div>
                    <div class="sr-field">
                        <label for="type">Type of Activity</label>
                        <input id="type" type="text" placeholder="e.g., Seminar / Workshop">
                    </div>
                    <div class="sr-field">
                        <label for="role">Role in Activity</label>
                        <input id="role" type="text" placeholder="e.g., Participant / Speaker">
                    </div>
                    <div class="sr-field">
                        <label for="date">Date of Activity</label>
                        <input id="date" type="date">
                    </div>
                    <div class="sr-field">
                        <label for="orgBody">Organizing Body</label>
                        <input id="orgBody" type="text" placeholder="e.g., OSAS">
                    </div>
                    <div class="sr-field">
                        <label for="note">Note (optional)</label>
                        <input id="note" type="text" placeholder="Any additional info">
                    </div>
                    <div class="sr-field">
                        <label for="term">Term</label>
                        <input id="term" type="text" placeholder="AY 2024–2025">
                    </div>
                    <div class="sr-field">
                        <label for="issuedBy">Issued by</label>
                        <input id="issuedBy" type="text" placeholder="e.g., OSAS">
                    </div>
                </div>

                <h3 style="margin-top:18px;">SLEA Classification</h3>
                <div class="sr-grid">
                    <div class="sr-field">
                        <label for="docType">Document Type</label>
                        <select id="docType">
                            <option value="">Select document type</option>
                            <option>Certificate</option>
                            <option>Appointment</option>
                            <option>Report</option>
                        </select>
                    </div>
                    <div class="sr-field">
                        <label for="sleacat">SLEA Category</label>
                        <select id="sleacat">
                            <option value="">Select category</option>
                            <option>Department</option>
                            <option>College</option>
                            <option>University</option>
                            <option>Organization</option>
                        </select>
                    </div>
                    <div class="sr-field">
                        <label for="subSection">Subsection</label>
                        <select id="subSection">
                            <option value="">Select subsection</option>
                            <option>Leadership</option>
                            <option>Service</option>
                            <option>Academic</option>
                        </select>
                    </div>
                </div>

                <div class="sr-actions">
                    <button type="button" class="sr-btn sr-btn-primary" id="btnProceed">Proceed</button>
                    <button type="button" class="sr-btn sr-btn-ghost" id="btnAnother">Submit Another</button>
                    <button type="button" class="sr-btn sr-btn-ghost" id="btnCancel">Cancel</button>
                </div>
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
    (() => {
        let files = [];
        const maxSize = 5 * 1024 * 1024;
        const acceptExt = /\.(pdf|jpg|jpeg|png)$/i;

        const fileInput = document.getElementById('fileInput');
        const dropzone = document.getElementById('dropzone');
        const fileList = document.getElementById('fileList');

        const btnProceed = document.getElementById('btnProceed');
        const btnAnother = document.getElementById('btnAnother');
        const btnCancel = document.getElementById('btnCancel');

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
        <button class="sr-remove" data-remove="${i}" title="Remove"><i class="fa-solid fa-xmark"></i></button>
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
          <button class="sr-pill sr-pill-x" data-remove="${i}" title="Remove"><i class="fa-solid fa-xmark"></i></button>
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
            closeModal(modalConfirm);
            closeModal(modalDraft);
            openModal(modalSuccess);
            setTimeout(() => {
                closeModal(modalSuccess);
                document.getElementById('submitForm').reset();
                files = [];
                renderQuickList();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }, 1100);
        });

        document.addEventListener('click', e => {
            const closer = e.target.closest('[data-close]');
            if (closer) closeModal(document.getElementById(closer.getAttribute('data-close')));
        });

        btnAnother.addEventListener('click', () => {
            document.getElementById('submitForm').reset();
            files = [];
            renderQuickList();
        });
        btnCancel.addEventListener('click', () => {
            document.getElementById('submitForm').reset();
            files = [];
            renderQuickList();
        });
    })();
</script>
@endsection
