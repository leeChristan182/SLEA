@extends('layouts.app')

@section('title', 'Scoring Rubric Configuration')

@section('content')
<div class="container">
    @include('partials.admin-sidebar')
    
    <main class="main-content">
        <div class="rubric-main-container" x-data="rubricTabs()">
            <div class="rubric-content">
        <!-- Back Button -->
        <div class="rubric-header-nav">
            <a href="{{ route('admin.profile') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <h2 class="rubric-main-title">Scoring Rubric Configuration</h2>

        <!-- Tabs Navigation -->
        <div class="tab-nav mb-3">
            <button class="btn" :class="tab === 'leadership' ? 'btn-disable' : ''" @click="tab = 'leadership'">I. Leadership Excellence</button>
            <button class="btn" :class="tab === 'academic' ? 'btn-disable' : ''" @click="tab = 'academic'">II. Academic Excellence</button>
            <button class="btn" :class="tab === 'awards' ? 'btn-disable' : ''" @click="tab = 'awards'">III. Awards/Recognition</button>
            <button class="btn" :class="tab === 'community' ? 'btn-disable' : ''" @click="tab = 'community'">IV. Community Involvement</button>
            <button class="btn" :class="tab === 'conduct' ? 'btn-disable' : ''" @click="tab = 'conduct'">V. Good Conduct</button>
        </div>

        <!-- Tab Content -->
        <template x-if="tab === 'leadership'">
            @include('admin.rubrics.sections.leadership')
        </template>
        <template x-if="tab === 'academic'">
            @include('admin.rubrics.sections.academic')
        </template>
        <template x-if="tab === 'awards'">
            @include('admin.rubrics.sections.awards')
        </template>
        <template x-if="tab === 'community'">
            @include('admin.rubrics.sections.community')
        </template>
        <template x-if="tab === 'conduct'">
            @include('admin.rubrics.sections.conduct')
        </template>

        <!-- Navigation Buttons -->
        <div class="unified-pagination mt-4">
            <button class="btn-nav" @click="previousTab()" x-show="!isFirstTab()" type="button">
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            <button class="btn-nav" @click="nextTab()" x-show="!isLastTab()" type="button">
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

{{-- Edit Rubric Modal --}}
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
                    <label for="editCategory">Category/Type:</label>
                    <input type="text" id="editCategory" name="category" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editPosition">Position/Title:</label>
                    <input type="text" id="editPosition" name="position_or_title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editPoints">Points:</label>
                    <input type="number" id="editPoints" name="points" class="form-control" step="0.1" min="0" max="5" required>
                </div>
                <div class="form-group">
                    <label for="editMaxPoints">Max Points:</label>
                    <input type="number" id="editMaxPoints" name="max_points" class="form-control" min="0" max="5">
                </div>
                <div class="form-group">
                    <label for="editEvidence">Evidence:</label>
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

{{-- Delete Rubric Modal --}}
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

{{-- Success Modal --}}
<div id="rubricSuccessModal" class="modal" style="display: none;">
    <div class="modal-body text-center">
        <div class="modal-header">
            <h3>Success</h3>
            <span class="close" onclick="closeRubricSuccessModal()">&times;</span>
        </div>
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 id="rubricSuccessMessage">Operation completed successfully!</h3>
        <div class="modal-footer">
            <button class="btn btn-primary" onclick="closeRubricSuccessModal()">OK</button>
        </div>
    </div>
</div>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function rubricTabs() {
    const tabs = ['leadership', 'academic', 'awards', 'community', 'conduct'];
    
    return {
        tab: 'leadership', // default active
        tabs: tabs,
        
        nextTab() {
            const currentIndex = this.tabs.indexOf(this.tab);
            if (currentIndex < this.tabs.length - 1) {
                this.tab = this.tabs[currentIndex + 1];
            }
        },
        
        previousTab() {
            const currentIndex = this.tabs.indexOf(this.tab);
            if (currentIndex > 0) {
                this.tab = this.tabs[currentIndex - 1];
            }
        },
        
        isFirstTab() {
            return this.tab === this.tabs[0];
        },
        
        isLastTab() {
            return this.tab === this.tabs[this.tabs.length - 1];
        }
    }
}

// Rubric Modal Functions
let currentRubricId = null;

// Edit Rubric Functions
function openEditRubricModal(rubricId, category, position, points, maxPoints, evidence) {
    try {
        currentRubricId = rubricId;
        
        // Populate form fields
        document.getElementById('editCategory').value = category || '';
        document.getElementById('editPosition').value = position || '';
        document.getElementById('editPoints').value = points || '';
        document.getElementById('editMaxPoints').value = maxPoints || '';
        document.getElementById('editEvidence').value = evidence || '';
        
        // For demo purposes, we'll create a new rubric instead of updating non-existent ones
        // In a real application, you would have actual rubric IDs from the database
        document.getElementById('editRubricForm').action = `/admin/rubrics`;
        document.getElementById('editRubricForm').method = 'POST';
        
        // Show modal
        const modal = document.getElementById('editRubricModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        } else {
            console.error('Edit modal not found');
            document.body.style.overflow = 'auto';
        }
    } catch (error) {
        console.error('Error opening edit modal:', error);
        document.body.style.overflow = 'auto';
    }
}

