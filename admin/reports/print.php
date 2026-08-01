<?php
/**
 * CampusVoice — Printable Survey Report View
 * Printable layout styled with print.css and window.print() support.
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';
require_once __DIR__ . '/../../utils/stats.php';

$surveyId = (int) ($_GET['id'] ?? 0);
$pdo = db();

recalculateSurveyResults($surveyId);

$stmt = $pdo->prepare("SELECT s.*, a.full_name as author FROM surveys s LEFT JOIN administrators a ON s.created_by = a.admin_id WHERE s.survey_id = ?");
$stmt->execute([$surveyId]);
$survey = $stmt->fetch();

if (!$survey) {
    die("Survey not found.");
}

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT student_id) FROM responses WHERE survey_id = ?");
$stmt->execute([$surveyId]);
$totalRespondents = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY order_index ASC");
$stmt->execute([$surveyId]);
$questions = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM survey_results WHERE survey_id = ?");
$stmt->execute([$surveyId]);
$resultsRows = $stmt->fetchAll();
$resultsByQ = [];
foreach ($resultsRows as $r) {
    $resultsByQ[$r['question_id']] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Report — <?= e($survey['title']) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pages.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/print.css">
    <style>
        body {
            background: white;
            padding: 20px;
        }

        .report-header {
            text-align: center;
            border-bottom: 2px solid var(--color-primary);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div style="margin-bottom: 20px;" class="no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg">🖨️ Print / Save as PDF</button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg">Close</button>
    </div>

    <div class="report-container">
        <div class="report-header">
            <h1 class="report-title"><?= APP_SCHOOL ?></h1>
            <h2 style="font-size: var(--font-size-xl); margin-top: 5px; color: var(--color-primary);">
                <?= e($survey['title']) ?>
            </h2>
            <div class="report-meta">
                <span>Category: <strong><?= e($survey['category']) ?></strong></span>
                <span>Total Respondents: <strong><?= $totalRespondents ?></strong></span>
                <span>Date Generated: <strong><?= date('F d, Y') ?></strong></span>
            </div>
        </div>

        <div class="report-section">
            <h3 class="report-section-title">1. Executive Summary & Overview</h3>
            <p><?= e($survey['description']) ?></p>
        </div>

        <div class="report-section">
            <h3 class="report-section-title">2. Detailed Question Breakdown</h3>
            <?php foreach ($questions as $idx => $q): ?>
                <div style="margin-bottom: 20px; page-break-inside: avoid;">
                    <h4 style="font-size: var(--font-size-md); margin-bottom: 8px;">Q<?= $idx + 1 ?>:
                        <?= e($q['question_text']) ?> (<?= getQuestionTypeLabel($q['question_type']) ?>)
                    </h4>

                    <?php
                    $resData = $resultsByQ[$q['question_id']] ?? null;
                    $details = $resData ? json_decode($resData['computed_details'], true) : [];
                    ?>

                    <?php if ($q['question_type'] === 'rating'): ?>
                        <p>Average Rating: <strong><?= number_format((float) ($resData['computed_value'] ?? 0), 2) ?> /
                                5.00</strong></p>
                    <?php elseif ($q['question_type'] === 'multiple_choice' || $q['question_type'] === 'yes_no'): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Option</th>
                                    <th>Responses</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($details['choices'])): ?>
                                    <?php foreach ($details['choices'] as $cName => $cInfo): ?>
                                        <tr>
                                            <td><?= e($cName) ?></td>
                                            <td><?= $cInfo['count'] ?></td>
                                            <td><?= $cInfo['percentage'] ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="report-section">
            <h3 class="report-section-title">3. Recommendations & Next Steps</h3>
            <p>Based on aggregated feedback, campus administration recommends prioritizing identified concerns for
                facility maintenance and service improvements.</p>
        </div>
    </div>
</body>

</html>