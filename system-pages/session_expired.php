<?php
/** CampusVoice — Session Expired */
require_once __DIR__ . '/../config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/layout.css">
</head>
<body>
    <div class="system-page">
        <div class="system-page-content">
            <div class="system-page-icon" style="background: var(--color-warning-light);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-warning)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <h1 class="system-page-title">Session Expired</h1>
            <p class="system-page-description">Your session has timed out due to inactivity. Please sign in again to continue.</p>
            <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-lg">Sign In Again</a>
        </div>
    </div>
</body>
</html>
