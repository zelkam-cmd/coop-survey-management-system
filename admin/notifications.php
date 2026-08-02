<?php
/**
 * CampusVoice — Admin Notifications & Announcement Creator Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../utils/helpers.php';

$pdo = db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $target = $_POST['target'] ?? 'all';

    if (empty($title) || empty($content)) {
        $error = 'Title and content are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, target, is_active, created_by, created_at) VALUES (?, ?, ?, 1, ?, NOW())");
        $stmt->execute([$title, $content, $target, getCurrentUserId()]);

        logActivity(getCurrentUserId(), ROLE_ADMIN, 'create_announcement', 'Posted announcement: ' . $title);
        setToast('Success', 'Announcement posted successfully!', 'success');
        redirect(BASE_URL . '/admin/notifications');
    }
}

$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll();

$pageTitle = 'Notifications & Announcements';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Announcements</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">Campus Announcements</h2>
        <p class="content-subtitle">Post global announcements to student dashboards</p>
    </div>
    <div class="content-actions">
        <button class="btn btn-primary" onclick="openModal('add-announcement-modal')">+ Create Announcement</button>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-error mb-4"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($announcements)): ?>
            <div class="empty-state">
                <div class="empty-state-title">No announcements</div>
            </div>
        <?php else: ?>
            <div class="notification-list">
                <?php foreach ($announcements as $ann): ?>
                <div class="notification-item">
                    <div class="notification-icon" style="background: var(--color-primary-lighter); color: var(--color-primary);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </div>
                    <div class="notification-content">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="notification-title"><?= e($ann['title']) ?></div>
                            <span class="badge badge-info">Target: <?= e($ann['target']) ?></span>
                        </div>
                        <div class="notification-text"><?= e($ann['content']) ?></div>
                        <div class="notification-time"><?= formatDateTime($ann['created_at']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="add-announcement-modal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Post Announcement</h3>
            <button class="modal-close" data-modal-close="add-announcement-modal">✕</button>
        </div>
        <form method="POST">
            <?php csrfField(); ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="ann_title" class="form-label">Title <span class="required">*</span></label>
                    <input type="text" id="ann_title" name="title" class="form-input" required placeholder="e.g. Wi-Fi Survey Available">
                </div>

                <div class="form-group">
                    <label for="ann_target" class="form-label">Target Audience <span class="required">*</span></label>
                    <select id="ann_target" name="target" class="form-select" required>
                        <option value="all">All Users</option>
                        <option value="students">Students Only</option>
                        <option value="admins">Administrators Only</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ann_content" class="form-label">Announcement Content <span class="required">*</span></label>
                    <textarea id="ann_content" name="content" class="form-textarea" required placeholder="Type announcement text..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="add-announcement-modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Post Announcement</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
