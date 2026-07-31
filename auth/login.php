<?php
/**
 * CampusVoice — Login Page
 * Shared login form for Students and Administrators.
 * Role is auto-detected based on which table matches the identifier.
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../utils/helpers.php';

// If already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    if (getCurrentRole() === ROLE_STUDENT) {
        redirect(BASE_URL . '/student/dashboard');
    } else {
        redirect(BASE_URL . '/admin/dashboard');
    }
}

$error = '';
$username = '';

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $pdo = db();
            $authenticated = false;
            $userData = null;
            $role = null;
            
            // 1. Try student login (by student_number)
            $stmt = $pdo->prepare("SELECT student_id, student_number, full_name, email, password_hash, must_change_password, status FROM students WHERE student_number = ? LIMIT 1");
            $stmt->execute([$username]);
            $student = $stmt->fetch();
            
            if ($student) {
                if ($student['status'] !== 'active') {
                    $error = 'Your account has been deactivated. Please contact the administrator.';
                } elseif (password_verify($password, $student['password_hash'])) {
                    $authenticated = true;
                    $role = ROLE_STUDENT;
                    $userData = [
                        'user_id'   => $student['student_id'],
                        'full_name' => $student['full_name'],
                        'email'     => $student['email'],
                        'must_change_password' => $student['must_change_password'],
                    ];
                } else {
                    $error = 'Invalid username or password.';
                }
            }
            
            // 2. If not a student, try admin login (by username)
            if (!$student && !$authenticated) {
                $stmt = $pdo->prepare("SELECT admin_id, username, full_name, email, password_hash, role, status FROM administrators WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $admin = $stmt->fetch();
                
                if ($admin) {
                    if ($admin['status'] !== 'active') {
                        $error = 'Your account has been deactivated.';
                    } elseif (password_verify($password, $admin['password_hash'])) {
                        $authenticated = true;
                        $role = ROLE_ADMIN;
                        $userData = [
                            'user_id'   => $admin['admin_id'],
                            'full_name' => $admin['full_name'],
                            'email'     => $admin['email'],
                            'admin_role' => $admin['role'],
                        ];
                    } else {
                        $error = 'Invalid username or password.';
                    }
                } else {
                    $error = 'Invalid username or password.';
                }
            }
            
            // 3. If authenticated, set up session
            if ($authenticated && $userData) {
                // Regenerate session ID to prevent fixation
                regenerateSession();
                
                $_SESSION['user_id']   = $userData['user_id'];
                $_SESSION['role']      = $role;
                $_SESSION['full_name'] = $userData['full_name'];
                $_SESSION['email']     = $userData['email'];
                
                if ($role === ROLE_STUDENT) {
                    $_SESSION['must_change_password'] = $userData['must_change_password'];
                }
                
                if ($role === ROLE_ADMIN) {
                    $_SESSION['admin_role'] = $userData['admin_role'];
                }
                
                // Log login to history
                try {
                    $logStmt = $pdo->prepare("INSERT INTO login_history (user_id, role, action, ip_address, user_agent) VALUES (?, ?, 'login', ?, ?)");
                    $logStmt->execute([
                        $userData['user_id'],
                        $role,
                        $_SERVER['REMOTE_ADDR'] ?? '',
                        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
                    ]);
                } catch (PDOException $e) {
                    // Non-critical, just log
                    error_log("Login history log error: " . $e->getMessage());
                }
                
                // Redirect
                if ($role === ROLE_STUDENT && !empty($userData['must_change_password'])) {
                    redirect(BASE_URL . '/change-password');
                } elseif ($role === ROLE_STUDENT) {
                    redirect(BASE_URL . '/student/dashboard');
                } else {
                    redirect(BASE_URL . '/admin/dashboard');
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= APP_TAGLINE ?>">
    <title>Login — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pages.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
</head>
<body>
    <div class="public-layout">
        <!-- Header -->
        <header class="public-header">
            <a href="<?= BASE_URL ?>/" class="public-logo">
                <div class="public-logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
                <span class="public-logo-text"><?= APP_NAME ?></span>
            </a>
        </header>

        <!-- Login Form -->
        <div class="public-content">
            <div class="login-container">
                <div class="login-card">
                    <div class="login-header">
                        <div class="login-logo">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                        </div>
                        <h1 class="login-title">Welcome back</h1>
                        <p class="login-subtitle">Sign in to your <?= APP_NAME ?> account</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: var(--space-5);">
                        <div class="alert-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <span><?= e($error) ?></span>
                    </div>
                    <?php endif; ?>

                    <form method="POST" id="login-form" novalidate>
                        <?php csrfField(); ?>
                        
                        <div class="form-group">
                            <label for="username" class="form-label">Username / Student ID <span class="required">*</span></label>
                            <input type="text" id="username" name="username" class="form-input" 
                                   placeholder="Enter your Student ID or admin username" 
                                   value="<?= e($username) ?>" required autofocus autocomplete="username">
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password <span class="required">*</span></label>
                            <div style="position: relative;">
                                <input type="password" id="password" name="password" class="form-input" 
                                       placeholder="Enter your password" required autocomplete="current-password"
                                       style="padding-right: 44px;">
                                <button type="button" class="btn-icon" 
                                        style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%); color: var(--color-text-tertiary);"
                                        onclick="togglePasswordVisibility('password', this)"
                                        aria-label="Toggle password visibility">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-full" id="login-btn" style="margin-top: var(--space-2);">
                            Sign In
                        </button>
                    </form>

                    <p style="text-align: center; margin-top: var(--space-6); font-size: var(--font-size-xs); color: var(--color-text-tertiary); margin-bottom: 0;">
                        Students: Use your Student ID as both username and default password.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="public-footer">
            &copy; <?= date('Y') ?> <?= APP_NAME ?> — <?= APP_SCHOOL ?>. All rights reserved.
        </footer>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
    <script>
        // Prevent double-submit
        document.getElementById('login-form').addEventListener('submit', function(e) {
            var btn = document.getElementById('login-btn');
            if (btn.disabled) {
                e.preventDefault();
                return;
            }
            
            var username = document.getElementById('username').value.trim();
            var password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                showToast('Validation Error', 'Please enter both username and password.', 'error');
                return;
            }
            
            disableSubmitButton(btn);
        });
    </script>
</body>
</html>
