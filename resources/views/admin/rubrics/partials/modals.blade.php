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
            <form id="editRubricForm" method="POST">
                @csrf
                <div class="form-group">
                    <label for="editCategory">Category/Type</label>
                    <input type="text" id="editCategory" name="category" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editPosition">Position/Title</label>
                    <input type="text" id="editPosition" name="position_or_title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editPoints">Points</label>
                    <input type="number" id="editPoints" name="points" class="form-control" step="0.1" min="0" max="5" required>
                </div>
                <div class="form-group">
                    <label for="editMaxPoints">Max Points</label>
                    <input type="number" id="editMaxPoints" name="max_points" class="form-control" min="0" max="5">
                </div>
                <div class="form-group">
                    <label for="editEvidence">Evidence</label>
                    <input type="text" id="editEvidence" name="evidence" class="form-control">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeEditRubricModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitEditRubricForm()">Save Changes</button>
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
        padding: 20px 25px;
        border-radius: 12px;
        width: 100%;
        max-width: 480px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        position: relative;
        animation: fadeIn 0.3s ease-in-out;
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
    }

    .modal-header h3 {
        font-weight: 600;
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

    .modal-body {
        padding: 10px 0;
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

    /* Dark Mode */
    @media (prefers-color-scheme: dark) {
        .modal-content {
            background: #2b2b2b;
            color: #eee;
        }

        .btn-secondary {
            background: #444;
            color: #eee;
        }

        .btn-secondary:hover {
            background: #555;
        }
    }
</style>

{{-- =========================================================
 ✅ JS: Modal Handlers (Edit / Delete / Success)
========================================================= --}}
<script>
    let currentRubricId = null;

    function openEditRubricModal(rubricId, category, position, points, maxPoints, evidence) {
        currentRubricId = rubricId;
        document.getElementById('editCategory').value = category || '';
        document.getElementById('editPosition').value = position || '';
        document.getElementById('editPoints').value = points || '';
        document.getElementById('editMaxPoints').value = maxPoints || '';
        document.getElementById('editEvidence').value = evidence || '';

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
        const formData = new FormData(form);
        const submitBtn = document.querySelector('#editRubricModal .btn-primary');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Saving...';
        submitBtn.disabled = true;

        fetch(form.action || '/admin/rubrics', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                closeEditRubricModal();
                showRubricSuccessModal(data.message || 'Rubric item saved successfully!');
                setTimeout(() => window.location.reload(), 1500);
            })
            .catch(err => console.error(err))
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
    }

    function openDeleteRubricModal(rubricId, category, position) {
        currentRubricId = rubricId;
        document.getElementById('deleteRubricMessage').textContent =
            `Are you sure you want to delete "${category} - ${position}"? This cannot be undone.`;
        document.getElementById('deleteRubricForm').action = `/admin/rubrics/${rubricId}`;
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
        const formData = new FormData(form);
        const btn = document.querySelector('#deleteRubricModal .btn-danger');
        const text = btn.textContent;
        btn.textContent = 'Deleting...';
        btn.disabled = true;

        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                closeDeleteRubricModal();
                showRubricSuccessModal(data.message || 'Deleted successfully!');
                setTimeout(() => window.location.reload(), 1500);
            })
            .catch(err => console.error(err))
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

    // Allow closing by clicking outside modals
    window.onclick = function(event) {
        ['editRubricModal', 'deleteRubricModal', 'rubricSuccessModal'].forEach(id => {
            const modal = document.getElementById(id);
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }
</script>