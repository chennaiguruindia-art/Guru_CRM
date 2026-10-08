/**
 * Horticulture CRM - Common Utilities & AJAX Handler
 */

// CSRF Token Retriever
function getCSRFToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
}

// Format Currency in INR (₹)
function formatCurrency(amount) {
    if (isNaN(amount) || amount === null) return '₹0.00';
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 2
    }).format(amount);
}

// Format numbers
function formatNumber(num) {
    if (isNaN(num) || num === null) return '0';
    return new Intl.NumberFormat('en-IN').format(num);
}

// Show Toast Message (using Bootstrap 5 Toasts)
function showToast(message, type = 'success') {
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '1090';
        document.body.appendChild(toastContainer);
    }

    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success text-white' :
                    type === 'error' || type === 'danger' ? 'bg-danger text-white' :
                    type === 'warning' ? 'bg-warning text-dark' : 'bg-info text-white';

    const icon = type === 'success' ? 'bi-check-circle-fill' :
                 type === 'error' || type === 'danger' ? 'bi-exclamation-triangle-fill' :
                 type === 'warning' ? 'bi-exclamation-circle-fill' : 'bi-info-circle-fill';

    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ${icon}"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    const bsToast = new bootstrap.Toast(toastElement, { delay: 4000 });
    bsToast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Unified AJAX Wrapper using Fetch API
async function ajaxRequest(url, options = {}) {
    const defaultHeaders = {
        'X-CSRF-TOKEN': getCSRFToken(),
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };

    if (!(options.body instanceof FormData)) {
        defaultHeaders['Content-Type'] = 'application/json';
    }

    const fetchOptions = {
        method: options.method || 'GET',
        headers: {
            ...defaultHeaders,
            ...(options.headers || {})
        }
    };

    if (options.body) {
        if (options.body instanceof FormData) {
            fetchOptions.body = options.body;
        } else if (typeof options.body === 'object') {
            fetchOptions.body = JSON.stringify(options.body);
        } else {
            fetchOptions.body = options.body;
        }
    }

    try {
        const response = await fetch(url, fetchOptions);
        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422 && options.formElement) {
                highlightValidationErrors(options.formElement, data.errors || {});
            }
            throw data;
        }

        return data;
    } catch (error) {
        console.error('AJAX Error:', error);
        throw error;
    }
}

// Highlight Form Validation Errors
function highlightValidationErrors(form, errors) {
    clearValidationErrors(form);

    for (const [field, messages] of Object.entries(errors)) {
        const input = form.querySelector(`[name="${field}"]`) || form.querySelector(`[name="${field}[]"]`);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.innerText = messages[0];
            input.parentNode.appendChild(feedback);
        }
    }
}

// Clear Form Validation Errors
function clearValidationErrors(form) {
    if (!form) return;
    const invalidInputs = form.querySelectorAll('.is-invalid');
    invalidInputs.forEach(input => input.classList.remove('is-invalid'));

    const feedbacks = form.querySelectorAll('.invalid-feedback');
    feedbacks.forEach(fb => fb.remove());
}

// Sidebar Mobile Toggle — off-canvas drawer below the lg breakpoint
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebar) {
        // Backdrop sits between the drawer and the page so a tap closes it.
        const backdrop = document.createElement('div');
        backdrop.id = 'sidebar-backdrop';
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.appendChild(backdrop);

        const drawerQuery = window.matchMedia('(max-width: 991.98px)');

        const closeDrawer = () => {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        };

        const openDrawer = () => {
            sidebar.classList.add('show');
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden'; // stop the page scrolling behind
        };

        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (sidebar.classList.contains('show')) closeDrawer();
                else openDrawer();
            });
        }

        const closeBtn = document.getElementById('sidebar-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                closeDrawer();
            });
        }

        backdrop.addEventListener('click', closeDrawer);

        // Following a menu link dismisses the drawer — otherwise it stays over
        // the page just navigated to. Pin stars and accordion headers are
        // <button>s, so they are unaffected.
        sidebar.addEventListener('click', (e) => {
            if (e.target.closest('a[href]')) closeDrawer();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) closeDrawer();
        });

        // Growing past the breakpoint would otherwise leave the drawer on screen.
        drawerQuery.addEventListener('change', (e) => {
            if (!e.matches) closeDrawer();
        });
    }

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
});
