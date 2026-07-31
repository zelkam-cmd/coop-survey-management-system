<?php
/**
 * CampusVoice — Edit Administrator Account
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../utils/helpers.php';

$adminId = (int)($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM administrators WHERE admin_id = ?");
$stmt->execute([$adminId]);
$adminAccount = $stmt->fetch();

if (!$adminAccount) {
    setToast('Error', 'Administrator account not found.', 'error');
    redirect(BASE_URL . '/admin/users');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'admin';
    $status = $_POST['status'] ?? 'active';

    if (empty($fullName)) {
        $error = 'Full Name is required.';
    } else {
        $stmt = $pdo->prepare("UPDATE administrators SET full_name = ?, email = ?, role = ?, status = ? WHERE admin_id = ?");
        $stmt->execute([$fullName, $email, $role, $status, $adminId]);

        logActivity(getCurrentUserId(), ROLE_ADMIN, 'edit_admin_user', 'Updated administrator account #' . $adminId);
        setToast('Success', 'Administrator account updated!', 'success');
        redirect(BASE_URL . '/admin/users');
    }
}

$pageTitle = 'Edit Administrator';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/users">Administrators</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Edit Administrator</div>
</div>

<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Edit Administrator: <?= e($adminAccount['username']) ?></h2>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: var(--space-5);"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="edit-admin-form" novalidate>
                <?php csrfField(); ?>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" value="<?= e($adminAccount['username']) ?>" disabled style="background: var(--color-gray-100);">
                </div>

                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" class="form-input" value="<?= e($adminAccount['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?= e($adminAccount['email']) ?>">
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">Role <span class="required">*</span></label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="admin" <?= $adminAccount['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        <option value="super_admin" <?= $adminAccount['role'] === 'super_admin' ? 'selected' : '' ?>>Super Administrator</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status <span class="required">*</span></label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="active" <?= $adminAccount['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $adminAccount['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
