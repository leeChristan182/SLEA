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
        // Look for existing unified-pagination container
        let container = table.closest('.page-content, .program-section, .manage-account, .approve-reject, .submission-oversight, .award-report, .system-monitoring, .final-review').querySelector('.unified-pagination');
        
        if (!container) {
            // Create new pagination container
            container = document.createElement('div');
            container.className = 'unified-pagination';
            container.innerHTML = `
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
            
            // Insert after the table
            const tableWrap = table.closest('.table-wrap') || table.parentElement;
            tableWrap.insertAdjacentElement('afterend', container);
        }

        return container;
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

        // Show max 5 page buttons
        let startPage = Math.max(1, this.currentPage - 2);
        let endPage = Math.min(this.totalPages, startPage + 4);

        // Adjust start page if we're near the end
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = 'btn-page';
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
});

// Export for global use
window.AdminPagination = AdminPagination;
window.initializeAdminPagination = initializeAdminPagination;


