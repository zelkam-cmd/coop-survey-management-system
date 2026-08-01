<?php
/**
 * CampusVoice — Survey Engine Logic
 * Handles survey status automation, date checks, duplicate prevention, and response validation.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Check if a survey is currently available for a specific student to answer
 */
function isSurveyAvailableForStudent($surveyId, $studentId) {
    $pdo = db();
    
    // 1. Check survey exists, is active, and within date bounds
    $stmt = $pdo->prepare("
        SELECT * 
        FROM surveys 
        WHERE survey_id = ?
    ");
    $stmt->execute([$surveyId]);
    $survey = $stmt->fetch();

    if (!$survey) {
        return ['available' => false, 'reason' => 'not_found'];
    }

    if ($survey['status'] !== 'active') {
        return ['available' => false, 'reason' => 'inactive', 'survey' => $survey];
    }

    $now = new DateTime();
    $openDate = new DateTime($survey['open_date']);
    $closeDate = new DateTime($survey['close_date']);

    if ($now < $openDate) {
        return ['available' => false, 'reason' => 'future', 'survey' => $survey];
    }

    if ($now > $closeDate) {
        return ['available' => false, 'reason' => 'closed', 'survey' => $survey];
    }

    // 2. Check if student has already submitted a response
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM responses WHERE survey_id = ? AND student_id = ?");
    $stmt->execute([$surveyId, $studentId]);
    if ($stmt->fetchColumn() > 0) {
        return ['available' => false, 'reason' => 'already_answered', 'survey' => $survey];
    }

    return ['available' => true, 'survey' => $survey];
}

/**
 * Submit survey answers with database transaction and duplicate protection
 */
function submitSurveyResponse($surveyId, $studentId, $answers) {
    $pdo = db();

    // Re-check availability server-side
    $check = isSurveyAvailableForStudent($surveyId, $studentId);
    if (!$check['available']) {
        return ['success' => false, 'error' => $check['reason']];
    }

    // Begin Transaction
    $pdo->beginTransaction();

    try {
        // Pre-check inside transaction
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM responses WHERE survey_id = ? AND student_id = ? FOR UPDATE");
        $checkStmt->execute([$surveyId, $studentId]);
        if ($checkStmt->fetchColumn() > 0) {
            $pdo->rollBack();
            return ['success' => false, 'error' => 'already_answered'];
        }

        // Fetch questions for this survey
        $qStmt = $pdo->prepare("SELECT question_id, question_type, is_required FROM survey_questions WHERE survey_id = ?");
        $qStmt->execute([$surveyId]);
        $questions = $qStmt->fetchAll();

        $insertStmt = $pdo->prepare("
            INSERT INTO responses (student_id, survey_id, question_id, choice_id, rating_value, text_answer, submitted_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        foreach ($questions as $q) {
            $qId = $q['question_id'];
            $qType = $q['question_type'];
            $ans = $answers[$qId] ?? null;

            if ($q['is_required'] && (is_null($ans) || $ans === '')) {
                $pdo->rollBack();
                return ['success' => false, 'error' => 'missing_required', 'question_id' => $qId];
            }

            $choiceId = null;
            $ratingVal = null;
            $textAns = null;

            if ($qType === 'multiple_choice' || $qType === 'yes_no') {
                $choiceId = !empty($ans) ? (int)$ans : null;
            } elseif ($qType === 'rating') {
                if (!empty($ans)) {
                    $ratingVal = (int)$ans;
                    if ($ratingVal < 1 || $ratingVal > 5) {
                        $pdo->rollBack();
                        return ['success' => false, 'error' => 'invalid_rating'];
                    }
                }
            } elseif ($qType === 'short_answer') {
                $textAns = !empty($ans) ? trim($ans) : null;
            }

            $insertStmt->execute([$studentId, $surveyId, $qId, $choiceId, $ratingVal, $textAns]);
        }

        $pdo->commit();

        // Trigger statistics recalculation
        require_once __DIR__ . '/stats.php';
        recalculateSurveyResults($surveyId);

        return ['success' => true];

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Survey submission transaction failed: " . $e->getMessage());
        return ['success' => false, 'error' => 'db_error', 'details' => $e->getMessage()];
    }
}
