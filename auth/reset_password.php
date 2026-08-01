<?php
/**
 * CampusVoice — Student Password Reset Request & Reset Flow
 */
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../utils/helpers.php';

$error = '';
$success = '';
$studentNumber = trim($_GET['student_number'] ?? $_POST['student_number'] ?? '');
$email = trim($_POST['email'] ?? '');
$step = isset($_POST['new_password']) ? 2 : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $studentNumber = trim($_POST['student_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $pdo = db();
    
    if (empty($studentNumber)) {
        $error = 'Please enter your Student ID Number.';
    } else {
        // Verify student exists
        $stmt = $pdo->prepare("SELECT student_id, student_number, full_name, email FROM students WHERE student_number = ? LIMIT 1");
        $stmt->execute([$studentNumber]);
        $student = $stmt->fetch();
        
        if (!$student) {
            $error = 'No student account found with that Student ID.';
        } elseif (!empty($newPassword)) {
            // Password update phase
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } else {
                $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $updateStmt = $pdo->prepare("UPDATE students SET password_hash = ?, must_change_password = 0 WHERE student_id = ?");
                $updateStmt->execute([$newHash, $student['student_id']]);
                
                logActivity($student['student_id'], ROLE_STUDENT, 'reset_own_password', 'Student reset password successfully');
                setToast('Success', 'Your password has been reset! You may now sign in with your new password.', 'success');
                redirect(BASE_URL . '/login');
            }
        } else {
            // Student verified, ready for new password
            $step = 2;
            $success = 'Account verified! Please set your new password below.';
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
    <title>Reset Password — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/layout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pages.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css?v=<?= time() ?>">
</head>
<body class="public-body">
    <div class="ambient-blob-center"></div>
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

        <!-- Content -->
        <div class="public-content">
            <div class="login-container">
                <div class="login-card glass-card">
                    <div class="login-header">
                        <div class="login-logo">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <h1 class="login-title">Reset Password</h1>
                        <p class="login-subtitle">Enter your Student ID to reset your student account password</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: var(--space-5);">
                        <div class="alert-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <span><?= e($error) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: var(--space-5);">
                        <div class="alert-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <span><?= e($success) ?></span>
                    </div>
                    <?php endif; ?>

                    <form method="POST" id="reset-password-form" novalidate>
                        <?php csrfField(); ?>
                        
                        <div class="form-group">
                            <label for="student_number" class="form-label">Student ID Number <span class="required">*</span></label>
                            <input type="text" id="student_number" name="student_number" class="form-input" 
                                   placeholder="e.g. STU-2024-001" 
                                   value="<?= e($studentNumber) ?>" required <?= $step === 2 ? 'readonly style="background: rgba(0,0,0,0.03);"' : 'autofocus' ?>>
                        </div>

                        <?php if ($step === 2): ?>
                        <div class="form-group">
                            <label for="new_password" class="form-label">New Password <span class="required">*</span></label>
                            <input type="password" id="new_password" name="new_password" class="form-input" 
                                   placeholder="Enter new password (min. 8 chars)" required autofocus>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password <span class="required">*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                                   placeholder="Re-enter new password" required>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary btn-lg w-full" style="margin-top: var(--space-2);">
                            <?= $step === 2 ? 'Update Password' : 'Verify Account & Continue' ?>
                        </button>
                    </form>

                    <p style="text-align: center; margin-top: var(--space-6); font-size: var(--font-size-sm); margin-bottom: 0;">
                        <a href="<?= BASE_URL ?>/login" class="text-primary font-semibold">← Back to Sign In</a>
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
</body>
</html>
