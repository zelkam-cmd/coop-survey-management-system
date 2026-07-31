/**
 * CampusVoice — Client-Side Form Validation
 * Validates all forms before submit; server-side PHP re-validates authoritatively.
 */

/**
 * Validate an entire form
 * @param {string} formId - The form element ID
 * @returns {boolean} True if valid
 */
function validateForm(formId) {
    var form = document.getElementById(formId);
    if (!form) return false;

    var isValid = true;
    clearFormErrors(formId);

    // Validate all required fields
    form.querySelectorAll('[required], [data-required]').forEach(function(field) {
        if (!validateField(field)) {
            isValid = false;
        }
    });

    // Validate email fields
    form.querySelectorAll('input[type="email"]').forEach(function(field) {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address.');
            isValid = false;
        }
    });

    // Validate password fields
    var newPassword = form.querySelector('#new_password, #password');
    var confirmPassword = form.querySelector('#confirm_password');
    
    if (newPassword && newPassword.value) {
        if (!validatePassword(newPassword)) {
            isValid = false;
        }
    }
    
    if (confirmPassword && newPassword && confirmPassword.value !== newPassword.value) {
        showFieldError(confirmPassword, 'Passwords do not match.');
        isValid = false;
    }

    // Show summary if errors exist
    if (!isValid) {
        showFormError(formId, 'Please fix the errors below before submitting.');
    }

    return isValid;
}

/**
 * Validate a single field
 * @param {HTMLElement} field
 * @returns {boolean}
 */
function validateField(field) {
    var value = field.value.trim();
    
    // Required check
    if ((field.hasAttribute('required') || field.dataset.required) && !value) {
        var label = getFieldLabel(field);
        showFieldError(field, label + ' is required.');
        return false;
    }

    // Min length
    if (field.dataset.minlength && value.length < parseInt(field.dataset.minlength)) {
        showFieldError(field, 'Must be at least ' + field.dataset.minlength + ' characters.');
        return false;
    }

    // Max length
    if (field.maxLength > 0 && value.length > field.maxLength) {
        showFieldError(field, 'Must not exceed ' + field.maxLength + ' characters.');
        return false;
    }

    // Pattern
    if (field.pattern && value && !new RegExp(field.pattern).test(value)) {
        showFieldError(field, field.dataset.patternMessage || 'Invalid format.');
        return false;
    }

    // Number range
    if (field.type === 'number') {
        var num = parseFloat(value);
        if (field.min && num < parseFloat(field.min)) {
            showFieldError(field, 'Value must be at least ' + field.min + '.');
            return false;
        }
        if (field.max && num > parseFloat(field.max)) {
            showFieldError(field, 'Value must not exceed ' + field.max + '.');
            return false;
        }
    }

    return true;
}

/**
 * Validate password strength
 */
function validatePassword(field) {
    var password = field.value;
    var isValid = true;

    if (password.length < 8) {
        showFieldError(field, 'Password must be at least 8 characters long.');
        isValid = false;
    } else if (!/[A-Z]/.test(password)) {
        showFieldError(field, 'Password must contain at least one uppercase letter.');
        isValid = false;
    } else if (!/[a-z]/.test(password)) {
        showFieldError(field, 'Password must contain at least one lowercase letter.');
        isValid = false;
    } else if (!/[0-9]/.test(password)) {
        showFieldError(field, 'Password must contain at least one number.');
        isValid = false;
    }

    return isValid;
}

/**
 * Validate survey form — all required questions answered
 */
