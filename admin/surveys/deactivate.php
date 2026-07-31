<?php
/**
 * CampusVoice — Deactivate Survey Handler
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';

$surveyId = (int)($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare("UPDATE surveys SET status = 'closed' WHERE survey_id = ?");
$stmt->execute([$surveyId]);

logActivity(getCurrentUserId(), ROLE_ADMIN, 'deactivate_survey', 'Deactivated survey #' . $surveyId, 'survey', $surveyId);
setToast('Success', 'Survey deactivated successfully!', 'success');

redirect(BASE_URL . '/admin/surveys');
