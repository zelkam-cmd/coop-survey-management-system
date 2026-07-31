<?php
/**
 * CampusVoice — Export Survey Results to CSV / Excel
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';

$surveyId = (int)($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare("SELECT title FROM surveys WHERE survey_id = ?");
$stmt->execute([$surveyId]);
$survey = $stmt->fetch();

if (!$survey) {
    die("Survey not found.");
}

// Fetch all responses joined with student and question details
$stmt = $pdo->prepare("
    SELECT r.response_id, s.student_number, s.full_name, sq.question_text, sq.question_type,
           sc.choice_text, r.rating_value, r.text_answer, r.submitted_at
    FROM responses r
    JOIN students s ON r.student_id = s.student_id
    JOIN survey_questions sq ON r.question_id = sq.question_id
    LEFT JOIN survey_choices sc ON r.choice_id = sc.choice_id
    WHERE r.survey_id = ?
    ORDER BY r.submitted_at DESC, r.question_id ASC
");
$stmt->execute([$surveyId]);
$rows = $stmt->fetchAll();

$filename = "CampusVoice_Survey_" . $surveyId . "_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, ['Response ID', 'Student ID', 'Student Name', 'Question', 'Question Type', 'Answer / Rating / Text', 'Submitted At']);

foreach ($rows as $row) {
    $answer = '';
    if ($row['question_type'] === 'multiple_choice' || $row['question_type'] === 'yes_no') {
        $answer = $row['choice_text'];
    } elseif ($row['question_type'] === 'rating') {
        $answer = $row['rating_value'] . ' Stars';
    } elseif ($row['question_type'] === 'short_answer') {
        $answer = $row['text_answer'];
    }

    fputcsv($output, [
        $row['response_id'],
        $row['student_number'],
        $row['full_name'],
        $row['question_text'],
        $row['question_type'],
        $answer,
        $row['submitted_at']
    ]);
}

fclose($output);
exit;
