@props([
    'id', 
    'title' => 'Confirm Action', 
    'message' => 'Are you sure you want to proceed with this action?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'confirmVariant' => 'danger',
    'icon' => 'bx-question-mark',
    'iconVariant' => 'warning'
])

<div class="modal fade modal-confirm modal-{{ $confirmVariant }}" 
     id="{{ $id }}" 
     tabindex="-1" 
     aria-labelledby="{{ $id }}Label" 
     aria-hidden="true"
     data-bs-backdrop="static" 
     data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pb-0">
                <div class="icon-box mb-4">
                    <i class='bx {{ $icon }}'></i>
                </div>
                <h4 class="modal-title mb-3">{{ $title }}</h4>
                <p class="mb-4">{{ $message }}</p>
                {{ $slot }}
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ $cancelText }}</button>
                <button type="button" class="btn btn-{{ $confirmVariant }} confirm-action">{{ $confirmText }}</button>
            </div>
        </div>
    </div>
</div>

@pushOnce('styles')
<style>
.modal-confirm .modal-content {
    padding: 1.5rem;
}

.modal-confirm .modal-header {
    padding: 0;
}

.modal-confirm .btn-close {
    position: absolute;
    right: 1rem;
    top: 1rem;
}

.modal-confirm .icon-box {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-confirm .icon-box i {
    font-size: 3rem;
}

/* Variant styles */
.modal-confirm.modal-danger .icon-box {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.modal-confirm.modal-warning .icon-box {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.modal-confirm.modal-success .icon-box {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.modal-confirm .modal-title {
    color: #2D3748;
    font-weight: 600;
}

.modal-confirm p {
    color: #6c757d;
    font-size: 0.95rem;
}

.modal-confirm .modal-footer {
    padding: 1rem 0 0;
}

.modal-confirm .btn {
    min-width: 120px;
    border-radius: 0.5rem;
    padding: 0.6rem 1.5rem;
    font-weight: 500;
}

/* Animation */
@keyframes modalConfirmIn {
    from {
        transform: scale(0.8);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.modal-confirm.show .modal-dialog {
    animation: modalConfirmIn 0.3s ease-out;
}

/* Loading state */
.modal-confirm .btn.loading {
    position: relative;
    pointer-events: none;
}

.modal-confirm .btn.loading span {
    visibility: hidden;
}

.modal-confirm .btn.loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin: -8px 0 0 -8px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: btnLoading 0.6s linear infinite;
}

@keyframes btnLoading {
    to {
        transform: rotate(360deg);
    }
}
</style>
@endPushOnce

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle confirmation modals
    const confirmModals = document.querySelectorAll('.modal-confirm');
    confirmModals.forEach(modal => {
        const confirmBtn = modal.querySelector('.confirm-action');
        const form = modal.querySelector('form');
        
        if (confirmBtn && form) {
            confirmBtn.addEventListener('click', () => {
                confirmBtn.classList.add('loading');
                confirmBtn.disabled = true;
                form.submit();
            });
        }
    });
});
</script>
@endPushOnce
