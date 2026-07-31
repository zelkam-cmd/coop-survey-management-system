<?php
/**
 * CampusVoice — Student Detail / Response History
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';

$studentId = (int)($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    setToast('Error', 'Student not found.', 'error');
    redirect(BASE_URL . '/admin/students');
}

// Fetch response history per survey
$stmt = $pdo->prepare("
    SELECT s.survey_id, s.title as survey_title, s.category, r.submitted_at, COUNT(r.response_id) as answered_questions
    FROM responses r
    JOIN surveys s ON r.survey_id = s.survey_id
    WHERE r.student_id = ?
    GROUP BY s.survey_id, s.title, s.category, r.submitted_at
    ORDER BY r.submitted_at DESC
");
$stmt->execute([$studentId]);
$history = $stmt->fetchAll();

$pageTitle = 'Student Detail: ' . $student['full_name'];
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/students">Students</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active"><?= e($student['full_name']) ?></div>
</div>

<div class="dashboard-grid">
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Survey Submission History</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($history)): ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No submissions recorded</div>
                        <p class="empty-state-description">This student has not submitted any survey responses yet.</p>
                    </div>
                <?php else: ?>
                    <div class="data-table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Survey Title</th>
                                    <th>Category</th>
                                    <th>Date Submitted</th>
                                    <th>Questions Answered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $item): ?>
                                <tr>
                                    <td class="font-semibold"><?= e($item['survey_title']) ?></td>
                                    <td><?= getCategoryBadge($item['category']) ?></td>
                                    <td><?= formatDateTime($item['submitted_at']) ?></td>
                                    <td><span class="badge badge-success"><?= $item['answered_questions'] ?> answers</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar"><?= getInitials($student['full_name']) ?></div>
                <div class="profile-info">
                    <h2><?= e($student['full_name']) ?></h2>
                    <p>ID: <?= e($student['student_number']) ?></p>
                </div>
            </div>
            <div class="profile-body">
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Email:</div>
                    <div class="profile-detail-value"><?= e($student['email'] ?: '—') ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Department:</div>
                    <div class="profile-detail-value"><?= e($student['department'] ?: '—') ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Year Level:</div>
                    <div class="profile-detail-value"><?= e($student['year_level'] ?: '—') ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Status:</div>
                    <div class="profile-detail-value"><?= getUserStatusBadge($student['status']) ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Account Created:</div>
                    <div class="profile-detail-value"><?= formatDate($student['created_at']) ?></div>
                </div>

                <div style="margin-top: var(--space-6);">
                    <a href="<?= BASE_URL ?>/admin/students/reset_password?id=<?= $student['student_id'] ?>" class="btn btn-warning w-full" onclick="return confirm('Reset password to Student ID?');">
                        Reset Password to Student ID
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
