{{-- =========================================================
 ✅ MODALS: Edit, Delete, and Success Notifications
========================================================= --}}

<!-- ✏️ Edit Rubric Modal -->
<div id="editRubricModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Rubric Item</h3>
            <span class="close" onclick="closeEditRubricModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editRubricForm" method="POST" action="">
                @csrf
                <input type="hidden" id="editSubsectionId" name="subsection_id" required>
                <div class="form-group">
                    <label for="editPosition">Position/Title (Label) <span class="text-danger">*</span></label>
                    <input type="text" id="editPosition" name="label" class="form-control form-control-lg" required>
                </div>
                <div class="form-group">
                    <label for="editPoints">Points <span class="text-danger">*</span></label>
                    <input type="number" id="editPoints" name="points" class="form-control form-control-lg" step="0.1" min="0" max="5" required>
                </div>
                <div class="form-group">
                    <label for="editEvidenceNeeded">Evidence Needed</label>
                    <textarea id="editEvidenceNeeded" name="evidence_needed" class="form-control form-control-lg" rows="3" placeholder="Enter evidence requirements..."></textarea>
                </div>
                <div class="form-group">
                    <label for="editNotes">Notes</label>
                    <textarea id="editNotes" name="notes" class="form-control form-control-lg" rows="3" placeholder="Enter additional notes..."></textarea>
                </div>
                <div class="form-group">
                    <label for="editOrderNo">Order No (optional)</label>
                    <input type="number" id="editOrderNo" name="order_no" class="form-control form-control-lg" min="0">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeEditRubricModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitEditRubricForm()">Save</button>
        </div>
    </div>
</div>

<!-- 🗑️ Delete Rubric Modal -->
<div id="deleteRubricModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Delete Rubric Item</h3>
            <span class="close" onclick="closeDeleteRubricModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p id="deleteRubricMessage">Are you sure you want to delete this rubric item? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeDeleteRubricModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="submitDeleteRubricForm()">Delete</button>
            <form id="deleteRubricForm" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<!-- ✏️ Edit Subsection Modal -->
<div id="editSubsectionModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Subsection</h3>
            <span class="close" onclick="closeEditSubsectionModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editSubsectionForm" method="POST" action="">
                @csrf
                <input type="hidden" id="editSubsectionSectionId" name="section_id">
                <div class="form-group">
                    <label for="editSubsectionName">Subsection Name <span class="text-danger">*</span></label>
                    <input type="text" id="editSubsectionName" name="sub_section" class="form-control form-control-lg" required>
                </div>
                <div class="form-group">
                    <label for="editSubsectionMaxPoints">Max Points</label>
                    <input type="number" id="editSubsectionMaxPoints" name="max_points" class="form-control form-control-lg" step="0.1" min="0">
                </div>
                <div class="form-group">
                    <label for="editSubsectionEvidence">Evidence Needed</label>
                    <textarea id="editSubsectionEvidence" name="evidence_needed" rows="3" class="form-control form-control-lg"></textarea>
                </div>
                <div class="form-group">
                    <label for="editSubsectionNotes">Notes</label>
                    <textarea id="editSubsectionNotes" name="notes" rows="3" class="form-control form-control-lg"></textarea>
                </div>
                <div class="form-group">
                    <label for="editSubsectionOrderNo">Order No</label>
                    <input type="number" id="editSubsectionOrderNo" name="order_no" class="form-control form-control-lg" min="0">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeEditSubsectionModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitEditSubsectionForm()">Save</button>
        </div>
    </div>
</div>

<!-- 🗑️ Delete Subsection Modal -->
<div id="deleteSubsectionModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Delete Subsection</h3>
            <span class="close" onclick="closeDeleteSubsectionModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p id="deleteSubsectionMessage">Are you sure you want to delete this subsection? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeDeleteSubsectionModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="submitDeleteSubsectionForm()">Delete</button>
            <form id="deleteSubsectionForm" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<!-- ✅ Success Modal -->
<div id="rubricSuccessModal" class="modal" style="display: none;">
    <div class="modal-content success">
        <div class="modal-header">
            <h3>Success</h3>
            <span class="close" onclick="closeRubricSuccessModal()">&times;</span>
        </div>
        <div class="modal-body text-center">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 id="rubricSuccessMessage">Operation completed successfully!</h3>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" onclick="closeRubricSuccessModal()">OK</button>
        </div>
    </div>
</div>

