<?php
/**
 * CampusVoice — Manual Results Recomputation Trigger
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';
require_once __DIR__ . '/../../utils/stats.php';

$surveyId = (int)($_GET['id'] ?? 0);
if ($surveyId > 0) {
    recalculateSurveyResults($surveyId);
    setToast('Success', 'Survey results refreshed successfully!', 'success');
    redirect(BASE_URL . '/admin/results/' . $surveyId);
} else {
    redirect(BASE_URL . '/admin/results');
}
