<!-- Bootstrap Toast Container -->
<div aria-live="polite" aria-atomic="true" class="position-relative">
  <div id="toastrContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.toastr) return; // Prevent duplicate init

    window.toastr = {
        _createToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toastrContainer');
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');

            // Optional header (you can remove img if not needed)
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;

            container.appendChild(toast);

            // Initialize Bootstrap toast
            const bsToast = new bootstrap.Toast(toast, { delay: duration });
            bsToast.show();

            // Remove from DOM after hidden
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        },

        success(msg, time = 4000) { this._createToast(msg, 'success', time); },
        error(msg, time = 4000) { this._createToast(msg, 'danger', time); },
        info(msg, time = 4000) { this._createToast(msg, 'info', time); },
        warning(msg, time = 4000) { this._createToast(msg, 'warning', time); }
    };
});
</script>
