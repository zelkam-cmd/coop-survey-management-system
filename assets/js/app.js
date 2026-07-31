/**
 * CampusVoice — Core UI JavaScript
 * Handles sidebar toggle, modals, toasts, dropdowns, and global UI behavior.
 */

document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initDropdowns();
    initModals();
    initToasts();
    initFAQ();
    initAlertDismiss();
});

/* ── Sidebar ─────────────────────────────────────────────── */
function initSidebar() {
    const sidebar = document.querySelector('.app-sidebar');
    const hamburger = document.querySelector('.hamburger-btn');
    const overlay = document.querySelector('.sidebar-overlay');

    if (hamburger && sidebar) {
        hamburger.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            if (overlay) overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.style.display = 'none';
        });
    }
}

/* ── Dropdowns ───────────────────────────────────────────── */
function initDropdowns() {
    document.querySelectorAll('.dropdown').forEach(function(dropdown) {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const menu = dropdown.querySelector('.dropdown-menu');

        if (trigger && menu) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                // Close other dropdowns
                document.querySelectorAll('.dropdown-menu.active').forEach(function(m) {
                    if (m !== menu) m.classList.remove('active');
                });
                menu.classList.toggle('active');
            });
        }
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.active').forEach(function(menu) {
            menu.classList.remove('active');
        });
    });
}

/* ── Modals ──────────────────────────────────────────────── */
function initModals() {
    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeModal(overlay.id);
            }
        });
    });

    // Close modal buttons
    document.querySelectorAll('[data-modal-close]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const modalId = btn.getAttribute('data-modal-close');
            closeModal(modalId);
        });
    });

    // ESC key closes modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(function(overlay) {
                closeModal(overlay.id);
            });
        }
    });
}

/**
 * Open a modal by ID
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close a modal by ID
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/* ── Toast Notifications ─────────────────────────────────── */
let toastContainer = null;

function initToasts() {
    toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
}

/**
 * Show a toast notification
 * @param {string} title - Toast title
 * @param {string} message - Toast message
 * @param {string} type - success|error|warning|info
 * @param {number} duration - Auto-dismiss in ms (default 5000)
 */
function showToast(title, message, type, duration) {
    if (typeof type === 'undefined') type = 'info';
    if (typeof duration === 'undefined') duration = 5000;
    
    if (!toastContainer) initToasts();

    var icons = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
    };

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = 
        '<div class="toast-icon" style="color: var(--color-' + (type === 'info' ? 'primary' : type) + ')">' + 
            (icons[type] || icons.info) + 
        '</div>' +
        '<div class="toast-content">' +
            '<div class="toast-title">' + escapeHtml(title) + '</div>' +
            (message ? '<div class="toast-message">' + escapeHtml(message) + '</div>' : '') +
        '</div>' +
        '<button class="toast-close" onclick="this.closest(\'.toast\').remove()">' +
            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
        '</button>';

    toastContainer.appendChild(toast);

    // Auto-dismiss
    if (duration > 0) {
        setTimeout(function() {
            toast.classList.add('hiding');
            setTimeout(function() { 
                if (toast.parentNode) toast.remove(); 
            }, 300);
        }, duration);
    }
}

/* ── FAQ Accordion ───────────────────────────────────────── */
function initFAQ() {
    document.querySelectorAll('.faq-question').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var item = btn.closest('.faq-item');
            var wasActive = item.classList.contains('active');
            
            // Close all
            document.querySelectorAll('.faq-item.active').forEach(function(i) {
                i.classList.remove('active');
            });
            
            if (!wasActive) {
                item.classList.add('active');
            }
        });
    });
}

/* ── Alert Dismiss ───────────────────────────────────────── */
function initAlertDismiss() {
    document.querySelectorAll('.alert-dismiss').forEach(function(btn) {
        btn.addEventListener('click', function() {
            btn.closest('.alert').remove();
        });
    });
}

/* ── Confirmation Dialog ─────────────────────────────────── */
function confirmAction(message, callback) {
    var modalId = 'confirm-modal';
    var existing = document.getElementById(modalId);
    if (existing) existing.remove();

    var modal = document.createElement('div');
    modal.id = modalId;
    modal.className = 'modal-overlay';
    modal.innerHTML = 
        '<div class="modal">' +
            '<div class="modal-header">' +
                '<h3 class="modal-title">Confirm Action</h3>' +
                '<button class="modal-close" data-modal-close="' + modalId + '">' +
                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
                '</button>' +
            '</div>' +
            '<div class="modal-body">' +
                '<p style="color: var(--color-text); margin-bottom: 0;">' + escapeHtml(message) + '</p>' +
            '</div>' +
            '<div class="modal-footer">' +
                '<button class="btn btn-secondary" data-modal-close="' + modalId + '">Cancel</button>' +
                '<button class="btn btn-danger" id="confirm-action-btn">Confirm</button>' +
            '</div>' +
        '</div>';

    document.body.appendChild(modal);
    
    // Show
    setTimeout(function() { modal.classList.add('active'); }, 10);
    document.body.style.overflow = 'hidden';

    // Event listeners
    modal.querySelector('#confirm-action-btn').addEventListener('click', function() {
        closeModal(modalId);
        setTimeout(function() { modal.remove(); }, 300);
        if (typeof callback === 'function') callback();
    });

    modal.querySelectorAll('[data-modal-close]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            closeModal(modalId);
            setTimeout(function() { modal.remove(); }, 300);
        });
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal(modalId);
            setTimeout(function() { modal.remove(); }, 300);
        }
    });
}

/* ── Unsaved Changes Warning ─────────────────────────────── */
var formChanged = false;

function trackFormChanges(formId) {
    var form = document.getElementById(formId);
    if (!form) return;
    
    form.querySelectorAll('input, select, textarea').forEach(function(el) {
        el.addEventListener('change', function() { formChanged = true; });
        el.addEventListener('input', function() { formChanged = true; });
    });
}

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});

/* ── Utility Functions ───────────────────────────────────── */
function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function formatDate(dateStr) {
    var d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatDateTime(dateStr) {
    var d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { 
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function timeAgo(dateStr) {
    var now = new Date();
    var past = new Date(dateStr);
    var seconds = Math.floor((now - past) / 1000);
    
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
    if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';
    return formatDate(dateStr);
}

function countdownText(dateStr) {
    var now = new Date();
    var target = new Date(dateStr);
    var diff = target - now;
    
    if (diff <= 0) return 'Closed';
    
    var days = Math.floor(diff / 86400000);
    var hours = Math.floor((diff % 86400000) / 3600000);
    
    if (days > 0) return days + ' day' + (days !== 1 ? 's' : '') + ' left';
    if (hours > 0) return hours + ' hour' + (hours !== 1 ? 's' : '') + ' left';
    return 'Closing soon';
}