{{-- =========================================================
 ✅ STYLES
========================================================= --}}
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background: rgba(0, 0, 0, 0.6);
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: #fff;
        color: #333;
        padding: 20px 30px;
        border-radius: 0 !important;
        width: 100%;
        max-width: 900px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        position: relative;
        animation: fadeIn 0.3s ease-in-out;
        transition: background-color 0.3s, color 0.3s;
    }

    body.dark-mode .modal-content {
        background: #2b2b2b;
        color: #fff;
    }

    .modal-content.success {
        max-width: 380px;
        text-align: center;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 15px;
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
        margin: -20px -30px 20px -30px;
        padding: 15px 30px;
    }

    body.dark-mode .modal-header {
        background: #1a1a1a;
        border-bottom-color: #444;
    }

    .modal-header h3 {
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    body.dark-mode .modal-header h3 {
        color: #fff;
    }

    .modal-header .close {
        cursor: pointer;
        font-size: 24px;
        color: #666;
        transition: 0.25s;
    }

    .modal-header .close:hover {
        color: #000;
    }

    body.dark-mode .modal-header .close {
        color: #ccc;
    }

    body.dark-mode .modal-header .close:hover {
        color: #fff;
    }

    .modal-body {
        padding: 10px 0;
    }

    body.dark-mode .modal-body {
        color: #fff;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 15px;
    }

    .btn {
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }

    .btn-primary {
        background: #007bff;
        border: none;
        color: white;
    }

    .btn-primary:hover {
        background: #0056b3;
    }

    .btn-secondary {
        background: #f0f0f0;
        color: #333;
        border: none;
    }

    .btn-secondary:hover {
        background: #ddd;
    }

    body.dark-mode .btn-secondary {
        background: #444;
        color: #eee;
    }

    body.dark-mode .btn-secondary:hover {
        background: #555;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
        border: none;
    }

    .btn-danger:hover {
        background: #b02a37;
    }

    .success-icon {
        font-size: 48px;
        color: #28a745;
        margin-bottom: 15px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Form elements dark mode */
    body.dark-mode .form-group label {
        color: #fff;
    }

    body.dark-mode .form-control {
        background-color: #3a3a3a;
        border-color: #555;
        color: #fff;
    }

    body.dark-mode .form-control:focus {
        background-color: #3a3a3a;
        border-color: #F9BD3D;
        color: #fff;
    }

    body.dark-mode .form-control::placeholder {
        color: #999;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #333;
    }

    body.dark-mode .form-group label {
        color: #fff;
        font-weight: bold;
    }

    .form-control-lg {
        width: 100%;
        padding: 12px 15px;
        font-size: 1rem;
        line-height: 1.5;
        border: 1px solid #ced4da;
        border-radius: 4px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control-lg:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    body.dark-mode .form-control-lg {
        background-color: #3a3a3a;
        border-color: #555;
        color: #fff;
    }

    body.dark-mode .form-control-lg:focus {
        border-color: #F9BD3D;
        box-shadow: 0 0 0 0.2rem rgba(249, 189, 61, 0.25);
    }

    textarea.form-control-lg {
        resize: vertical;
        min-height: 80px;
    }
</style>

{{-- =========================================================
 ✅ JS: Modal Handlers (Edit / Delete / Success)
========================================================= --}}
<script>
    let currentRubricId = null;

    function openEditRubricModal(rubricId, subsectionId, position, points, orderNo, evidenceNeeded, notes) {
        currentRubricId = rubricId;
        const form = document.getElementById('editRubricForm');
        
        // Set form action
        form.action = `/admin/rubrics/options/${rubricId}`;
        
        // Fill form fields
        document.getElementById('editSubsectionId').value = subsectionId || '';
        document.getElementById('editPosition').value = position || '';
        document.getElementById('editPoints').value = points || '';
        document.getElementById('editOrderNo').value = orderNo || '';
        document.getElementById('editEvidenceNeeded').value = evidenceNeeded || '';
        document.getElementById('editNotes').value = notes || '';
        
        const modal = document.getElementById('editRubricModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeEditRubricModal() {
        document.getElementById('editRubricModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        currentRubricId = null;
    }

    function submitEditRubricForm() {
        const form = document.getElementById('editRubricForm');
        if (!form || !currentRubricId) {
            console.error('Form or rubric ID missing');
            return;
        }

        const formData = new FormData(form);
        formData.append('_method', 'PUT');
        
        const submitBtn = document.querySelector('#editRubricModal .btn-primary');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Saving...';
        submitBtn.disabled = true;

        const actionUrl = form.action || `/admin/rubrics/options/${currentRubricId}`;

        fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value
                }
            })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => { throw new Error(text); });
                }
                return res.json();
            })
            .then(data => {
                closeEditRubricModal();
                showRubricSuccessModal(data.message || 'Rubric item saved successfully!');
                setTimeout(() => window.location.reload(), 1500);
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error saving rubric item. Please try again.');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
    }

    function openDeleteRubricModal(rubricId, category, position) {
        currentRubricId = rubricId;
        document.getElementById('deleteRubricMessage').textContent =
            `Are you sure you want to delete "${category} - ${position}"? This cannot be undone.`;
        document.getElementById('deleteRubricForm').action = `/admin/rubrics/options/${rubricId}`;
        document.getElementById('deleteRubricModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteRubricModal() {
        document.getElementById('deleteRubricModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        currentRubricId = null;
    }

    function submitDeleteRubricForm() {
        const form = document.getElementById('deleteRubricForm');
        if (!form || !currentRubricId) {
            console.error('Form or rubric ID missing');
            return;
        }

        const formData = new FormData(form);
        const btn = document.querySelector('#deleteRubricModal .btn-danger');
        const text = btn.textContent;
        btn.textContent = 'Deleting...';
        btn.disabled = true;

        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value
                }
            })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => { throw new Error(text); });
                }
                return res.json();
            })
            .then(data => {
                closeDeleteRubricModal();
                showRubricSuccessModal(data.message || 'Deleted successfully!');
                setTimeout(() => window.location.reload(), 1500);
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error deleting rubric item. Please try again.');
            })
            .finally(() => {
                btn.textContent = text;
                btn.disabled = false;
            });
    }

    function showRubricSuccessModal(message) {
        document.getElementById('rubricSuccessMessage').textContent = message;
        document.getElementById('rubricSuccessModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeRubricSuccessModal() {
        document.getElementById('rubricSuccessModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Subsection editing functions
    let currentSubsectionId = null;

    function openEditSubsectionModal(subsectionId, sectionId, subSectionName, maxPoints, evidenceNeeded, notes, orderNo) {
        currentSubsectionId = subsectionId;
        const form = document.getElementById('editSubsectionForm');
        
        // Set form action
        form.action = `/admin/rubrics/subsections/${subsectionId}`;
        
        // Fill form fields
        document.getElementById('editSubsectionSectionId').value = sectionId || '';
        document.getElementById('editSubsectionName').value = subSectionName || '';
        document.getElementById('editSubsectionMaxPoints').value = maxPoints || '';
        document.getElementById('editSubsectionEvidence').value = evidenceNeeded || '';
        document.getElementById('editSubsectionNotes').value = notes || '';
        document.getElementById('editSubsectionOrderNo').value = orderNo || '';
        
        const modal = document.getElementById('editSubsectionModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeEditSubsectionModal() {
        document.getElementById('editSubsectionModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        currentSubsectionId = null;
    }

    function submitEditSubsectionForm() {
        const form = document.getElementById('editSubsectionForm');
        if (!form || !currentSubsectionId) {
            console.error('Form or subsection ID missing');
            return;
        }

        const formData = new FormData(form);
        formData.append('_method', 'PUT');
        
        const submitBtn = document.querySelector('#editSubsectionModal .btn-primary');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Saving...';
        submitBtn.disabled = true;

        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value
                }
            })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => { throw new Error(text); });
                }
                return res.json();
            })
            .then(data => {
                closeEditSubsectionModal();
                showRubricSuccessModal(data.message || 'Subsection saved successfully!');
                setTimeout(() => window.location.reload(), 1500);
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error saving subsection. Please try again.');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
    }

    function openDeleteSubsectionModal(subsectionId, subsectionName) {
        currentSubsectionId = subsectionId;
        document.getElementById('deleteSubsectionMessage').textContent =
            `Are you sure you want to delete "${subsectionName}"? This action cannot be undone.`;
        document.getElementById('deleteSubsectionForm').action = `/admin/rubrics/subsections/${subsectionId}`;
        document.getElementById('deleteSubsectionModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteSubsectionModal() {
        document.getElementById('deleteSubsectionModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        currentSubsectionId = null;
    }

    function submitDeleteSubsectionForm() {
        const form = document.getElementById('deleteSubsectionForm');
        if (!form || !currentSubsectionId) {
            console.error('Form or subsection ID missing');
            return;
        }

        const formData = new FormData(form);
        const btn = document.querySelector('#deleteSubsectionModal .btn-danger');
        const text = btn.textContent;
        btn.textContent = 'Deleting...';
        btn.disabled = true;

        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value
                }
            })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => { throw new Error(text); });
                }
                return res.json();
            })
            .then(data => {
                closeDeleteSubsectionModal();
                showRubricSuccessModal(data.message || 'Deleted successfully!');
                setTimeout(() => window.location.reload(), 1500);
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error deleting subsection. Please try again.');
            })
            .finally(() => {
                btn.textContent = text;
                btn.disabled = false;
            });
    }

    // Allow closing by clicking outside modals
    window.onclick = function(event) {
        ['editRubricModal', 'deleteRubricModal', 'rubricSuccessModal', 'editSubsectionModal', 'deleteSubsectionModal'].forEach(id => {
            const modal = document.getElementById(id);
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }
</script>
</script>
</script>