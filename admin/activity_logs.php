<?php
/**
 * CampusVoice — Activity Logs Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../utils/helpers.php';

$pdo = db();
$stmt = $pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 100");
$logs = $stmt->fetchAll();

$pageTitle = 'Activity Audit Logs';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Activity Logs</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">System Activity Logs</h2>
        <p class="content-subtitle">Audit trail of actions taken across the platform</p>
    </div>
</div>

<div class="data-table-wrapper">
    <div class="data-table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User ID</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: var(--space-8);">No activity recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td style="font-size: var(--font-size-xs); color: var(--color-text-secondary);"><?= formatDateTime($log['created_at']) ?></td>
                        <td>User #<?= $log['user_id'] ?></td>
                        <td>
                            <span class="badge <?= $log['role'] === 'admin' ? 'badge-primary' : 'badge-secondary' ?>">
                                <?= e($log['role']) ?>
                            </span>
                        </td>
                        <td><span class="font-mono" style="font-size: var(--font-size-xs);"><?= e($log['action']) ?></span></td>
                        <td><?= e($log['description']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
