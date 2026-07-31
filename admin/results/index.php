<?php
/**
 * CampusVoice — Results Dashboard Hub / Selector
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';

$pdo = db();

$stmt = $pdo->query("
    SELECT s.*, 
           (SELECT COUNT(DISTINCT student_id) FROM responses r WHERE r.survey_id = s.survey_id) as response_count,
           (SELECT COUNT(*) FROM survey_questions sq WHERE sq.survey_id = s.survey_id) as question_count
    FROM surveys s 
    ORDER BY s.created_at DESC
");
$surveys = $stmt->fetchAll();

$pageTitle = 'Results Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Results Dashboard</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">Results & Analytics Dashboard</h2>
        <p class="content-subtitle">Select a survey to view computed statistics, distribution charts, and highlighted concerns</p>
    </div>
</div>

<div class="survey-grid">
    <?php foreach ($surveys as $s): ?>
    <div class="survey-card card-clickable" onclick="window.location.href='<?= BASE_URL ?>/admin/results/<?= $s['survey_id'] ?>'">
        <?= getCategoryBadge($s['category']) ?>
        <h3 class="survey-card-title"><?= e($s['title']) ?></h3>
        <p class="survey-card-description"><?= e($s['description']) ?></p>
        <div class="survey-card-meta">
            <span class="survey-card-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                <?= $s['question_count'] ?> questions
            </span>
            <span class="survey-card-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <strong class="text-primary"><?= $s['response_count'] ?></strong> responses
            </span>
        </div>
        <div class="survey-card-footer">
            <?= getStatusBadge($s['status']) ?>
            <span class="btn btn-primary btn-sm">View Results →</span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
