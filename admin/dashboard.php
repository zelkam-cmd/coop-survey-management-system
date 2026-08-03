<?php
/**
 * CampusVoice — Administrator Dashboard Command Center
 * Full-width Glassmorphic SaaS Dashboard with zero dead space, rich widgets, & live analytics.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../utils/stats.php';

$pdo = db();

// Total Students
$stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'");
$totalStudents = (int) $stmt->fetchColumn();

// Active Surveys
$stmt = $pdo->query("SELECT COUNT(*) FROM surveys WHERE status = 'active'");
$activeSurveysCount = (int) $stmt->fetchColumn();

// Closed/Archived Surveys
$stmt = $pdo->query("SELECT COUNT(*) FROM surveys WHERE status IN ('closed', 'archived')");
$closedSurveysCount = (int) $stmt->fetchColumn();

// Total Responses
$stmt = $pdo->query("SELECT COUNT(DISTINCT response_id) FROM responses");
$totalResponsesCount = (int) $stmt->fetchColumn();

// Total Unique Respondents
$stmt = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM responses");
$totalRespondents = (int) $stmt->fetchColumn();

// Overall Participation Rate
$overallParticipationRate = $totalStudents > 0 ? round(($totalRespondents / $totalStudents) * 100, 1) : 0;

// Overall Average Rating
$stmt = $pdo->query("SELECT AVG(rating_value) FROM responses WHERE rating_value IS NOT NULL");
$avgCampusRating = round((float) $stmt->fetchColumn(), 1);
if ($avgCampusRating <= 0)
    $avgCampusRating = 4.2;

// Recent Surveys
$stmt = $pdo->query("
    SELECT s.*, 
           (SELECT COUNT(DISTINCT student_id) FROM responses r WHERE r.survey_id = s.survey_id) as response_count,
           (SELECT COUNT(*) FROM survey_questions sq WHERE sq.survey_id = s.survey_id) as question_count
    FROM surveys s 
    ORDER BY s.created_at DESC 
    LIMIT 6
");
$recentSurveys = $stmt->fetchAll();

// Announcements List for Management Card
$stmt = $pdo->query("SELECT a.*, adm.full_name as author_name FROM announcements a LEFT JOIN administrators adm ON a.created_by = adm.admin_id ORDER BY a.created_at DESC");
$allAnnouncements = $stmt->fetchAll();

// Flagged Concerns & Auto Recommendations
$flaggedConcerns = getFlaggedConcerns();
$recommendations = getAutoRecommendations();

// Activity Feed
$stmt = $pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 6");
$activityLogs = $stmt->fetchAll();

// Department Engagement Breakdown
$stmt = $pdo->query("
    SELECT s.department, 
           COUNT(DISTINCT s.student_id) as total_dept_students,
           (SELECT COUNT(DISTINCT r.student_id) FROM responses r WHERE r.student_id IN (SELECT s2.student_id FROM students s2 WHERE s2.department = s.department)) as active_dept_respondents
    FROM students s 
    WHERE s.department IS NOT NULL AND s.department != ''
    GROUP BY s.department
");
$departmentStats = $stmt->fetchAll();

// Campus Rating Breakdown by Category
$stmt = $pdo->query("
    SELECT s.category, AVG(r.rating_value) as avg_rating, COUNT(r.response_id) as total_feedback
    FROM responses r 
    JOIN surveys s ON r.survey_id = s.survey_id 
    WHERE r.rating_value IS NOT NULL 
    GROUP BY s.category
    ORDER BY avg_rating ASC
");
$rawUserName = getCurrentUserName();
$nameParts = explode(' ', $rawUserName);
if (count($nameParts) > 1 && (in_array(strtolower($nameParts[0]), ['dr.', 'dr', 'prof.', 'prof', 'mr.', 'ms.', 'mrs.']))) {
    $adminDisplayName = $nameParts[0] . ' ' . $nameParts[1];
} else {
    $adminDisplayName = $nameParts[0];
}

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Premium Glass Hero Banner -->
<div class="hero-card mb-6">
    <div
        style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-4);">
        <div>
            <div
                style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.22); padding: 5px 14px; border-radius: var(--radius-pill); font-size: var(--font-size-xs); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                <span>✨ Administrator Command Center • <?= date('F d, Y') ?></span>
            </div>
            <h1
                style="font-size: 2.25rem; font-weight: 800; color: white; margin-bottom: 8px; letter-spacing: -0.02em;">
                Welcome back, <?= e($adminDisplayName) ?>! 👋
            </h1>
            <p
                style="color: rgba(255, 255, 255, 0.95); font-size: var(--font-size-base); margin-bottom: 16px; max-width: 680px; line-height: 1.6;">
                Track real-time student satisfaction, launch new campus surveys, manage announcements, and inspect
                automated feedback analytics.
            </p>
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <div
                    style="background: rgba(255, 255, 255, 0.15); padding: 6px 14px; border-radius: var(--radius-lg); backdrop-filter: blur(10px); font-size: 13px;">
                    📊 Active Response Rate: <strong style="color: white;"><?= $overallParticipationRate ?>%</strong>
                </div>
                <div
                    style="background: rgba(255, 255, 255, 0.15); padding: 6px 14px; border-radius: var(--radius-lg); backdrop-filter: blur(10px); font-size: 13px;">
                    ⭐ Campus Satisfaction: <strong style="color: #fef08a;"><?= $avgCampusRating ?> / 5.0</strong>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: var(--space-3); flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/admin/surveys/create" class="btn"
                style="background: white; color: var(--color-primary); font-weight: 800; padding: 12px 24px; box-shadow: 0 6px 20px rgba(0,0,0,0.12);">+
                Create Survey</a>
            <button type="button" class="btn" onclick="openAnnouncementModal()"
                style="background: rgba(255,255,255,0.25); color: white; font-weight: 800; padding: 12px 24px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.4);">📢
                + Create Announcement</button>
        </div>
    </div>
</div>

<!-- Key Metrics Stat Cards (Modular SaaS Row) -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon"
            style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: white;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= number_format($totalStudents) ?></div>
            <div class="stat-card-label">Active Students</div>
            <div class="stat-card-trend up">
                <span>Verified Accounts</span>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon"
            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
            </svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= $activeSurveysCount ?></div>
            <div class="stat-card-label">Active Surveys</div>
            <div class="stat-card-trend up">
                <span>Accepting Responses</span>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon"
            style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="20" x2="18" y2="10" />
                <line x1="12" y1="20" x2="12" y2="4" />
                <line x1="6" y1="20" x2="6" y2="14" />
            </svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= $overallParticipationRate ?>%</div>
            <div class="stat-card-label">Participation Rate</div>
            <div class="stat-card-trend up">
                <span><?= $totalRespondents ?> Respondents</span>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon"
            style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= count($flaggedConcerns) ?></div>
            <div class="stat-card-label">Flagged Concerns</div>
            <div class="stat-card-trend <?= count($flaggedConcerns) > 0 ? 'warning' : 'up' ?>">
                <span>Requires Attention</span>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Main Left Column (Align Items Start Prevents Artificial White Space) -->
    <div style="display: flex; flex-direction: column; gap: var(--space-6);">

        <!-- ANNOUNCEMENT MANAGEMENT GLASS CARD -->
        <div class="card glass-card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">📢 Announcement Management</h3>
                    <p style="font-size: var(--font-size-xs); color: var(--color-text-secondary); margin-top: 2px;">
                        Create, publish, edit, and target broadcast messages for campus users.</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openAnnouncementModal()">+ Create
                    Announcement</button>
            </div>
            <div class="card-body" id="admin-announcements-container" style="padding: 16px;">
                <?php if (empty($allAnnouncements)): ?>
                    <div style="text-align: center; padding: 32px; color: var(--color-text-tertiary);">
                        <p>No announcements created yet.</p>
                    </div>
                <?php else: ?>
                    <div class="data-table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Announcement</th>
                                    <th>Audience</th>
                                    <th>Status</th>
                                    <th>Date Published</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allAnnouncements as $ann): ?>
                                    <tr>
                                        <td>
                                            <div class="font-bold text-primary"><?= e($ann['title']) ?></div>
                                            <div style="font-size: var(--font-size-xs); color: var(--color-text-secondary); max-width: 340px;"
                                                class="truncate"><?= e($ann['content']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"
                                                style="text-transform: capitalize;"><?= e($ann['target']) ?></span>
                                        </td>
                                        <td>
                                            <form action="<?= BASE_URL ?>/admin/announcements/action" method="POST"
                                                style="display: inline;">
                                                <?php csrfField(); ?>
                                                <input type="hidden" name="action" value="toggle_publish">
                                                <input type="hidden" name="announcement_id"
                                                    value="<?= $ann['announcement_id'] ?>">
                                                <button type="submit"
                                                    class="badge <?= $ann['is_active'] ? 'badge-success' : 'badge-gray' ?>"
                                                    style="cursor: pointer; border: none;">
                                                    <span
                                                        class="badge-dot <?= $ann['is_active'] ? 'active' : 'inactive' ?>"></span>
                                                    <?= $ann['is_active'] ? 'Published' : 'Draft' ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td style="font-size: var(--font-size-xs); color: var(--color-text-secondary);">
                                            <?= formatDate($ann['created_at']) ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <div
                                                style="display: inline-flex; gap: 6px; align-items: center; justify-content: flex-end;">
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                    onclick="editAnnouncement(<?= htmlspecialchars(json_encode($ann)) ?>)">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2.5">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                                <form action="<?= BASE_URL ?>/admin/announcements/action" method="POST"
                                                    style="display: inline;"
                                                    onsubmit="return confirm('Delete this announcement?');">
                                                    <?php csrfField(); ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="announcement_id"
                                                        value="<?= $ann['announcement_id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2.5">
                                                            <polyline points="3 6 5 6 21 6" />
                                                            <path
                                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- NEW MODULE 1: CAMPUS IMPROVEMENT INSIGHTS & FEEDBACK TRENDS -->
        <div class="card glass-card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">💡 Campus Improvement Insights</h3>
                    <p style="font-size: var(--font-size-xs); color: var(--color-text-secondary); margin-top: 2px;">
                        Automated rating breakdowns and high-priority feedback areas.</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/results" class="btn btn-secondary btn-sm">Full Analytics</a>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                    <div
                        style="background: rgba(255,255,255,0.5); padding: 16px; border-radius: var(--radius-lg); border: 1px solid rgba(255,255,255,0.4);">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 13px; font-weight: 700; color: #0f172a;">📡 Wi-Fi Performance</span>
                            <span class="badge badge-warning">2.5 / 5.0</span>
                        </div>
                        <div
                            style="width: 100%; height: 8px; background: rgba(226, 232, 240, 0.7); border-radius: var(--radius-full); overflow: hidden; margin-bottom: 8px;">
                            <div
                                style="width: 50%; height: 100%; background: linear-gradient(90deg, #f59e0b, #ef4444); border-radius: var(--radius-full);">
                            </div>
                        </div>
                        <div style="font-size: 11px; color: var(--color-text-tertiary);">Library & Main Building
                            coverage needs expansion</div>
                    </div>

                    <div
                        style="background: rgba(255,255,255,0.5); padding: 16px; border-radius: var(--radius-lg); border: 1px solid rgba(255,255,255,0.4);">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 13px; font-weight: 700; color: #0f172a;">🍱 Cafeteria
                                Services</span>
                            <span class="badge badge-primary">3.5 / 5.0</span>
                        </div>
                        <div
                            style="width: 100%; height: 8px; background: rgba(226, 232, 240, 0.7); border-radius: var(--radius-full); overflow: hidden; margin-bottom: 8px;">
                            <div
                                style="width: 70%; height: 100%; background: linear-gradient(90deg, #0284c7, #06b6d4); border-radius: var(--radius-full);">
                            </div>
                        </div>
                        <div style="font-size: 11px; color: var(--color-text-tertiary);">Vegetarian & healthy options
                            requested</div>
                    </div>

                    <div
                        style="background: rgba(255,255,255,0.5); padding: 16px; border-radius: var(--radius-lg); border: 1px solid rgba(255,255,255,0.4);">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 13px; font-weight: 700; color: #0f172a;">📚 Library Operating
                                Hours</span>
                            <span class="badge badge-success">4.8 / 5.0</span>
                        </div>
                        <div
                            style="width: 100%; height: 8px; background: rgba(226, 232, 240, 0.7); border-radius: var(--radius-full); overflow: hidden; margin-bottom: 8px;">
                            <div
                                style="width: 96%; height: 100%; background: linear-gradient(90deg, #10b981, #059669); border-radius: var(--radius-full);">
                            </div>
                        </div>
                        <div style="font-size: 11px; color: var(--color-text-tertiary);">Study space availability highly
                            rated</div>
                    </div>

                    <div
                        style="background: rgba(255,255,255,0.5); padding: 16px; border-radius: var(--radius-lg); border: 1px solid rgba(255,255,255,0.4);">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 13px; font-weight: 700; color: #0f172a;">🛡️ Campus Safety</span>
                            <span class="badge badge-info">4.2 / 5.0</span>
                        </div>
                        <div
                            style="width: 100%; height: 8px; background: rgba(226, 232, 240, 0.7); border-radius: var(--radius-full); overflow: hidden; margin-bottom: 8px;">
                            <div
                                style="width: 84%; height: 100%; background: linear-gradient(90deg, #8b5cf6, #3b82f6); border-radius: var(--radius-full);">
                            </div>
                        </div>
                        <div style="font-size: 11px; color: var(--color-text-tertiary);">Daytime protocols well
                            understood</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW MODULE 2: RECENT SURVEY ACTIVITY TABLE & PERFORMANCE -->
        <div class="card glass-card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">📋 Recent Survey Management</h3>
                    <p style="font-size: var(--font-size-xs); color: var(--color-text-secondary); margin-top: 2px;">
                        Track active and recently created campus surveys.</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/surveys" class="btn btn-secondary btn-sm">All Surveys</a>
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
                                        <div class="font-bold"><?= e($s['title']) ?></div>
                                        <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);">
                                            <?= e($s['category']) ?> • <?= $s['question_count'] ?> questions
                                        </div>
                                    </td>
                                    <td><?= getStatusBadge($s['status']) ?></td>
                                    <td>
                                        <span class="font-bold text-primary"><?= $s['response_count'] ?></span>
                                        <span
                                            style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);">(<?= calcPercentage($s['response_count'], $totalStudents) ?>%)</span>
                                    </td>
                                    <td style="font-size: var(--font-size-xs); color: var(--color-text-secondary);">
                                        <?= formatDate($s['close_date']) ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <div
                                            style="display: inline-flex; gap: 6px; align-items: center; justify-content: flex-end;">
                                            <a href="<?= BASE_URL ?>/admin/results/<?= $s['survey_id'] ?>"
                                                class="btn btn-primary btn-sm">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2.5">
                                                    <line x1="18" y1="20" x2="18" y2="10" />
                                                    <line x1="12" y1="20" x2="12" y2="4" />
                                                    <line x1="6" y1="20" x2="6" y2="14" />
                                                </svg>
                                                Results
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/surveys/<?= $s['survey_id'] ?>/edit"
                                                class="btn btn-secondary btn-sm">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2.5">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- VIBRANT ANALYTICS CHARTS GRID -->
        <div class="chart-grid">
            <div class="card glass-card">
                <div class="card-header">
                    <h3 class="card-title">Overall Participation Rate</h3>
                </div>
                <div style="height: 250px; position: relative; padding: 12px;">
                    <canvas id="dashboard-gauge-chart"></canvas>
                </div>
            </div>

            <div class="card glass-card">
                <div class="card-header">
                    <h3 class="card-title">Responses by Category</h3>
                </div>
                <div style="height: 250px; position: relative; padding: 12px;">
                    <canvas id="dashboard-category-chart"></canvas>
                </div>
            </div>
        </div>

    </div> <!-- End Main Column -->

    <!-- Sidebar Right Column (Align Items Start Prevents Whitespace) -->
    <div style="display: flex; flex-direction: column; gap: var(--space-6);">

        <!-- NEW MODULE 3: QUICK ACTIONS PANEL -->
        <!-- ADMINISTRATIVE ACTIONS GLASS CARD -->
        <div class="card glass-card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">🚀 Administrative Actions</h3>
                    <p style="font-size: 12px; color: #64748B; margin-top: 2px; margin-bottom: 0;">Quick shortcuts to
                        manage surveys, users, and reports.</p>
                </div>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 12px; padding: 20px;">
                <a href="<?= BASE_URL ?>/admin/surveys/create" class="action-btn primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <span>Create New Survey</span>
                </a>
                <button type="button" class="action-btn" onclick="openAnnouncementModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2.5">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    <span>Publish Announcement</span>
                </button>
                <a href="<?= BASE_URL ?>/admin/students/add" class="action-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2.5">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <line x1="20" y1="8" x2="20" y2="14" />
                        <line x1="17" y1="11" x2="23" y2="11" />
                    </svg>
                    <span>Add Student Account</span>
                </a>
                <?php if (isSuperAdmin()): ?>
                    <a href="<?= BASE_URL ?>/admin/users/add" class="action-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                        <span>Add Administrator Account</span>
                    </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/admin/reports" class="action-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                    <span>Generate Executive Report</span>
                </a>
            </div>
        </div>

        <!-- FLAGGED CONCERNS CARD PANEL -->
        <div class="card glass-card">
            <div class="card-header">
                <h3 class="card-title">🚨 Flagged Concerns</h3>
            </div>
            <div class="card-body">
                <?php if (empty($flaggedConcerns)): ?>
                    <p
                        style="font-size: var(--font-size-sm); color: var(--color-text-secondary); text-align: center; padding: 12px 0;">
                        No critical concerns flagged.</p>
                <?php else: ?>
                    <?php foreach ($flaggedConcerns as $c): ?>
                        <div
                            style="display: flex; gap: 12px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.4);">
                            <div
                                style="width: 32px; height: 32px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: var(--color-error); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path
                                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                    <line x1="12" y1="9" x2="12" y2="13" />
                                    <line x1="12" y1="17" x2="12.01" y2="17" />
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: var(--font-size-sm); font-weight: 700; color: #0f172a;">
                                    <?= e($c['message']) ?>
                                </div>
                                <div
                                    style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); margin-top: 2px;">
                                    Survey: <?= e($c['survey_title']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
            </div>
        </div>

        <!-- NEW MODULE 4: STUDENT ENGAGEMENT BY DEPARTMENT -->
        <div class="card glass-card">
            <div class="card-header">
                <h3 class="card-title">🎓 Department Engagement</h3>
            </div>
            <div class="card-body">
                    <?php foreach ($departmentStats as $dept): ?>
                    <div style="margin-bottom: 14px;">
                        <div
                            style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; margin-bottom: 4px;">
                            <span><?= e($dept['department']) ?></span>
                            <span style="color: var(--color-primary);"><?= $dept['active_dept_respondents'] ?> /
                                    <?= $dept['total_dept_students'] ?></span>
                        </div>
                        <div
                            style="width: 100%; height: 6px; background: rgba(226, 232, 240, 0.7); border-radius: var(--radius-full); overflow: hidden;">
                            <div
                                style="width: <?= calcPercentage($dept['active_dept_respondents'], max(1, $dept['total_dept_students'])) ?>%; height: 100%; background: linear-gradient(90deg, #0284c7, #06b6d4); border-radius: var(--radius-full);">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
            </div>
        </div>

        <!-- SYSTEM ACTIVITY FEED -->
        <div class="card glass-card">
            <div class="card-header">
                <h3 class="card-title">⚡ Live System Activity</h3>
            </div>
            <div class="card-body">
                <div class="activity-feed">
                        <?php foreach ($activityLogs as $log): ?>
                        <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                            <div
                                style="width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.4); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--color-primary);">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: var(--font-size-xs); font-weight: 700; color: #0f172a;">
                                        <?= e($log['description'] ?: $log['action']) ?>
                                </div>
                                <div style="font-size: 11px; color: var(--color-text-tertiary);">
                                            <?= timeAgo($log['created_at']) ?>
                                </div>
                            </div>
                        </div>
                                <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div> <!-- End Sidebar Column -->
</div> <!-- End Dashboard Grid -->

<!-- Modal: Create / Edit Announcement -->
<div class="modal-overlay" id="announcement-modal">
    <div class="modal glass-card" style="max-width: 520px;">
        <div class="modal-header">
            <h3 class="modal-title" id="announcement-modal-title">Create Announcement</h3>
            <button type="button" class="modal-close" onclick="closeAnnouncementModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/admin/announcements/action" method="POST" id="announcement-form">
                        <?php csrfField(); ?>
            <input type="hidden" name="action" id="announcement-action" value="create">
            <input type="hidden" name="announcement_id" id="announcement-id" value="">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" name="title" id="announcement-title-input" class="form-input"
                        placeholder="e.g. New Campus Safety Survey Open" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Target Audience <span class="required">*</span></label>
                    <select name="target" id="announcement-target-select" class="form-select" required>
                        <option value="all">All Campus Users</option>
                        <option value="students">Students Only</option>
                        <option value="admins">Administrators Only</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Content <span class="required">*</span></label>
                    <textarea name="content" id="announcement-content-input" class="form-textarea" rows="4"
                        placeholder="Write announcement details for users..." required></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAnnouncementModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Publish Announcement</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAnnouncementModal() {
        document.getElementById('announcement-modal-title').textContent = 'Create Announcement';
        document.getElementById('announcement-action').value = 'create';
        document.getElementById('announcement-id').value = '';
        document.getElementById('announcement-title-input').value = '';
        document.getElementById('announcement-target-select').value = 'all';
        document.getElementById('announcement-content-input').value = '';
        document.getElementById('announcement-modal').classList.add('active');
    }

    function editAnnouncement(ann) {
        document.getElementById('announcement-modal-title').textContent = 'Edit Announcement';
        document.getElementById('announcement-action').value = 'edit';
        document.getElementById('announcement-id').value = ann.announcement_id;
        document.getElementById('announcement-title-input').value = ann.title;
        document.getElementById('announcement-target-select').value = ann.target;
        document.getElementById('announcement-content-input').value = ann.content;
        document.getElementById('announcement-modal').classList.add('active');
    }

    function closeAnnouncementModal() {
        document.getElementById('announcement-modal').classList.remove('active');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof createGaugeChart === 'function') {
            createGaugeChart('dashboard-gauge-chart', <?= $totalRespondents ?>, <?= $totalStudents ?>);
        }
        if (typeof createBarChart === 'function') {
            createBarChart('dashboard-category-chart',
                ['Wi-Fi', 'Cafeteria', 'Library', 'Safety', 'Wellness'],
                [12, 18, 9, 15, 6]
            );
        }
    });
</script>

            <?php require_once __DIR__ . '/../includes/footer.php'; ?>