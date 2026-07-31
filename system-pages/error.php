<?php
/** CampusVoice — Generic Error Page */
require_once __DIR__ . '/../config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error — <?= APP_NAME ?></title>
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
            <div class="system-page-icon" style="background: var(--color-error-light);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-error)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <h1 class="system-page-title">Something Went Wrong</h1>
            <p class="system-page-description">An unexpected error occurred. Please try again or contact support if the problem persists.</p>
            <div style="display: flex; gap: var(--space-3); justify-content: center;">
                <a href="javascript:history.back()" class="btn btn-secondary">← Go Back</a>
                <a href="<?= BASE_URL ?>/" class="btn btn-primary">Go Home</a>
            </div>
        </div>
    </div>
</body>
</html>
