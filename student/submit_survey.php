<?php
/**
 * CampusVoice — Survey Submission Handler
 * Processes POST requests for survey submissions securely.
 */

require_once __DIR__ . '/../includes/auth_check.php';
requireRole(ROLE_STUDENT);
checkPasswordChange();
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../utils/survey_engine.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/student/surveys');
}

requireCSRF();

$surveyId = (int)($_POST['survey_id'] ?? 0);
$studentId = getCurrentUserId();
$answers = $_POST['answer'] ?? [];

$result = submitSurveyResponse($surveyId, $studentId, $answers);

if ($result['success']) {
    // Log activity
    logActivity($studentId, ROLE_STUDENT, 'submit_survey', 'Submitted response for survey #' . $surveyId, 'survey', $surveyId);
    
    // Redirect to confirmation
    redirect(BASE_URL . '/student/surveys/' . $surveyId . '/confirmation');
} else {
    if ($result['error'] === 'already_answered') {
        setToast('Notice', 'You have already answered this survey.', 'warning');
        redirect(BASE_URL . '/student/dashboard');
    } elseif ($result['error'] === 'closed' || $result['error'] === 'inactive') {
        redirect(BASE_URL . '/student/surveys/' . $surveyId . '/closed');
    } else {
        setToast('Error', 'Failed to submit survey. Please answer all required questions.', 'error');
        redirect(BASE_URL . '/student/surveys/' . $surveyId);
    }
}
