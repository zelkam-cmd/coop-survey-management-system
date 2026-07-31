<?php
/**
 * CampusVoice — Survey Closed Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(ROLE_STUDENT);
require_once __DIR__ . '/../utils/helpers.php';

$surveyId = (int)($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare("SELECT title FROM surveys WHERE survey_id = ?");
$stmt->execute([$surveyId]);
$survey = $stmt->fetch();

$pageTitle = 'Survey Closed';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="confirmation-container">
    <div class="confirmation-icon" style="background: var(--color-error-light);">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--color-error)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
    </div>
    
    <h2 class="confirmation-title">Survey Closed</h2>
    
    <p class="confirmation-text">
        The survey <strong><?= e($survey['title'] ?? 'this survey') ?></strong> is no longer accepting responses because it has closed or been deactivated.
    </p>

    <div style="display: flex; gap: var(--space-4); justify-content: center;">
        <a href="<?= BASE_URL ?>/student/surveys" class="btn btn-primary">
            View Available Surveys
        </a>
        <a href="<?= BASE_URL ?>/student/dashboard" class="btn btn-secondary">
            Back to Dashboard
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