function validateSurveyForm(formId) {
    var form = document.getElementById(formId);
    if (!form) return false;

    var isValid = true;
    clearFormErrors(formId);

    // Check each required question
    form.querySelectorAll('.question-card[data-required="1"]').forEach(function(card) {
        var questionId = card.dataset.questionId;
        var questionType = card.dataset.questionType;
        var answered = false;

        if (questionType === 'multiple_choice' || questionType === 'yes_no') {
            answered = card.querySelector('input[type="radio"]:checked') !== null;
        } else if (questionType === 'rating') {
            answered = card.querySelector('input[name="rating_' + questionId + '"]') !== null && 
                       card.querySelector('input[name="rating_' + questionId + '"]').value > 0;
            // Also check hidden input
            var ratingInput = card.querySelector('input[type="hidden"][name="answer[' + questionId + ']"]');
            if (ratingInput) {
                answered = ratingInput.value !== '' && parseInt(ratingInput.value) >= 1 && parseInt(ratingInput.value) <= 5;
            }
        } else if (questionType === 'short_answer') {
            var textarea = card.querySelector('textarea');
            answered = textarea && textarea.value.trim() !== '';
        }

        if (!answered) {
            card.classList.add('error');
            var errorDiv = document.createElement('div');
            errorDiv.className = 'form-error';
            errorDiv.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> This question is required.';
            card.appendChild(errorDiv);
            isValid = false;
        }
    });

    // Validate rating values are in range
    form.querySelectorAll('input[name^="answer["][data-type="rating"]').forEach(function(input) {
        var val = parseInt(input.value);
        if (input.value && (val < 1 || val > 5 || isNaN(val))) {
            var card = input.closest('.question-card');
            if (card) {
                card.classList.add('error');
                var errorDiv = document.createElement('div');
                errorDiv.className = 'form-error';
                errorDiv.textContent = 'Rating must be between 1 and 5.';
                card.appendChild(errorDiv);
            }
            isValid = false;
        }
    });

    if (!isValid) {
        showFormError(formId, 'Please answer all required questions before submitting.');
        // Scroll to first error
        var firstError = form.querySelector('.question-card.error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    return isValid;
}

/* ── Helper Functions ────────────────────────────────────── */

function showFieldError(field, message) {
    field.classList.add('error');
    
    var errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> ' + escapeHtml(message);
    
    var group = field.closest('.form-group');
    if (group) {
        group.appendChild(errorDiv);
    } else {
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
}

function showFormError(formId, message) {
    var form = document.getElementById(formId);
    if (!form) return;

    // Remove existing banner
    var existing = form.querySelector('.form-error-banner');
    if (existing) existing.remove();

    var banner = document.createElement('div');
    banner.className = 'alert alert-error form-error-banner';
    banner.style.marginBottom = 'var(--space-4)';
    banner.innerHTML = '<div class="alert-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div><span>' + escapeHtml(message) + '</span>';
    
    form.insertBefore(banner, form.firstChild);
    banner.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function clearFormErrors(formId) {
    var form = document.getElementById(formId);
    if (!form) return;

    form.querySelectorAll('.form-error').forEach(function(el) { el.remove(); });
    form.querySelectorAll('.form-error-banner').forEach(function(el) { el.remove(); });
    form.querySelectorAll('.error').forEach(function(el) { el.classList.remove('error'); });
}

function getFieldLabel(field) {
    var label = field.closest('.form-group');
    if (label) {
        var labelEl = label.querySelector('.form-label');
        if (labelEl) return labelEl.textContent.replace('*', '').trim();
    }
    return field.placeholder || field.name || 'This field';
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Disable submit button and show spinner (prevent double-submit)
 */
function disableSubmitButton(btn) {
    btn.disabled = true;
    btn.dataset.originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span> Submitting...';
}

/**
 * Re-enable submit button
 */
function enableSubmitButton(btn) {
    btn.disabled = false;
    if (btn.dataset.originalText) {
        btn.innerHTML = btn.dataset.originalText;
    }
}

/**
 * Toggle password visibility
 */
function togglePasswordVisibility(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    }
}

// Real-time validation on blur
document.addEventListener('focusout', function(e) {
    var field = e.target;
    if (field.matches && field.matches('input, select, textarea')) {
        if (field.classList.contains('error')) {
            // Clear and re-validate this field only
            field.classList.remove('error');
            var group = field.closest('.form-group');
            if (group) {
                var error = group.querySelector('.form-error');
                if (error) error.remove();
            }
            validateField(field);
        }
    }
});
