<?php
/**
 * CampusVoice — Admin Profile Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../utils/helpers.php';

$pdo = db();
$adminId = getCurrentUserId();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($fullName)) {
        $error = 'Full Name is required.';
    } else {
        $stmt = $pdo->prepare("UPDATE administrators SET full_name = ?, email = ? WHERE admin_id = ?");
        $stmt->execute([$fullName, $email, $adminId]);

        $_SESSION['full_name'] = $fullName;
        $_SESSION['email'] = $email;

        logActivity($adminId, ROLE_ADMIN, 'update_admin_profile', 'Updated profile details');
        setToast('Success', 'Profile updated successfully!', 'success');
        redirect(BASE_URL . '/admin/profile');
    }
}

$stmt = $pdo->prepare("SELECT * FROM administrators WHERE admin_id = ?");
$stmt->execute([$adminId]);
$adminUser = $stmt->fetch();

$pageTitle = 'Admin Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Admin Profile</div>
</div>

<div class="dashboard-grid">
    <div>
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar"><?= getInitials($adminUser['full_name']) ?></div>
                <div class="profile-info">
                    <h2><?= e($adminUser['full_name']) ?></h2>
                    <p>Username: <strong><?= e($adminUser['username']) ?></strong> (<?= e($adminUser['role']) ?>)</p>
                </div>
            </div>
            <div class="profile-body">
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Full Name:</div>
                    <div class="profile-detail-value"><?= e($adminUser['full_name']) ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Username:</div>
                    <div class="profile-detail-value"><?= e($adminUser['username']) ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Email:</div>
                    <div class="profile-detail-value"><?= e($adminUser['email'] ?: 'Not set') ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Role:</div>
                    <div class="profile-detail-value"><span class="badge badge-primary"><?= e($adminUser['role']) ?></span></div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Update Profile</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-error mb-4"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?php csrfField(); ?>
                    <div class="form-group">
                        <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="form-input" value="<?= e($adminUser['full_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" value="<?= e($adminUser['email']) ?>">
                    </div>

                    <button type="submit" class="btn btn-primary w-full">Save Profile Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
