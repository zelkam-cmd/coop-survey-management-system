<?php
/**
 * CampusVoice — Administrator Dashboard
 * Summary statistics, recent surveys, charts, flagged concerns, recommendations, activity feed.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../utils/stats.php';

$pdo = db();

// Total Students
$stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'");
$totalStudents = (int)$stmt->fetchColumn();

// Active Surveys
$stmt = $pdo->query("SELECT COUNT(*) FROM surveys WHERE status = 'active'");
$activeSurveysCount = (int)$stmt->fetchColumn();

// Closed Surveys
$stmt = $pdo->query("SELECT COUNT(*) FROM surveys WHERE status IN ('closed', 'archived')");
$closedSurveysCount = (int)$stmt->fetchColumn();

// Total Unique Respondents
$stmt = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM responses");
$totalRespondents = (int)$stmt->fetchColumn();

// Overall Participation Rate
$overallParticipationRate = $totalStudents > 0 ? round(($totalRespondents / $totalStudents) * 100, 1) : 0;

// Recent Surveys
$stmt = $pdo->query("
    SELECT s.*, 
           (SELECT COUNT(DISTINCT student_id) FROM responses r WHERE r.survey_id = s.survey_id) as response_count,
           (SELECT COUNT(*) FROM survey_questions sq WHERE sq.survey_id = s.survey_id) as question_count
    FROM surveys s 
    ORDER BY s.created_at DESC 
    LIMIT 5
");
$recentSurveys = $stmt->fetchAll();

// Flagged Concerns & Auto Recommendations
$flaggedConcerns = getFlaggedConcerns();
$recommendations = getAutoRecommendations();

// Activity Feed
$stmt = $pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 6");
$activityLogs = $stmt->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Welcome Banner -->
<div class="dashboard-welcome" style="background: linear-gradient(135deg, #1E293B, #334155);">
    <div class="dashboard-welcome-content">
        <h2>Administrator Command Center</h2>
        <p>Monitor participation, track live student feedback, and address campus concerns.</p>
    </div>
</div>

<!-- Stat Cards Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-primary-lighter);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= $totalStudents ?></div>
            <div class="stat-card-label">Total Active Students</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-success-light);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-success)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= $activeSurveysCount ?></div>
            <div class="stat-card-label">Active Surveys</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-accent-lighter);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= $overallParticipationRate ?>%</div>
            <div class="stat-card-label">Participation Rate</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-warning-light);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-warning)" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= count($flaggedConcerns) ?></div>
            <div class="stat-card-label">Flagged Concerns</div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Main Column -->
    <div>
        <!-- Recent Surveys Table -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-header">
                <h3 class="card-title">Recent Surveys</h3>
                <a href="<?= BASE_URL ?>/admin/surveys/create" class="btn btn-primary btn-sm">+ Create Survey</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="data-table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title & Category</th>
                                <th>Status</th>
                                <th>Responses</th>
                                <th>Closing Date</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSurveys as $s): ?>
                            <tr>
                                <td>
                                    <div class="font-semibold"><?= e($s['title']) ?></div>
                                    <div style="font-size: var(--font-size-xs); color: var(--color-text-secondary);"><?= e($s['category']) ?> • <?= $s['question_count'] ?> questions</div>
                                </td>
                                <td><?= getStatusBadge($s['status']) ?></td>
                                <td>
                                    <span class="font-semibold"><?= $s['response_count'] ?></span>
                                    <span style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);">
                                        (<?= calcPercentage($s['response_count'], $totalStudents) ?>%)
                                    </span>
                                </td>
                                <td><?= formatDate($s['close_date']) ?></td>
                                <td style="text-align: right;">
                                    <a href="<?= BASE_URL ?>/admin/results/<?= $s['survey_id'] ?>" class="btn btn-secondary btn-sm">Results</a>
                                    <a href="<?= BASE_URL ?>/admin/surveys/<?= $s['survey_id'] ?>/edit" class="btn btn-ghost btn-sm">Edit</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Participation Gauge & Trend Charts -->
        <div class="chart-grid">
            <div class="chart-container">
                <div class="chart-header">
                    <div class="chart-title">Participation Rate</div>
                </div>
                <div class="chart-body" style="height: 220px;">
                    <canvas id="dashboard-gauge-chart"></canvas>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-header">
                    <div class="chart-title">Responses by Category</div>
                </div>
                <div class="chart-body" style="height: 220px;">
                    <canvas id="dashboard-category-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div>
        <!-- Flagged Concerns -->
        <div class="card" style="margin-bottom: var(--space-5);">
            <div class="card-header">
                <h3 class="card-title">🚨 Flagged Concerns</h3>
            </div>
            <div class="card-body">
                <?php if (empty($flaggedConcerns)): ?>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-secondary); text-align: center; padding: var(--space-4) 0;">No critical concerns flagged.</p>
                <?php else: ?>
                    <?php foreach ($flaggedConcerns as $c): ?>
                    <div class="concern-item">
                        <svg class="concern-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div>
                            <div class="concern-text"><?= e($c['message']) ?></div>
                            <div class="concern-detail">Survey: <?= e($c['survey_title']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Auto Recommendations -->
        <div class="card" style="margin-bottom: var(--space-5);">
            <div class="card-header">
                <h3 class="card-title">💡 Auto Recommendations</h3>
            </div>
            <div class="card-body">
                <?php foreach ($recommendations as $rec): ?>
                <div class="recommendation-item">
                    <svg class="recommendation-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <div>
                        <div class="recommendation-text"><?= e($rec['title']) ?></div>
                        <div style="font-size: var(--font-size-xs); color: var(--color-text-secondary); margin-top: 2px;"><?= e($rec['description']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Activity Feed</h3>
            </div>
            <div class="card-body" style="padding: var(--space-2);">
                <div class="activity-feed">
                    <?php foreach ($activityLogs as $log): ?>
                    <div class="activity-item">
                        <div class="activity-icon" style="background: var(--color-gray-100);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text"><?= e($log['description'] ?: $log['action']) ?></div>
                            <div class="activity-time"><?= timeAgo($log['created_at']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gauge chart
        createGaugeChart('dashboard-gauge-chart', <?= $totalRespondents ?>, <?= $totalStudents ?>);

        // Category chart
        createBarChart('dashboard-category-chart', 
            ['Wi-Fi', 'Cafeteria', 'Library', 'Safety', 'Wellness'], 
            [12, 18, 9, 15, 6]
        );
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
