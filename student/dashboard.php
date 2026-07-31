<?php
/**
 * CampusVoice — Student Dashboard
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(ROLE_STUDENT);
checkPasswordChange();
require_once __DIR__ . '/../utils/helpers.php';

$pdo = db();
$studentId = getCurrentUserId();

// Get pending surveys count (active, within date range, not answered)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT s.survey_id) as count 
    FROM surveys s 
    WHERE s.status = 'active' 
    AND NOW() >= s.open_date 
    AND NOW() <= s.close_date 
    AND s.survey_id NOT IN (
        SELECT DISTINCT r.survey_id FROM responses r WHERE r.student_id = ?
    )
");
$stmt->execute([$studentId]);
$pendingSurveyCount = $stmt->fetchColumn();

// Get completed surveys count
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT survey_id) as count FROM responses WHERE student_id = ?");
$stmt->execute([$studentId]);
$completedCount = $stmt->fetchColumn();

// Get last submission date
$stmt = $pdo->prepare("SELECT MAX(submitted_at) FROM responses WHERE student_id = ?");
$stmt->execute([$studentId]);
$lastSubmission = $stmt->fetchColumn();

// Get announcements
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE is_active = 1 AND (target = 'all' OR target = 'students') ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$announcements = $stmt->fetchAll();

// Get recent surveys for quick access
$stmt = $pdo->prepare("
    SELECT s.*, 
        (SELECT COUNT(*) FROM survey_questions sq WHERE sq.survey_id = s.survey_id) as question_count
    FROM surveys s 
    WHERE s.status = 'active' 
    AND NOW() >= s.open_date 
    AND NOW() <= s.close_date 
    AND s.survey_id NOT IN (
        SELECT DISTINCT r.survey_id FROM responses r WHERE r.student_id = ?
    )
    ORDER BY s.close_date ASC 
    LIMIT 3
");
$stmt->execute([$studentId]);
$recentSurveys = $stmt->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Welcome Banner -->
<div class="dashboard-welcome">
    <div class="dashboard-welcome-content">
        <h2>Welcome back, <?= e(explode(' ', getCurrentUserName())[0]) ?>! 👋</h2>
        <p>You have <strong><?= $pendingSurveyCount ?></strong> survey<?= $pendingSurveyCount !== 1 ? 's' : '' ?> waiting for your feedback. Your voice matters!</p>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-primary-lighter);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= $pendingSurveyCount ?></div>
            <div class="stat-card-label">Pending Surveys</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-success-light);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-success)" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= $completedCount ?></div>
            <div class="stat-card-label">Completed Surveys</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-accent-lighter);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= $lastSubmission ? formatDate($lastSubmission, 'M d') : '—' ?></div>
            <div class="stat-card-label">Last Submission</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-warning-light);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-warning)" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= count($announcements) ?></div>
            <div class="stat-card-label">Announcements</div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Available Surveys -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Available Surveys</h3>
                <a href="<?= BASE_URL ?>/student/surveys" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentSurveys)): ?>
                    <div class="empty-state" style="padding: var(--space-8) 0;">
                        <div class="empty-state-icon">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div class="empty-state-title">All caught up!</div>
                        <p class="empty-state-description">No pending surveys right now. Check back later for new ones.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentSurveys as $survey): ?>
                    <div class="survey-card" style="margin-bottom: var(--space-3);">
                        <?= getCategoryBadge($survey['category']) ?>
                        <div class="survey-card-title"><?= e($survey['title']) ?></div>
                        <div class="survey-card-description"><?= e($survey['description']) ?></div>
                        <div class="survey-card-meta">
                            <span class="survey-card-meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                                <?= $survey['question_count'] ?> questions
                            </span>
                            <span class="survey-card-meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <?= getCountdown($survey['close_date']) ?>
                            </span>
                        </div>
                        <div class="survey-card-footer">
                            <span></span>
                            <a href="<?= BASE_URL ?>/student/surveys/<?= $survey['survey_id'] ?>" class="btn btn-primary btn-sm">
                                Answer Survey →
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Announcements & Quick Actions -->
    <div>
        <!-- Quick Actions -->
        <div class="card quick-actions-card" style="margin-bottom: var(--space-5);">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: var(--space-2);">
                <a href="<?= BASE_URL ?>/student/surveys" class="btn btn-secondary w-full" style="justify-content: flex-start;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                    View Available Surveys
                </a>
                <a href="<?= BASE_URL ?>/student/profile" class="btn btn-secondary w-full" style="justify-content: flex-start;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    My Profile
                </a>
                <a href="<?= BASE_URL ?>/change-password" class="btn btn-secondary w-full" style="justify-content: flex-start;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Change Password
                </a>
            </div>
        </div>

        <!-- Announcements -->
        <div class="card announcements-card">
            <div class="card-header">
                <h3 class="card-title">📢 Announcements</h3>
            </div>
            <div class="card-body">
                <?php if (empty($announcements)): ?>
                    <p style="color: var(--color-text-tertiary); text-align: center; padding: var(--space-4) 0;">No announcements at this time.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                    <div class="notification-item">
                        <div class="notification-content">
                            <div class="notification-title"><?= e($announcement['title']) ?></div>
                            <div class="notification-text"><?= e($announcement['content']) ?></div>
                            <div class="notification-time"><?= timeAgo($announcement['created_at']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>