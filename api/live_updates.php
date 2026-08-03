<?php
/**
 * CampusVoice — Live Updates API Endpoint
 * Returns JSON payload of active announcements and statistics for AJAX polling.
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$pdo = db();
$currentRole = getCurrentRole();

if ($currentRole === ROLE_ADMIN) {
    // Admin sees all announcements
    $stmt = $pdo->query("
        SELECT a.*, adm.full_name as author_name 
        FROM announcements a 
        LEFT JOIN administrators adm ON a.created_by = adm.admin_id 
        ORDER BY a.created_at DESC 
        LIMIT 10
    ");
    $announcements = $stmt->fetchAll();
} else {
    // Student sees active announcements targeted at 'all' or 'students'
    $stmt = $pdo->prepare("
        SELECT a.*, adm.full_name as author_name 
        FROM announcements a 
        LEFT JOIN administrators adm ON a.created_by = adm.admin_id 
        WHERE a.is_active = 1 AND (a.target = 'all' OR a.target = 'students') 
        ORDER BY a.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $announcements = $stmt->fetchAll();
}

$formattedAnnouncements = [];
foreach ($announcements as $ann) {
    $formattedAnnouncements[] = [
        'announcement_id' => (int)$ann['announcement_id'],
        'title'           => htmlspecialchars($ann['title']),
        'content'         => htmlspecialchars($ann['content']),
        'target'          => $ann['target'],
        'is_active'       => (int)$ann['is_active'],
        'author_name'     => htmlspecialchars($ann['author_name'] ?? 'System Administrator'),
        'created_at'      => $ann['created_at'],
        'time_ago'        => timeAgo($ann['created_at'])
    ];
}

echo json_encode([
    'success'       => true,
    'role'          => $currentRole,
    'count'         => count($formattedAnnouncements),
    'announcements' => $formattedAnnouncements,
    'timestamp'     => time()
]);
exit;
