<?php
/** CampusVoice — 401 Unauthorized */
require_once __DIR__ . '/../config/constants.php';
http_response_code(401);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized — <?= APP_NAME ?></title>
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
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div class="system-page-code">401</div>
            <h1 class="system-page-title">Access Denied</h1>
            <p class="system-page-description">You don't have permission to access this page. This area is restricted to authorized users only.</p>
            <a href="<?= BASE_URL ?>/" class="btn btn-primary btn-lg">← Go Home</a>
        </div>
    </div>
</body>
</html>
