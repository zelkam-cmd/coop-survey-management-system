<?php
/**
 * CampusVoice — Administrator Account Management
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';

$pdo = db();
$stmt = $pdo->query("SELECT * FROM administrators ORDER BY created_at DESC");
$admins = $stmt->fetchAll();

$pageTitle = 'Administrator Accounts';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Administrator Accounts</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">Administrator Accounts</h2>
        <p class="content-subtitle">Manage system staff accounts, roles, and administrative access</p>
    </div>
    <div class="content-actions">
        <a href="<?= BASE_URL ?>/admin/users/add" class="btn btn-primary">+ Add New Administrator</a>
    </div>
</div>

<div class="data-table-wrapper">
    <div class="data-table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $adm): ?>
                <tr>
                    <td class="font-semibold"><?= e($adm['username']) ?></td>
                    <td><?= e($adm['full_name']) ?></td>
                    <td><?= e($adm['email']) ?></td>
                    <td><span class="badge badge-primary"><?= e($adm['role']) ?></span></td>
                    <td><?= getUserStatusBadge($adm['status']) ?></td>
                    <td><?= formatDate($adm['created_at']) ?></td>
                    <td style="text-align: right;">
                        <a href="<?= BASE_URL ?>/admin/users/edit?id=<?= $adm['admin_id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
