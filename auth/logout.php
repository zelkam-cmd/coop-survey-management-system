<?php
/**
 * CampusVoice — Secure Logout
 * Fully destroys the session, clears cookies, and redirects to login.
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';

// Log the logout event
if (isLoggedIn()) {
    try {
        require_once __DIR__ . '/../config/db.php';
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO login_history (user_id, role, action, ip_address) VALUES (?, ?, 'logout', ?)");
        $stmt->execute([getCurrentUserId(), getCurrentRole(), $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (Exception $e) {
        // Non-critical
        error_log("Logout log error: " . $e->getMessage());
    }
}

// Destroy session completely
destroySession();

// Redirect to login
header('Location: ' . BASE_URL . '/login');
exit;
