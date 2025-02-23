@props(['id', 'title', 'size' => 'md', 'staticBackdrop' => false])

<div class="modal fade" 
     id="{{ $id }}" 
     tabindex="-1" 
     aria-labelledby="{{ $id }}Label" 
     aria-hidden="true"
     @if($staticBackdrop) data-bs-backdrop="static" data-bs-keyboard="false" @endif>
    <div class="modal-dialog modal-{{ $size }} modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{ $slot }}
        </div>
    </div>
</div>

@pushOnce('styles')
<style>
.modal-content {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    background-color: #fff;
    border-radius: 1rem 1rem 0 0;
}

.modal-title {
    font-weight: 600;
    color: #2D3748;
    font-size: 1.25rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    background-color: #fff;
    border-radius: 0 0 1rem 1rem;
}

.btn-close {
    background-size: 0.8em;
    transition: transform 0.2s;
}

.btn-close:hover {
    transform: rotate(90deg);
}

/* Modal animations */
.modal.fade .modal-dialog {
    transition: transform 0.2s ease-out;
}

.modal.fade .modal-dialog {
    transform: scale(0.95);
}

.modal.show .modal-dialog {
    transform: none;
}

/* Confirmation modal specific styles */
.modal-confirm .modal-body {
    text-align: center;
    padding: 2rem;
}

.modal-confirm .icon-box {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-confirm .icon-box i {
    font-size: 3rem;
}

.modal-confirm.modal-danger .icon-box {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.modal-confirm.modal-success .icon-box {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

/* Form modal specific styles */
.modal-form .modal-body {
    padding: 2rem;
}

.modal-form .form-group:last-child {
    margin-bottom: 0;
}

/* Size variations */
.modal-sm {
    max-width: 400px;
}

.modal-lg {
    max-width: 800px;
}

.modal-xl {
    max-width: 1140px;
}

@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-content {
        border-radius: 0.5rem;
    }
    
    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 1rem;
    }
}
</style>
@endPushOnce

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submissions in modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const form = modal.querySelector('form');
        if (form) {
            form.addEventListener('submit', () => {
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Loading...
                    `;
                }
            });
        }
    });

    // Reset form and button state when modal is hidden
    const modalElements = document.querySelectorAll('.modal');
    modalElements.forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function () {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Submit';
                }
            }
        });
    });
});
</script>
@endPushOnce
