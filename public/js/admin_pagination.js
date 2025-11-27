// Admin Dashboard Dynamic Pagination System
// Matches the assessor dashboard pagination functionality

class AdminPagination {
    constructor(tableSelector, entriesPerPage = 10) {
        this.tableSelector = tableSelector;
        this.entriesPerPage = entriesPerPage;
        this.currentPage = 1;
        this.totalEntries = 0;
        this.totalPages = 0;
        this.paginationContainer = null;
    }

    // Initialize pagination for a specific table
    initializePagination() {
        const table = document.querySelector(this.tableSelector);
        if (!table) {
            console.error(`Table with selector "${this.tableSelector}" not found`);
            return;
        }

        // Count total entries (rows in table body)
        const tableRows = table.querySelectorAll('tbody tr');
        this.totalEntries = tableRows.length;
        this.totalPages = Math.ceil(this.totalEntries / this.entriesPerPage);

        // Find or create pagination container
        this.paginationContainer = this.findOrCreatePaginationContainer(table);
        
        if (!this.paginationContainer) {
            console.error('Pagination container not found');
            return;
        }

        // Update pagination info and generate buttons
        this.updatePaginationInfo();
        this.generatePageButtons();
        this.updateButtonStates();
        
        // Show/hide table rows based on current page
        this.updateTableDisplay();
    }

    // Find existing pagination container or create one
    findOrCreatePaginationContainer(table) {
        // First, look for existing pagination container with data attribute
        let paginationContainer = document.querySelector('[data-pagination-container]');
        
        if (paginationContainer) {
            // Return the unified-pagination div inside it
            return paginationContainer.querySelector('.unified-pagination') || paginationContainer;
        }
        
        // Look for existing unified-pagination container
        const parent = table.closest('.page-content, .program-section, .manage-account, .approve-reject, .submission-oversight, .award-report, .system-monitoring, .final-review, .main-content');
        if (parent) {
            const existing = parent.querySelector('.unified-pagination');
            if (existing) return existing;
        }
        
        // Create new pagination container structure
        paginationContainer = document.createElement('div');
        paginationContainer.className = 'pagination-container';
        paginationContainer.setAttribute('data-pagination-container', '');
        
        const unifiedPagination = document.createElement('div');
        unifiedPagination.className = 'unified-pagination';
        unifiedPagination.innerHTML = `
            <button class="btn-nav" id="prevBtn" disabled>
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            <span class="pagination-pages" id="paginationPages">
                <!-- Dynamic pages will be generated here -->
            </span>
            <button class="btn-nav" id="nextBtn">
                Next <i class="fas fa-chevron-right"></i>
            </button>
        `;
        
        const paginationInfo = document.createElement('div');
        paginationInfo.className = 'pagination-info';
        paginationContainer.appendChild(paginationInfo);
        paginationContainer.appendChild(unifiedPagination);
        
        // Insert after the table container
        const tableContainer = table.closest('.submissions-table-container') || table.parentElement;
        if (tableContainer) {
            tableContainer.insertAdjacentElement('afterend', paginationContainer);
        } else {
            table.insertAdjacentElement('afterend', paginationContainer);
        }

        return unifiedPagination;
    }

    // Update pagination info display
    updatePaginationInfo() {
        const start = (this.currentPage - 1) * this.entriesPerPage + 1;
        const end = Math.min(this.currentPage * this.entriesPerPage, this.totalEntries);
        
        // Update pagination info if it exists
        const infoElement = document.querySelector('.pagination-info');
        if (infoElement) {
            infoElement.innerHTML = `Showing <span id="showingStart">${start}</span>-<span id="showingEnd">${end}</span> of <span id="totalEntries">${this.totalEntries}</span> entries`;
        }
    }

    // Generate page number buttons
    generatePageButtons() {
        const paginationPages = this.paginationContainer.querySelector('#paginationPages');
        if (!paginationPages) return;

        paginationPages.innerHTML = '';

        if (this.totalPages === 0) return;

        // Show max 5 page buttons
        let startPage = Math.max(1, this.currentPage - 2);
        let endPage = Math.min(this.totalPages, startPage + 4);

        // Adjust start page if we're near the end
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = 'page-btn';
            if (i === this.currentPage) {
                pageBtn.classList.add('active');
            }
            pageBtn.textContent = i;
            pageBtn.onclick = () => this.goToPage(i);
            paginationPages.appendChild(pageBtn);
        }
    }

    // Update button states (Previous/Next)
    updateButtonStates() {
        const prevBtn = this.paginationContainer.querySelector('#prevBtn');
        const nextBtn = this.paginationContainer.querySelector('#nextBtn');

        if (prevBtn) {
            prevBtn.disabled = this.currentPage === 1;
        }

        if (nextBtn) {
            nextBtn.disabled = this.currentPage === this.totalPages;
        }
    }

    // Go to specific page
    goToPage(page) {
        if (page < 1 || page > this.totalPages || page === this.currentPage) {
            return;
        }

        this.currentPage = page;
        this.updatePaginationInfo();
        this.generatePageButtons();
        this.updateButtonStates();
        this.updateTableDisplay();
    }

    // Show/hide table rows based on current page
    updateTableDisplay() {
        const table = document.querySelector(this.tableSelector);
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr');
        const startIndex = (this.currentPage - 1) * this.entriesPerPage;
        const endIndex = startIndex + this.entriesPerPage;

        rows.forEach((row, index) => {
            if (index >= startIndex && index < endIndex) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Go to next page
    nextPage() {
        this.goToPage(this.currentPage + 1);
    }

    // Go to previous page
    previousPage() {
        this.goToPage(this.currentPage - 1);
    }

    // Set up event listeners
    setupEventListeners() {
        if (!this.paginationContainer) return;

        const prevBtn = this.paginationContainer.querySelector('#prevBtn');
        const nextBtn = this.paginationContainer.querySelector('#nextBtn');

        if (prevBtn) {
            prevBtn.onclick = () => this.previousPage();
        }

        if (nextBtn) {
            nextBtn.onclick = () => this.nextPage();
        }
    }

    // Initialize everything
    init() {
        this.initializePagination();
        this.setupEventListeners();
    }
}

// Global pagination instances
const paginationInstances = {};

// Initialize pagination for a specific table
function initializeAdminPagination(tableSelector, entriesPerPage = 10) {
    const pagination = new AdminPagination(tableSelector, entriesPerPage);
    pagination.init();
    paginationInstances[tableSelector] = pagination;
    return pagination;
}

// Initialize all pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize pagination for different admin pages
    
    // Award Report - BTVTED Program
    if (document.querySelector('.award-table')) {
        initializeAdminPagination('.award-table', 10);
    }

    // Manage Account
    if (document.querySelector('.manage-table')) {
        initializeAdminPagination('.manage-table', 10);
    }

    // Approval of Accounts
    if (document.querySelector('.approval-table')) {
        initializeAdminPagination('.approval-table', 10);
    }

    // Submission Oversight
    if (document.querySelector('.submission-table')) {
        initializeAdminPagination('.submission-table', 10);
    }

    // System Monitoring
    if (document.querySelector('.monitoring-table')) {
        initializeAdminPagination('.monitoring-table', 10);
    }

    // Final Review
    if (document.querySelector('.final-review-table')) {
        initializeAdminPagination('.final-review-table', 10);
    }

    // Pending Submissions (Assessor)
    if (document.querySelector('.submissions-table') || document.querySelector('table.submissions-table')) {
        initializeAdminPagination('.submissions-table', 5);
    }
});

// Export for global use
window.AdminPagination = AdminPagination;
window.initializeAdminPagination = initializeAdminPagination;


