<?php
/**
 * CampusVoice — Survey Results Analytics View
 * Visualizes pie, bar, rating histograms, summary tables, and highlighted concerns for a specific survey.
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';
require_once __DIR__ . '/../../utils/stats.php';

$surveyId = (int)($_GET['id'] ?? 0);
$pdo = db();

// Ensure results are up-to-date
recalculateSurveyResults($surveyId);

$stmt = $pdo->prepare("SELECT * FROM surveys WHERE survey_id = ?");
$stmt->execute([$surveyId]);
$survey = $stmt->fetch();

if (!$survey) {
    setToast('Error', 'Survey not found.', 'error');
    redirect(BASE_URL . '/admin/results');
}

// Get total respondents count
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT student_id) FROM responses WHERE survey_id = ?");
$stmt->execute([$surveyId]);
$totalRespondents = (int)$stmt->fetchColumn();

// Get questions
$stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY order_index ASC");
$stmt->execute([$surveyId]);
$questions = $stmt->fetchAll();

// Get precomputed results
$stmt = $pdo->prepare("SELECT * FROM survey_results WHERE survey_id = ?");
$stmt->execute([$surveyId]);
$resultsRows = $stmt->fetchAll();
$resultsByQ = [];
foreach ($resultsRows as $r) {
    $resultsByQ[$r['question_id']] = $r;
}

// Get verbatim short answer responses
$shortAnswers = [];
foreach ($questions as $q) {
    if ($q['question_type'] === 'short_answer') {
        $saStmt = $pdo->prepare("SELECT text_answer, submitted_at FROM responses WHERE question_id = ? AND text_answer IS NOT NULL AND text_answer != '' ORDER BY submitted_at DESC");
        $saStmt->execute([$q['question_id']]);
        $shortAnswers[$q['question_id']] = $saStmt->fetchAll();
    }
}

$pageTitle = 'Results: ' . $survey['title'];
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/results">Results</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active"><?= e($survey['title']) ?></div>
</div>

<div class="content-header">
    <div>
        <div style="margin-bottom: var(--space-2);"><?= getCategoryBadge($survey['category']) ?></div>
        <h2 class="content-title"><?= e($survey['title']) ?></h2>
        <p class="content-subtitle">Total Respondents: <strong><?= $totalRespondents ?></strong> students • Status: <?= getStatusBadge($survey['status']) ?></p>
    </div>
    <div class="content-actions">
        <a href="<?= BASE_URL ?>/admin/results/compute?id=<?= $surveyId ?>" class="btn btn-secondary btn-sm">🔄 Refresh Results</a>
        <a href="<?= BASE_URL ?>/admin/reports/<?= $surveyId ?>/print" class="btn btn-primary btn-sm" target="_blank">Print Report 🖨️</a>
    </div>
</div>

<?php if ($totalRespondents === 0): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <div class="empty-state-title">No Responses Yet</div>
            <p class="empty-state-description">No students have completed this survey yet. Results will appear automatically when submissions arrive.</p>
        </div>
    </div>
<?php else: ?>
    <!-- Per-Question Analytics Breakdown -->
    <?php foreach ($questions as $idx => $q): ?>
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-header">
                <div>
                    <span class="font-semibold text-primary">Question <?= $idx + 1 ?></span>
                    <?= getQuestionTypeBadge($q['question_type']) ?>
                    <h3 class="card-title" style="margin-top: var(--space-1);"><?= e($q['question_text']) ?></h3>
                </div>
            </div>
            <div class="card-body">
                <?php 
                $resData = $resultsByQ[$q['question_id']] ?? null;
                $details = $resData ? json_decode($resData['computed_details'], true) : [];
                ?>

                <?php if ($q['question_type'] === 'rating'): ?>
                    <div class="chart-grid">
                        <div>
                            <div style="text-align: center; padding: var(--space-6); background: var(--color-gray-50); border-radius: var(--radius-lg); margin-bottom: var(--space-4);">
                                <div style="font-size: 48px; font-weight: bold; color: var(--color-primary);"><?= number_format((float)($resData['computed_value'] ?? 0), 2) ?></div>
                                <div style="font-size: var(--font-size-sm); color: var(--color-text-secondary);">Average Rating out of 5.0</div>
                            </div>
                        </div>
                        <div>
                            <div style="height: 200px;">
                                <canvas id="chart-q-<?= $q['question_id'] ?>"></canvas>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            createRatingHistogram('chart-q-<?= $q['question_id'] ?>', <?= json_encode($details['distribution'] ?? []) ?>);
                        });
                    </script>

                <?php elseif ($q['question_type'] === 'multiple_choice' || $q['question_type'] === 'yes_no'): ?>
                    <div class="chart-grid">
                        <div>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Choice</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($details['choices'])): ?>
                                        <?php foreach ($details['choices'] as $cName => $cInfo): ?>
                                        <tr>
                                            <td class="font-medium"><?= e($cName) ?></td>
                                            <td><?= $cInfo['count'] ?></td>
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    <div class="progress-bar" style="width: 100px;">
                                                        <div class="progress-bar-fill" style="width: <?= $cInfo['percentage'] ?>%;"></div>
                                                    </div>
                                                    <span class="font-semibold"><?= $cInfo['percentage'] ?>%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <div style="height: 220px;">
                                <canvas id="chart-q-<?= $q['question_id'] ?>"></canvas>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const labels = <?= json_encode(array_keys($details['choices'] ?? [])) ?>;
                            const counts = <?= json_encode(array_column($details['choices'] ?? [], 'count')) ?>;
                            createPieChart('chart-q-<?= $q['question_id'] ?>', labels, counts, { doughnut: true });
                        });
                    </script>

                <?php elseif ($q['question_type'] === 'short_answer'): ?>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php if (empty($shortAnswers[$q['question_id']])): ?>
                            <p style="color: var(--color-text-tertiary);">No text responses submitted.</p>
                        <?php else: ?>
                            <?php foreach ($shortAnswers[$q['question_id']] as $sa): ?>
                            <div style="padding: var(--space-3); border-bottom: 1px solid var(--color-divider);">
                                <div style="font-size: var(--font-size-base); color: var(--color-text); mb-1">"<?= e($sa['text_answer']) ?>"</div>
                                <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);"><?= timeAgo($sa['submitted_at']) ?></div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
