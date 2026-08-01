<?php
/**
 * CampusVoice — Announcement Management Action Handler
 * Handles CRUD operations and status toggling for announcements.
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../utils/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $action = $_POST['action'] ?? '';
    $pdo = db();
    $adminId = getCurrentUserId();

    switch ($action) {
        case 'create':
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $target = $_POST['target'] ?? 'all';
            $isActive = isset($_POST['is_active']) ? 1 : 1;

            if (!empty($title) && !empty($content)) {
                $stmt = $pdo->prepare("
                    INSERT INTO announcements (title, content, target, is_active, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$title, $content, $target, $isActive, $adminId]);
                $announcementId = $pdo->lastInsertId();

                logActivity($adminId, ROLE_ADMIN, 'create_announcement', 'Created announcement: ' . $title, 'announcement', $announcementId);
                setToast('Success', 'Announcement published successfully!', 'success');
            } else {
                setToast('Error', 'Announcement title and content are required.', 'error');
            }
            break;

        case 'edit':
            $announcementId = (int)($_POST['announcement_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $target = $_POST['target'] ?? 'all';

            if ($announcementId > 0 && !empty($title) && !empty($content)) {
                $stmt = $pdo->prepare("
                    UPDATE announcements 
                    SET title = ?, content = ?, target = ?
                    WHERE announcement_id = ?
                ");
                $stmt->execute([$title, $content, $target, $announcementId]);

                logActivity($adminId, ROLE_ADMIN, 'edit_announcement', 'Updated announcement #' . $announcementId, 'announcement', $announcementId);
                setToast('Success', 'Announcement updated successfully!', 'success');
            } else {
                setToast('Error', 'Failed to update announcement. Missing required fields.', 'error');
            }
            break;

        case 'toggle_publish':
            $announcementId = (int)($_POST['announcement_id'] ?? 0);
            if ($announcementId > 0) {
                $stmt = $pdo->prepare("UPDATE announcements SET is_active = NOT is_active WHERE announcement_id = ?");
                $stmt->execute([$announcementId]);

                logActivity($adminId, ROLE_ADMIN, 'toggle_announcement', 'Toggled status for announcement #' . $announcementId, 'announcement', $announcementId);
                setToast('Success', 'Announcement status updated!', 'success');
            }
            break;

        case 'delete':
            $announcementId = (int)($_POST['announcement_id'] ?? 0);
            if ($announcementId > 0) {
                $stmt = $pdo->prepare("DELETE FROM announcements WHERE announcement_id = ?");
                $stmt->execute([$announcementId]);

                logActivity($adminId, ROLE_ADMIN, 'delete_announcement', 'Deleted announcement #' . $announcementId, 'announcement', $announcementId);
                setToast('Success', 'Announcement deleted successfully!', 'success');
            }
            break;
    }
}

// Redirect back to Admin Dashboard
redirect(BASE_URL . '/admin/dashboard');
