<?php
/**
 * CampusVoice — Add Administrator Account
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../utils/helpers.php';

$error = '';
$username = '';
$fullName = '';
$email = '';
$role = 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'admin';

    if (empty($username) || empty($fullName) || empty($password)) {
        $error = 'Username, Full Name, and Password are required.';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM administrators WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'An administrator with this username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO administrators (username, full_name, email, password_hash, role, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$username, $fullName, $email, $hash, $role]);

            logActivity(getCurrentUserId(), ROLE_ADMIN, 'add_admin_user', 'Created administrator account: ' . $username);
            setToast('Success', 'Administrator account created successfully!', 'success');
            redirect(BASE_URL . '/admin/users');
        }
    }
}

$pageTitle = 'Add Administrator';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/users">Administrators</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Add Administrator</div>
</div>

<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Add Administrator Account</h2>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: var(--space-5);"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="add-admin-form" novalidate>
                <?php csrfField(); ?>

                <div class="form-group">
                    <label for="username" class="form-label">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" class="form-input" value="<?= e($username) ?>" required autofocus>
                </div>

                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" class="form-input" value="<?= e($fullName) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?= e($email) ?>">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Initial Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">Role <span class="required">*</span></label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        <option value="super_admin" <?= $role === 'super_admin' ? 'selected' : '' ?>>Super Administrator</option>
                    </select>
                </div>

                <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
                    <button type="submit" class="btn btn-primary">Create Administrator</button>
                    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
