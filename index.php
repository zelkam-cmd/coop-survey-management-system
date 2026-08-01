<?php
/**
 * CampusVoice — Central Router
 * Dispatches requests to the appropriate page based on URL.
 */

require_once __DIR__ . '/config/constants.php';

// Check maintenance mode first
if (MAINTENANCE_MODE) {
    require_once __DIR__ . '/system-pages/maintenance.php';
    exit;
}

// Get the requested route
$route = isset($_GET['route']) ? trim($_GET['route'], '/') : '';

// Route mapping
switch ($route) {
    // ── Public Pages ────────────────────────────────────
    case '':
    case 'home':
    case 'landing':
        require_once __DIR__ . '/pages/landing.php';
        break;

    case 'login':
        require_once __DIR__ . '/auth/login.php';
        break;

    case 'logout':
        require_once __DIR__ . '/auth/logout.php';
        break;

    case 'reset-password':
    case 'forgot-password':
    case 'student/reset-password':
        require_once __DIR__ . '/auth/reset_password.php';
        break;

    case 'change-password':
        require_once __DIR__ . '/auth/change_password.php';
        break;

    // ── Student Pages ───────────────────────────────────
    case 'student/dashboard':
        require_once __DIR__ . '/student/dashboard.php';
        break;

    case 'student/surveys':
        require_once __DIR__ . '/student/surveys.php';
        break;

    case 'student/notifications':
        require_once __DIR__ . '/student/notifications.php';
        break;

    case 'student/help':
        require_once __DIR__ . '/student/help.php';
        break;

    case 'student/profile':
        require_once __DIR__ . '/student/profile.php';
        break;

    // ── Admin Pages ─────────────────────────────────────
    case 'admin/dashboard':
        require_once __DIR__ . '/admin/dashboard.php';
        break;

    case 'admin/surveys':
        require_once __DIR__ . '/admin/surveys/index.php';
        break;

    case 'admin/surveys/create':
        require_once __DIR__ . '/admin/surveys/create.php';
        break;

    case 'admin/students':
        require_once __DIR__ . '/admin/students/index.php';
        break;

    case 'admin/students/add':
    case 'admin/students/create':
        require_once __DIR__ . '/admin/students/add.php';
        break;

    case 'admin/students/reset_password':
        require_once __DIR__ . '/admin/students/reset_password.php';
        break;

    case 'admin/results':
        require_once __DIR__ . '/admin/results/index.php';
        break;

    case 'admin/results/compute':
        require_once __DIR__ . '/admin/results/compute.php';
        break;

    case 'admin/reports':
        require_once __DIR__ . '/admin/reports/index.php';
        break;

    case 'admin/users':
        require_once __DIR__ . '/admin/users/index.php';
        break;

    case 'admin/users/add':
    case 'admin/users/create':
        require_once __DIR__ . '/admin/users/add.php';
        break;

    case 'admin/users/edit':
        require_once __DIR__ . '/admin/users/edit.php';
        break;

    case 'admin/announcements/action':
        require_once __DIR__ . '/admin/announcements/action.php';
        break;
    case 'admin/profile':
        require_once __DIR__ . '/admin/profile.php';
        break;

    case 'admin/notifications':
        require_once __DIR__ . '/admin/notifications.php';
        break;

    case 'admin/settings':
        require_once __DIR__ . '/admin/settings.php';
        break;

    case 'admin/activity-logs':
        require_once __DIR__ . '/admin/activity_logs.php';
        break;

    // ── System Pages ────────────────────────────────────
    case '401':
    case 'unauthorized':
        require_once __DIR__ . '/system-pages/401.php';
        break;

    case 'session-expired':
        require_once __DIR__ . '/system-pages/session_expired.php';
        break;

    case 'maintenance':
        require_once __DIR__ . '/system-pages/maintenance.php';
        break;

    case 'error':
        require_once __DIR__ . '/system-pages/error.php';
        break;

    default:
        // Dynamic routes with parameters
        if (preg_match('/^student\/surveys\/(\d+)$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/student/survey_form.php';
        } elseif (preg_match('/^student\/surveys\/(\d+)\/confirmation$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/student/confirmation.php';
        } elseif (preg_match('/^student\/surveys\/(\d+)\/closed$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/student/survey_closed.php';
        } elseif (preg_match('/^admin\/surveys\/(\d+)\/edit$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/admin/surveys/edit.php';
        } elseif (preg_match('/^admin\/surveys\/(\d+)\/questions$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/admin/surveys/questions.php';
        } elseif (preg_match('/^admin\/surveys\/(\d+)\/deactivate$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/admin/surveys/deactivate.php';
        } elseif (preg_match('/^admin\/students\/(\d+)$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/admin/students/detail.php';
        } elseif (preg_match('/^admin\/students\/(\d+)\/reset-password$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/admin/students/reset_password.php';
        } elseif (preg_match('/^admin\/users\/(\d+)\/edit$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/admin/users/edit.php';
        } elseif (preg_match('/^admin\/results\/(\d+)$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/admin/results/view.php';
        } elseif (preg_match('/^admin\/results\/(\d+)\/compute$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/admin/results/compute.php';
        } elseif (preg_match('/^admin\/reports\/(\d+)\/print$/', $route, $matches)) {
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/admin/reports/print.php';
        } elseif (preg_match('/^admin\/reports\/export$/', $route, $matches)) {
            require_once __DIR__ . '/admin/reports/export.php';
        } else {
            // 404 — page not found
            http_response_code(404);
            require_once __DIR__ . '/system-pages/404.php';
        }
        break;
}
