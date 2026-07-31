<?php
/**
 * CampusVoice — Submission Confirmation Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(ROLE_STUDENT);
require_once __DIR__ . '/../utils/helpers.php';

$surveyId = (int)($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare("SELECT title, category FROM surveys WHERE survey_id = ?");
$stmt->execute([$surveyId]);
$survey = $stmt->fetch();

$pageTitle = 'Submission Confirmed';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="confirmation-container">
    <div class="confirmation-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
    </div>
    
    <h2 class="confirmation-title">Response Submitted!</h2>
    
    <p class="confirmation-text">
        Thank you for submitting your feedback for <strong><?= e($survey['title'] ?? 'the survey') ?></strong>. Your response has been recorded and will help shape a better campus.
    </p>

    <div style="display: flex; gap: var(--space-4); justify-content: center;">
        <a href="<?= BASE_URL ?>/student/dashboard" class="btn btn-primary btn-lg">
            Back to Dashboard
        </a>
        <a href="<?= BASE_URL ?>/student/surveys" class="btn btn-secondary btn-lg">
            View Other Surveys
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
