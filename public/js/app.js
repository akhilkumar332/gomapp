// Configure Axios defaults
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add CSRF Token to all requests
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Global error handler
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 419) {
            // Handle expired CSRF token
            window.location.reload();
        }
        return Promise.reject(error);
    }
);

// Initialize all components that require JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });

    // Initialize popovers
    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(popover => {
        new bootstrap.Popover(popover);
    });

    // Handle form submissions with loading states
    const forms = document.querySelectorAll('form:not([data-no-loading])');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Loading...
                `;
                // Store original text for reset
                submitBtn.dataset.originalText = originalText;
            }
        });
    });

    // Handle delete confirmations
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.dataset.confirmMessage || 'Are you sure you want to delete this item?';
            
            Swal.fire({
                title: 'Confirm Delete',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = this.closest('form');
                    if (form) form.submit();
                }
            });
        });
    });

    // Handle tabs with local storage
    const tabElements = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabElements.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const id = e.target.getAttribute('data-bs-target');
            localStorage.setItem('activeTab', id);
        });

        // Restore active tab
        const activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            const tab = document.querySelector(`[data-bs-toggle="tab"][data-bs-target="${activeTab}"]`);
            if (tab) {
                new bootstrap.Tab(tab).show();
            }
        }
    });

    // Handle collapsible cards
    const collapsibleCards = document.querySelectorAll('.card[data-collapsible]');
    collapsibleCards.forEach(card => {
        const toggleBtn = card.querySelector('.card-collapse-toggle');
        const cardBody = card.querySelector('.card-body');
        
        if (toggleBtn && cardBody) {
            toggleBtn.addEventListener('click', function() {
                const isCollapsed = cardBody.classList.contains('d-none');
                
                if (isCollapsed) {
                    cardBody.classList.remove('d-none');
                    toggleBtn.querySelector('i').classList.replace('bx-plus', 'bx-minus');
                } else {
                    cardBody.classList.add('d-none');
                    toggleBtn.querySelector('i').classList.replace('bx-minus', 'bx-plus');
                }
            });
        }
    });

    // Handle filter form reset
    const filterForms = document.querySelectorAll('.filter-form');
    filterForms.forEach(form => {
        const resetBtn = form.querySelector('[data-reset-filters]');
        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                form.reset();
                form.submit();
            });
        }
    });

    // Handle bulk actions
    const bulkActionForms = document.querySelectorAll('[data-bulk-actions]');
    bulkActionForms.forEach(form => {
        const checkAll = form.querySelector('[data-check-all]');
        const checkboxes = form.querySelectorAll('[data-bulk-checkbox]');
        const actionBtn = form.querySelector('[data-bulk-action]');
        
        if (checkAll && checkboxes.length && actionBtn) {
            // Handle "Check All" functionality
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateActionButton();
            });

            // Handle individual checkbox changes
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateActionButton);
            });

            // Update action button state
            function updateActionButton() {
                const checkedCount = form.querySelectorAll('[data-bulk-checkbox]:checked').length;
                actionBtn.disabled = checkedCount === 0;
                actionBtn.querySelector('.count').textContent = checkedCount;
            }
        }
    });

    // Handle responsive tables
    const responsiveTables = document.querySelectorAll('.table-responsive');
    responsiveTables.forEach(table => {
        const scrollIndicator = document.createElement('div');
        scrollIndicator.className = 'table-scroll-indicator';
        table.parentNode.insertBefore(scrollIndicator, table);

        table.addEventListener('scroll', function() {
            const maxScroll = this.scrollWidth - this.clientWidth;
            const currentScroll = this.scrollLeft;
            const scrollPercentage = (currentScroll / maxScroll) * 100;
            
            scrollIndicator.style.width = `${scrollPercentage}%`;
        });
    });

    // Initialize custom file inputs
    const fileInputs = document.querySelectorAll('.custom-file-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file chosen';
            const label = this.nextElementSibling;
            if (label) {
                label.textContent = fileName;
            }
        });
    });
});

// Toast notification helper
window.showToast = function(options) {
    const defaults = {
        title: '',
        message: '',
        type: 'info',
        duration: 3000
    };

    const settings = { ...defaults, ...options };
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${settings.type} show`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="toast-header">
            <i class="bx bx-${settings.type === 'success' ? 'check' : settings.type} me-2"></i>
            <strong class="me-auto">${settings.title}</strong>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">${settings.message}</div>
    `;
    
    document.querySelector('.toast-container')?.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, settings.duration);
};

// Confirmation dialog helper
window.confirmAction = function(options) {
    const defaults = {
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#4723D9',
        cancelButtonColor: '#6c757d'
    };

    const settings = { ...defaults, ...options };
    
    return Swal.fire(settings);
};

// Form helper functions
window.resetForm = function(form) {
    form.reset();
    form.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(el => {
        el.remove();
    });
};

window.handleFormErrors = function(form, errors) {
    resetForm(form);
    
    Object.keys(errors).forEach(field => {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
            input.classList.add('is-invalid');
            
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = errors[field][0];
            
            input.parentNode.appendChild(feedback);
        }
    });
};
