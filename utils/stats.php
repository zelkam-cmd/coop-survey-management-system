<?php
/**
 * CampusVoice — Statistics & Analytics Engine
 * Computes metrics (percentages, averages, rating distribution) and caches them in survey_results table.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Recalculate and update cached survey_results table for a survey
 */
function recalculateSurveyResults($surveyId) {
    $pdo = db();

    // Get all questions
    $stmt = $pdo->prepare("SELECT question_id, question_type FROM survey_questions WHERE survey_id = ?");
    $stmt->execute([$surveyId]);
    $questions = $stmt->fetchAll();

    foreach ($questions as $q) {
        $qId = $q['question_id'];
        $type = $q['question_type'];

        if ($type === 'rating') {
            // Calculate Average Rating
            $avgStmt = $pdo->prepare("SELECT AVG(rating_value) as avg_rating, COUNT(*) as total FROM responses WHERE question_id = ? AND rating_value IS NOT NULL");
            $avgStmt->execute([$qId]);
            $res = $avgStmt->fetch();
            $avgValue = $res['avg_rating'] !== null ? round((float)$res['avg_rating'], 2) : 0;
            $total = (int)$res['total'];

            // Distribution
            $distStmt = $pdo->prepare("SELECT rating_value, COUNT(*) as cnt FROM responses WHERE question_id = ? AND rating_value IS NOT NULL GROUP BY rating_value");
            $distStmt->execute([$qId]);
            $rows = $distStmt->fetchAll();
            $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            foreach ($rows as $r) {
                $dist[$r['rating_value']] = (int)$r['cnt'];
            }

            saveSurveyResultMetric($surveyId, $qId, 'average', $avgValue, json_encode(['distribution' => $dist, 'total_responses' => $total]));

        } elseif ($type === 'multiple_choice' || $type === 'yes_no') {
            // Count per choice
            $choiceStmt = $pdo->prepare("
                SELECT sc.choice_id, sc.choice_text, COUNT(r.response_id) as cnt
                FROM survey_choices sc
                LEFT JOIN responses r ON sc.choice_id = r.choice_id
                WHERE sc.question_id = ?
                GROUP BY sc.choice_id, sc.choice_text
                ORDER BY sc.order_index ASC
            ");
            $choiceStmt->execute([$qId]);
            $choices = $choiceStmt->fetchAll();

            $totalRespStmt = $pdo->prepare("SELECT COUNT(*) FROM responses WHERE question_id = ? AND choice_id IS NOT NULL");
            $totalRespStmt->execute([$qId]);
            $total = (int)$totalRespStmt->fetchColumn();

            $percentages = [];
            foreach ($choices as $c) {
                $cnt = (int)$c['cnt'];
                $pct = $total > 0 ? round(($cnt / $total) * 100, 1) : 0;
                $percentages[$c['choice_text']] = [
                    'choice_id' => $c['choice_id'],
                    'count' => $cnt,
                    'percentage' => $pct
                ];
            }

            saveSurveyResultMetric($surveyId, $qId, 'percentage', null, json_encode(['choices' => $percentages, 'total_responses' => $total]));
        }
    }
}

/**
 * Save or update metric in survey_results table
 */
function saveSurveyResultMetric($surveyId, $questionId, $metric, $val, $details) {
    $pdo = db();
    $stmt = $pdo->prepare("
        INSERT INTO survey_results (survey_id, question_id, computed_metric, computed_value, computed_details, last_refreshed_at)
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE computed_value = VALUES(computed_value), computed_details = VALUES(computed_details), last_refreshed_at = NOW()
    ");
    $stmt->execute([$surveyId, $questionId, $metric, $val, $details]);
}

/**
 * Fetch flagged concerns across active surveys (e.g. negative choices or low rating average)
 */
function getFlaggedConcerns() {
    $pdo = db();
    $concerns = [];

    // Query questions with low average rating (< 2.8)
    $stmt = $pdo->prepare("
        SELECT sr.*, sq.question_text, s.title as survey_title, s.survey_id
        FROM survey_results sr
        JOIN survey_questions sq ON sr.question_id = sq.question_id
        JOIN surveys s ON sr.survey_id = s.survey_id
        WHERE sr.computed_metric = 'average' AND sr.computed_value < 3.0 AND s.status = 'active'
    ");
    $stmt->execute();
    $lowRatings = $stmt->fetchAll();

    foreach ($lowRatings as $lr) {
        $details = json_decode($lr['computed_details'], true);
        $concerns[] = [
            'type' => 'low_rating',
            'survey_id' => $lr['survey_id'],
            'survey_title' => $lr['survey_title'],
            'question_text' => $lr['question_text'],
            'value' => $lr['computed_value'],
            'message' => "Low average rating of {$lr['computed_value']} / 5.0 in '{$lr['question_text']}'"
        ];
    }

    // Query choices crossing CONCERN_THRESHOLD
    $stmt = $pdo->prepare("
        SELECT sr.*, sq.question_text, s.title as survey_title, s.survey_id
        FROM survey_results sr
        JOIN survey_questions sq ON sr.question_id = sq.question_id
        JOIN surveys s ON sr.survey_id = s.survey_id
        WHERE sr.computed_metric = 'percentage' AND s.status = 'active'
    ");
    $stmt->execute();
    $choiceResults = $stmt->fetchAll();

    foreach ($choiceResults as $cr) {
        $details = json_decode($cr['computed_details'] ?? '', true);
        if (!empty($details['choices']) && is_array($details['choices'])) {
            foreach ($details['choices'] as $cText => $cData) {
                $pct = is_array($cData) ? (float)($cData['percentage'] ?? 0) : (float)$cData;
                if ($pct >= CONCERN_THRESHOLD && (
                    strripos($cText, 'yes') !== false || 
                    strripos($cText, 'slow') !== false || 
                    strripos($cText, 'poor') !== false || 
                    strripos($cText, 'frequent') !== false ||
                    strripos($cText, 'unsafe') !== false
                )) {
                    $concerns[] = [
                        'type' => 'negative_threshold',
                        'survey_id' => $cr['survey_id'],
                        'survey_title' => $cr['survey_title'],
                        'question_text' => $cr['question_text'],
                        'choice' => $cText,
                        'percentage' => $pct,
                        'message' => "{$pct}% of respondents selected '{$cText}' for '{$cr['question_text']}'"
                    ];
                }
            }
        }
    }

    return $concerns;
}

/**
 * Generate automatic recommendations based on concerns
 */
function getAutoRecommendations() {
    $concerns = getFlaggedConcerns();
    $recommendations = [];

    foreach ($concerns as $c) {
        if (strripos($c['survey_title'], 'wi-fi') !== false || strripos($c['question_text'], 'wi-fi') !== false) {
            $recommendations[] = [
                'title' => 'Upgrade Campus Wi-Fi & Bandwidth',
                'description' => 'High percentage of disconnections reported. Recommend adding access points in heavy traffic zones (Library & Academic Buildings).',
                'priority' => 'High'
            ];
        } elseif (strripos($c['survey_title'], 'cafeteria') !== false || strripos($c['question_text'], 'food') !== false) {
            $recommendations[] = [
                'title' => 'Review Cafeteria Food Variety & Pricing',
                'description' => 'Student feedback indicates demand for healthier options and affordable pricing structures.',
                'priority' => 'Medium'
            ];
        } elseif (strripos($c['survey_title'], 'safety') !== false || strripos($c['question_text'], 'safe') !== false) {
            $recommendations[] = [
                'title' => 'Enhance Campus Lighting & Night Security',
                'description' => 'Concerns flagged regarding night-time security. Increase patrols around Parking and Back Gate areas.',
                'priority' => 'High'
            ];
        } else {
            $recommendations[] = [
                'title' => 'Address Feedback in ' . $c['survey_title'],
                'description' => 'Review qualitative responses and evaluate facility/service improvements for ' . $c['question_text'],
                'priority' => 'Medium'
            ];
        }
    }

    // Default fallback recommendation if none flagged
    if (empty($recommendations)) {
        $recommendations[] = [
            'title' => 'Maintain Current Service Standards',
            'description' => 'All active surveys show satisfaction within target parameters. Continue monitoring student responses.',
            'priority' => 'Low'
        ];
    }

    return array_unique($recommendations, SORT_REGULAR);
}
