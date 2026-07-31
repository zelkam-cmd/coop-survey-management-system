<?php
/**
 * CampusVoice — Admin Settings Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../utils/helpers.php';

$pageTitle = 'System Settings';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Settings</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">System Settings</h2>
        <p class="content-subtitle">Global platform configuration, parameters, and thresholds</p>
    </div>
</div>

<div style="max-width: 680px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">General Platform Parameters</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Application Name</label>
                <input type="text" class="form-input" value="<?= APP_NAME ?>" disabled style="background: var(--color-gray-100);">
            </div>

            <div class="form-group">
                <label class="form-label">School / Campus</label>
                <input type="text" class="form-input" value="<?= APP_SCHOOL ?>" disabled style="background: var(--color-gray-100);">
            </div>

            <div class="form-group">
                <label class="form-label">Flagged Concern Threshold (%)</label>
                <input type="text" class="form-input" value="<?= CONCERN_THRESHOLD ?>%" disabled style="background: var(--color-gray-100);">
                <div class="form-hint">Responses exceeding this percentage on negative answers generate automatic concern flags.</div>
            </div>

            <div class="form-group">
                <label class="form-label">Idle Session Timeout</label>
                <input type="text" class="form-input" value="<?= SESSION_TIMEOUT / 60 ?> minutes" disabled style="background: var(--color-gray-100);">
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
