/**
 * TLS Toast Notification System
 * Simple, Bootstrap 5-based toast notifications
 */

class TLSToast {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create toast container if it doesn't exist
        if (!document.getElementById('tls-toast-container')) {
            this.container = document.createElement('div');
            this.container.id = 'tls-toast-container';
            this.container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            this.container.style.zIndex = '9999';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('tls-toast-container');
        }
    }

    /**
     * Show a toast notification
     * @param {string} message - The message to display
     * @param {string} type - Type of toast: success, error, warning, info
     * @param {number} duration - Duration in milliseconds (default: 3000)
     */
    show(message, type = 'info', duration = 3000) {
        const toastId = 'toast-' + Date.now();

        // Map types to Bootstrap classes and icons
        const typeConfig = {
            success: {
                class: 'bg-success text-white',
                icon: 'bi-check-circle-fill'
            },
            error: {
                class: 'bg-danger text-white',
                icon: 'bi-exclamation-circle-fill'
            },
            warning: {
                class: 'bg-warning text-dark',
                icon: 'bi-exclamation-triangle-fill'
            },
            info: {
                class: 'bg-info text-white',
                icon: 'bi-info-circle-fill'
            }
        };

        const config = typeConfig[type] || typeConfig.info;

        // Create toast HTML
        const toastHTML = `
            <div id="${toastId}" class="toast ${config.class}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex align-items-center p-3">
                    <i class="${config.icon} me-2 fs-5"></i>
                    <div class="toast-body flex-grow-1">
                        ${this.escapeHtml(message)}
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        // Add to container
        this.container.insertAdjacentHTML('beforeend', toastHTML);

        // Initialize Bootstrap toast
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: duration > 0,
            delay: duration
        });

        // Remove from DOM after hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });

        // Show toast
        toast.show();
    }

    success(message, duration = 3000) {
        this.show(message, 'success', duration);
    }

    error(message, duration = 5000) {
        this.show(message, 'error', duration);
    }

    warning(message, duration = 4000) {
        this.show(message, 'warning', duration);
    }

    info(message, duration = 3000) {
        this.show(message, 'info', duration);
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Create global instance
window.tlsToast = new TLSToast();
