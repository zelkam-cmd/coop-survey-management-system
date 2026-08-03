<?php
/**
 * CampusVoice — Student Notifications Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(ROLE_STUDENT);
checkPasswordChange();
require_once __DIR__ . '/../utils/helpers.php';

$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE is_active = 1 AND (target = 'all' OR target = 'students') ORDER BY created_at DESC");
$stmt->execute();
$announcements = $stmt->fetchAll();

$pageTitle = 'Notifications & Announcements';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/student/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Notifications</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">Campus Announcements</h2>
        <p class="content-subtitle">Stay updated with latest survey announcements and campus notices</p>
    </div>
</div>

<div class="card">
    <div class="card-body" id="student-notifications-feed">
        <?php if (empty($announcements)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <div class="empty-state-title">No notifications yet</div>
                <p class="empty-state-description">Check back later for system announcements and survey updates.</p>
            </div>
        <?php else: ?>
            <div class="notification-list">
                <?php foreach ($announcements as $ann): ?>
                <div class="notification-item">
                    <div class="notification-icon" style="background: var(--color-primary-lighter); color: var(--color-primary);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title"><?= e($ann['title']) ?></div>
                        <div class="notification-text"><?= e($ann['content']) ?></div>
                        <div class="notification-time"><?= formatDateTime($ann['created_at']) ?> (<?= timeAgo($ann['created_at']) ?>)</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
