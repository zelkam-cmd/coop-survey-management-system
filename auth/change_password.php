<?php
/**
 * CampusVoice — Change Password
 * Forced on first login for students; available anytime for all users.
 */

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../utils/helpers.php';

$error = '';
$success = '';
$isForced = isset($_SESSION['must_change_password']) && $_SESSION['must_change_password'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    if ($isForced && isset($_POST['skip_change'])) {
        $pdo = db();
        $userId = getCurrentUserId();

        $stmt = $pdo->prepare("UPDATE students SET must_change_password = 0 WHERE student_id = ?");
        $stmt->execute([$userId]);

        $_SESSION['must_change_password'] = false;
        $_SESSION['password_change_skipped'] = true;

        logActivity($userId, ROLE_STUDENT, 'skip_password_change', 'Chose to continue with the default password');
        setToast('Notice', 'You can continue using the default password and update it later from your profile.', 'info');

        redirect(BASE_URL . '/student/dashboard');
    }
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate
    if (empty($currentPassword) && !$isForced) {
        $error = 'Current password is required.';
    } elseif (empty($newPassword)) {
        $error = 'New password is required.';
    } elseif (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $newPassword)) {
        $error = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $error = 'Password must contain at least one number.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $pdo = db();
        $userId = getCurrentUserId();
        $role = getCurrentRole();
        
        // Get current hash
        if ($role === ROLE_STUDENT) {
            $stmt = $pdo->prepare("SELECT password_hash FROM students WHERE student_id = ?");
        } else {
            $stmt = $pdo->prepare("SELECT password_hash FROM administrators WHERE admin_id = ?");
        }
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $error = 'User not found.';
        } elseif (!$isForced && !password_verify($currentPassword, $user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } else {
            // Hash and update
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            
            if ($role === ROLE_STUDENT) {
                $stmt = $pdo->prepare("UPDATE students SET password_hash = ?, must_change_password = 0 WHERE student_id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE administrators SET password_hash = ? WHERE admin_id = ?");
            }
            $stmt->execute([$newHash, $userId]);
            
            // Clear the forced flag in session
            $_SESSION['must_change_password'] = false;
            
            // Log activity
            logActivity($userId, $role, 'change_password', 'Password changed successfully');
            
            setToast('Success', 'Your password has been changed successfully.', 'success');
            
            // Redirect to appropriate dashboard
            if ($role === ROLE_STUDENT) {
                redirect(BASE_URL . '/student/dashboard');
            } else {
                redirect(BASE_URL . '/admin/dashboard');
            }
        }
    }
}

$pageTitle = 'Change Password';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/<?= getCurrentRole() === ROLE_STUDENT ? 'student' : 'admin' ?>/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Change Password</div>
</div>

<div style="max-width: 520px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <?php if ($isForced): ?>
                    🔒 Set Your New Password
                <?php else: ?>
                    Change Password
                <?php endif; ?>
            </h2>
        </div>
        <div class="card-body">
            <?php if ($isForced): ?>
            <div class="alert alert-info" style="margin-bottom: var(--space-5);">
                <div class="alert-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                </div>
                <span>We recommend changing your password now for better security. You may continue with the default password and update it later from your profile.</span>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: var(--space-5);">
                <div class="alert-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <span><?= e($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" id="change-password-form" novalidate>
                <?php csrfField(); ?>
                
                <?php if (!$isForced): ?>
                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password <span class="required">*</span></label>
                    <input type="password" id="current_password" name="current_password" class="form-input" required autocomplete="current-password">
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="new_password" class="form-label">New Password <span class="required">*</span></label>
                    <input type="password" id="new_password" name="new_password" class="form-input" required 
                           data-minlength="<?= PASSWORD_MIN_LENGTH ?>" autocomplete="new-password">
                    <div class="form-hint">Must be at least <?= PASSWORD_MIN_LENGTH ?> characters with uppercase, lowercase, and a number.</div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm New Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required autocomplete="new-password">
                </div>

                <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Change Password
                    </button>
                    <?php if (!$isForced): ?>
                    <a href="<?= BASE_URL ?>/<?= getCurrentRole() === ROLE_STUDENT ? 'student' : 'admin' ?>/dashboard" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($isForced): ?>
            <form method="POST" style="margin-top: var(--space-4); display: inline-block;">
                <?php csrfField(); ?>
                <button type="submit" name="skip_change" class="btn btn-secondary">Continue without changing</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('change-password-form').addEventListener('submit', function(e) {
        if (!validateForm('change-password-form')) {
            e.preventDefault();
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
