<?php
/**
 * CampusVoice — Available Surveys (Student)
 * Lists open, not-yet-answered surveys with category and deadline countdown.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(ROLE_STUDENT);
checkPasswordChange();
require_once __DIR__ . '/../utils/helpers.php';

$pdo = db();
$studentId = getCurrentUserId();

// Get available surveys: active, within date range, NOT answered by this student
$stmt = $pdo->prepare("
    SELECT s.*, 
        (SELECT COUNT(*) FROM survey_questions sq WHERE sq.survey_id = s.survey_id) as question_count,
        a.full_name as created_by_name
    FROM surveys s
    LEFT JOIN administrators a ON s.created_by = a.admin_id
    WHERE s.status = 'active'
    AND NOW() >= s.open_date
    AND NOW() <= s.close_date
    AND s.survey_id NOT IN (
        SELECT DISTINCT r.survey_id FROM responses r WHERE r.student_id = ?
    )
    ORDER BY s.close_date ASC
");
$stmt->execute([$studentId]);
$surveys = $stmt->fetchAll();
$pendingSurveyCount = count($surveys);

$pageTitle = 'Available Surveys';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/student/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Available Surveys</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">Available Surveys</h2>
        <p class="content-subtitle"><?= $pendingSurveyCount ?> survey<?= $pendingSurveyCount !== 1 ? 's' : '' ?> waiting for your response</p>
    </div>
</div>

<?php if (empty($surveys)): ?>
    <!-- No Available Surveys Empty State -->
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="empty-state-title">No Available Surveys</div>
            <p class="empty-state-description">You've completed all available surveys! Check back later for new ones.</p>
            <a href="<?= BASE_URL ?>/student/dashboard" class="btn btn-primary">← Back to Dashboard</a>
        </div>
    </div>
<?php else: ?>
    <!-- Survey Cards Grid -->
    <div class="survey-grid">
        <?php foreach ($surveys as $survey): ?>
        <div class="survey-card">
            <?= getCategoryBadge($survey['category']) ?>
            <h3 class="survey-card-title"><?= e($survey['title']) ?></h3>
            <p class="survey-card-description"><?= e($survey['description']) ?></p>
            <div class="survey-card-meta">
                <span class="survey-card-meta-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                    <?= $survey['question_count'] ?> question<?= $survey['question_count'] !== 1 ? 's' : '' ?>
                </span>
                <span class="survey-card-meta-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?= getCountdown($survey['close_date']) ?>
                </span>
            </div>
            <div class="survey-card-footer">
                <span class="text-secondary" style="font-size: var(--font-size-xs);">
                    Closes <?= formatDate($survey['close_date']) ?>
                </span>
                <a href="<?= BASE_URL ?>/student/surveys/<?= $survey['survey_id'] ?>" class="btn btn-primary btn-sm">
                    Answer Survey →
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