function closeEditRubricModal() {
    document.getElementById('editRubricModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    currentRubricId = null;
}

function submitEditRubricForm() {
    const form = document.getElementById('editRubricForm');
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = document.querySelector('#editRubricModal .btn-primary');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        }
        return response.json().then(err => Promise.reject(err));
    })
    .then(data => {
        closeEditRubricModal();
        showRubricSuccessModalWithCountdown(data.message || 'Rubric item updated successfully!', 2000);
        // Auto-close success modal and reload the page
        setTimeout(() => {
            closeRubricSuccessModal();
            window.location.reload();
        }, 2000);
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMessage = error.error || error.message || 'Error updating rubric item. Please try again.';
        showRubricSuccessModal(errorMessage);
        // Auto-close error modal after 3 seconds
        setTimeout(() => {
            closeRubricSuccessModal();
        }, 3000);
        // Restore button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Delete Rubric Functions
function openDeleteRubricModal(rubricId, category, position) {
    try {
        currentRubricId = rubricId;
        
        // Update message
        const message = `Are you sure you want to delete "${category} - ${position}"? This action cannot be undone.`;
        document.getElementById('deleteRubricMessage').textContent = message;
        
        // Set form action
        document.getElementById('deleteRubricForm').action = `/admin/rubrics/${rubricId}`;
        
        // Show modal
        const modal = document.getElementById('deleteRubricModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        } else {
            console.error('Delete modal not found');
            document.body.style.overflow = 'auto';
        }
    } catch (error) {
        console.error('Error opening delete modal:', error);
        document.body.style.overflow = 'auto';
    }
}

function closeDeleteRubricModal() {
    document.getElementById('deleteRubricModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    currentRubricId = null;
}

function submitDeleteRubricForm() {
    const form = document.getElementById('deleteRubricForm');
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = document.querySelector('#deleteRubricModal .btn-danger');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Deleting...';
    submitBtn.disabled = true;
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        }
        return response.json().then(err => Promise.reject(err));
    })
    .then(data => {
        closeDeleteRubricModal();
        showRubricSuccessModalWithCountdown(data.message || 'Rubric item deleted successfully!', 2000);
        // Auto-close success modal and reload the page
        setTimeout(() => {
            closeRubricSuccessModal();
            window.location.reload();
        }, 2000);
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMessage = error.error || error.message || 'Error deleting rubric item. Please try again.';
        showRubricSuccessModal(errorMessage);
        // Auto-close error modal after 3 seconds
        setTimeout(() => {
            closeRubricSuccessModal();
        }, 3000);
        // Restore button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Success Modal Functions
function showRubricSuccessModal(message) {
    document.getElementById('rubricSuccessMessage').textContent = message;
    document.getElementById('rubricSuccessModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Enhanced success modal with auto-close countdown
function showRubricSuccessModalWithCountdown(message, autoCloseDelay = 2000) {
    document.getElementById('rubricSuccessMessage').textContent = message;
    document.getElementById('rubricSuccessModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Add countdown indicator
    const countdownElement = document.createElement('div');
    countdownElement.id = 'countdownIndicator';
    countdownElement.style.cssText = `
        position: absolute;
        top: 10px;
        right: 50px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
    `;
    
    const modalHeader = document.querySelector('#rubricSuccessModal .modal-header');
    modalHeader.appendChild(countdownElement);
    
    // Start countdown
    let timeLeft = autoCloseDelay / 1000;
    const countdownInterval = setInterval(() => {
        countdownElement.textContent = `Auto-close in ${timeLeft}s`;
        timeLeft--;
        
        if (timeLeft < 0) {
            clearInterval(countdownInterval);
            countdownElement.remove();
        }
    }, 1000);
}

function closeRubricSuccessModal() {
    // Clean up countdown indicator if it exists
    const countdownElement = document.getElementById('countdownIndicator');
    if (countdownElement) {
        countdownElement.remove();
    }
    
    document.getElementById('rubricSuccessModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modals when clicking outside
window.onclick = function(event) {
    const editModal = document.getElementById('editRubricModal');
    const deleteModal = document.getElementById('deleteRubricModal');
    const successModal = document.getElementById('rubricSuccessModal');
    
    if (event.target === editModal) {
        closeEditRubricModal();
    }
    if (event.target === deleteModal) {
        closeDeleteRubricModal();
    }
    if (event.target === successModal) {
        closeRubricSuccessModal();
    }
}

// Check for success message on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(session('status'))
    showRubricSuccessModal('{{ session('status') }}');
    @endif
    
    // Add escape key handler to close modals and restore scroll
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            // Close any open modals and restore scroll
            closeEditRubricModal();
            closeDeleteRubricModal();
            closeRubricSuccessModal();
            document.body.style.overflow = 'auto';
        }
    });
});

// Global function to restore scrollability (safety net)
function restoreScrollability() {
    document.body.style.overflow = 'auto';
}

// Make it available globally
window.restoreScrollability = restoreScrollability;
</script>
@endsection
