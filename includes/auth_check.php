<?php
/**
 * CampusVoice — Authentication Guard
 * Include at the top of every protected page to verify session + role.
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

// Check if session has expired
if (isLoggedIn() && isSessionExpired()) {
    destroySession();
    header('Location: ' . BASE_URL . '/session-expired');
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Update last activity
updateLastActivity();

/**
 * Require a specific role to access the current page
 * @param string $requiredRole - 'student' or 'admin'
 */
function requireRole($requiredRole) {
    $currentRole = getCurrentRole();
    
    if ($currentRole !== $requiredRole) {
        // If admin tries to access student pages or vice versa
        header('Location: ' . BASE_URL . '/unauthorized');
        exit;
    }
}

/**
 * Require admin role (accepts both 'admin' and 'super_admin')
 */
function requireAdmin() {
    $currentRole = getCurrentRole();
    $adminRole = $_SESSION['admin_role'] ?? null;
    
    if ($currentRole !== ROLE_ADMIN && $currentRole !== ROLE_SUPER_ADMIN && $adminRole !== ROLE_SUPER_ADMIN) {
        header('Location: ' . BASE_URL . '/unauthorized');
        exit;
    }
}

/**
 * Require super admin role explicitly
 */
function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        header('Location: ' . BASE_URL . '/unauthorized');
        exit;
    }
}

/**
 * Check if the student must change their password first
 */
function checkPasswordChange() {
    if (getCurrentRole() === ROLE_STUDENT && isset($_SESSION['must_change_password']) && $_SESSION['must_change_password']) {
        // Allow access to change password page, or skip if the user chose not to change it now.
        $currentRoute = isset($_GET['route']) ? trim($_GET['route'], '/') : '';
        if ($currentRoute !== 'change-password' && empty($_SESSION['password_change_skipped'])) {
            header('Location: ' . BASE_URL . '/change-password');
            exit;
        }
    }
}
