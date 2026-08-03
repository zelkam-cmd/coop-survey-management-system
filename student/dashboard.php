<?php
/**
 * CampusVoice — Student Dashboard (CoachPro Modern SaaS Glassmorphism)
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(ROLE_STUDENT);
checkPasswordChange();
require_once __DIR__ . '/../utils/helpers.php';

$pdo = db();
$studentId = getCurrentUserId();

// Get student detail
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$studentId]);
$studentData = $stmt->fetch();

// Get pending surveys count
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
$pendingSurveyCount = (int)$stmt->fetchColumn();

// Get completed surveys count
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT survey_id) as count FROM responses WHERE student_id = ?");
$stmt->execute([$studentId]);
$completedCount = (int)$stmt->fetchColumn();

// Get total active surveys
$stmt = $pdo->query("SELECT COUNT(*) FROM surveys WHERE status = 'active'");
$totalActiveSurveys = (int)$stmt->fetchColumn();

// Participation Score
$score = $totalActiveSurveys > 0 ? round(($completedCount / max(1, $completedCount + $pendingSurveyCount)) * 100) : 100;

// Get announcements
$stmt = $pdo->prepare("SELECT a.*, adm.full_name as author_name FROM announcements a LEFT JOIN administrators adm ON a.created_by = adm.admin_id WHERE a.is_active = 1 AND (a.target = 'all' OR a.target = 'students') ORDER BY a.created_at DESC LIMIT 5");
$stmt->execute();
$announcements = $stmt->fetchAll();

// Get available surveys
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
");
$stmt->execute([$studentId]);
$availableSurveys = $stmt->fetchAll();

$pageTitle = 'Student Portal';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Welcome Glass Hero Card -->
<div class="hero-card mb-6">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-4);">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.2); padding: 4px 12px; border-radius: var(--radius-full); font-size: var(--font-size-xs); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                <span>🎓 <?= e($studentData['department'] ?: 'Student Portal') ?> • <?= e($studentData['year_level'] ?: 'BulSU') ?></span>
            </div>
            <h2 style="font-size: var(--font-size-3xl); font-weight: 800; color: white; margin-bottom: 6px;">Welcome back, <?= e(explode(' ', getCurrentUserName())[0]) ?>! 👋</h2>
            <p style="color: rgba(255, 255, 255, 0.95); font-size: var(--font-size-base); margin-bottom: 0; max-width: 600px;">
                You have <strong style="color: #fef08a;"><?= $pendingSurveyCount ?></strong> pending survey<?= $pendingSurveyCount !== 1 ? 's' : '' ?> awaiting your feedback. Every student response shapes a better campus!
            </p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/student/surveys" class="btn" style="background: white; color: var(--color-primary); font-weight: 700; padding: 12px 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.12);">Explore Surveys →</a>
        </div>
    </div>
</div>

<!-- Stats Modular Row -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: white;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= $pendingSurveyCount ?></div>
            <div class="stat-card-label">Pending Surveys</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= $completedCount ?></div>
            <div class="stat-card-label">Completed Surveys</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= $score ?>%</div>
            <div class="stat-card-label">Participation Rate</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>
        <div class="stat-card-content">
            <div class="stat-card-value"><?= count($announcements) ?></div>
            <div class="stat-card-label">Active Broadcasts</div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Main Left Column: Available Surveys -->
    <div>
        <div class="card glass-card mb-6">
            <div class="card-header">
                <div>
                    <h3 class="card-title">📋 Available Campus Surveys</h3>
                    <p style="font-size: var(--font-size-xs); color: var(--color-text-secondary); margin-top: 2px;">Your feedback directly influences campus policies, Wi-Fi upgrades, cafeteria services, and facility improvements.</p>
                </div>
                <a href="<?= BASE_URL ?>/student/surveys" class="btn btn-secondary btn-sm">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($availableSurveys)): ?>
                    <div style="text-align: center; padding: 40px 20px;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--color-success-light); color: var(--color-success); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <h4 style="font-size: var(--font-size-lg); font-weight: 700; color: var(--color-text); margin-bottom: 6px;">All Caught Up!</h4>
                        <p style="font-size: var(--font-size-sm); color: var(--color-text-secondary); max-width: 400px; margin: 0 auto;">You have completed all open surveys. Thank you for making your voice heard!</p>
                    </div>
                <?php else: ?>
                    <div style="display: grid; gap: 16px;">
                        <?php foreach ($availableSurveys as $survey): ?>
                        <div class="card glass-card" style="padding: 20px; border-left: 4px solid var(--color-primary); background: rgba(255,255,255,0.9);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                                <?= getCategoryBadge($survey['category']) ?>
                                <span style="font-size: 11px; font-weight: 600; color: var(--color-warning-dark); background: var(--color-warning-light); padding: 2px 10px; border-radius: var(--radius-full);">
                                    ⏳ Closes: <?= getCountdown($survey['close_date']) ?>
                                </span>
                            </div>
                            <h4 style="font-size: var(--font-size-lg); font-weight: 800; color: var(--color-text); margin-bottom: 6px;"><?= e($survey['title']) ?></h4>
                            <p style="font-size: var(--font-size-sm); color: var(--color-text-secondary); margin-bottom: 14px;"><?= e($survey['description']) ?></p>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; pt-3; border-top: var(--glass-border-subtle); margin-top: 10px;">
                                <span style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); display: flex; align-items: center; gap: 4px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                                    <?= $survey['question_count'] ?> Questions
                                </span>
                                <a href="<?= BASE_URL ?>/student/surveys/<?= $survey['survey_id'] ?>" class="btn btn-primary btn-sm">
                                    Take Survey →
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar Right Column: Announcements & Quick Actions -->
    <div>
        <!-- Quick Actions Card -->
        <div class="card glass-card mb-6">
            <div class="card-header">
                <div>
                    <h3 class="card-title">🚀 Quick Actions</h3>
                    <p style="font-size: 12px; color: var(--color-text-secondary); margin-top: 2px; margin-bottom: 0;">Shortcuts to surveys, announcements, and account settings.</p>
                </div>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 12px; padding: 20px;">
                <a href="<?= BASE_URL ?>/student/surveys" class="action-btn primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>Browse Available Surveys</span>
                </a>
                <a href="<?= BASE_URL ?>/student/notifications" class="action-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span>View Announcements & Alerts</span>
                </a>
                <a href="<?= BASE_URL ?>/student/profile" class="action-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>My Account Profile</span>
                </a>
                <a href="<?= BASE_URL ?>/student/help" class="action-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>Help Center & FAQs</span>
                </a>
                <a href="<?= BASE_URL ?>/change-password" class="action-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span>Change Security Password</span>
                </a>
            </div>
        </div>

        <!-- Campus Announcements Card (Display Published Announcements) -->
        <div class="card glass-card">
            <div class="card-header">
                <h3 class="card-title">📢 Campus Announcements</h3>
            </div>
            <div class="card-body" id="student-announcements-container" style="padding: 16px;">
                <?php if (empty($announcements)): ?>
                    <div style="text-align: center; padding: 24px; color: var(--color-text-tertiary);">
                        <p style="font-size: var(--font-size-xs);">No active announcements at this time.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column;">
                        <?php foreach ($announcements as $ann): ?>
                        <div style="padding: 16px 20px; border-bottom: var(--glass-border-subtle);">
                            <div style="font-size: var(--font-size-sm); font-weight: 800; color: var(--color-primary); margin-bottom: 4px;"><?= e($ann['title']) ?></div>
                            <div style="font-size: var(--font-size-xs); color: var(--color-text-secondary); line-height: 1.5; margin-bottom: 6px;"><?= e($ann['content']) ?></div>
                            <div style="font-size: 10px; color: var(--color-text-tertiary); display: flex; justify-content: space-between;">
                                <span>By <?= e($ann['author_name'] ?: 'Administration') ?></span>
                                <span><?= timeAgo($ann['created_at']) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>