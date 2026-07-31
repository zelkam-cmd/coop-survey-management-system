<?php
/**
 * CampusVoice — CSRF Token Protection
 * Generates and validates CSRF tokens for all state-changing forms.
 */

require_once __DIR__ . '/../config/session.php';

/**
 * Generate a CSRF token and store it in the session
 * @return string The generated token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF field for forms
 */
function csrfField() {
    $token = generateCSRFToken();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Validate the submitted CSRF token
 * @return bool True if valid
 */
function validateCSRFToken() {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    $isValid = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    
    // Regenerate token after validation (single-use)
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    return $isValid;
}

/**
 * Validate CSRF and die with error if invalid
 */
function requireCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validateCSRFToken()) {
            http_response_code(403);
            die('Invalid security token. Please refresh the page and try again.');
        }
    }
}
