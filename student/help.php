<?php
/**
 * CampusVoice — Help Center / FAQ Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(ROLE_STUDENT);
checkPasswordChange();
require_once __DIR__ . '/../utils/helpers.php';

$pageTitle = 'Help Center & FAQ';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/student/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Help Center</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">Help Center & FAQ</h2>
        <p class="content-subtitle">Frequently asked questions and support contact details</p>
    </div>
</div>

<div class="dashboard-grid">
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Frequently Asked Questions</h3>
            </div>
            <div class="card-body">
                <div class="faq-item">
                    <button class="faq-question">
                        <span>How many times can I answer a survey?</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        Each student can submit answers for a specific survey exactly once. Once you submit your response, the survey will disappear from your Available Surveys list.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>Are my survey responses anonymous?</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        Aggregated statistics (percentages, rating averages, charts) presented to campus staff do not disclose individual names. However, text answers and response records are managed securely under administrative access rules.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>What happens if a survey closes while I am filling it out?</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        Surveys have strict open and close dates. If a survey closes or is deactivated before you click submit, the system will prevent submission and redirect you to the Survey Closed notice.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>How do I change my account password?</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        You can change your password anytime by clicking on your name/avatar in the top right menu or selecting "Change Password" from the left sidebar navigation menu.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>What should I do if I encounter an error?</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="faq-answer">
                        If you encounter technical issues or errors while answering surveys, try clearing your browser cache or contact the campus IT support team using the details provided on this page.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Support Contact</h3>
            </div>
            <div class="card-body">
                <p>If you need further assistance regarding your account or survey access, reach out to us:</p>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Email Support:</div>
                    <div class="profile-detail-value"><a href="mailto:<?= APP_SUPPORT_EMAIL ?>"><?= APP_SUPPORT_EMAIL ?></a></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Institution:</div>
                    <div class="profile-detail-value"><?= APP_SCHOOL ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Office:</div>
                    <div class="profile-detail-value">IT Services / Student Affairs Division</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
