<?php
/**
 * CampusVoice — 404 Page Not Found
 */
require_once __DIR__ . '/../config/constants.php';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found — <?= APP_NAME ?></title>
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
            <div class="system-page-code">404</div>
            <h1 class="system-page-title">Page Not Found</h1>
            <p class="system-page-description">The page you're looking for doesn't exist or has been moved. Check the URL and try again.</p>
            <a href="<?= BASE_URL ?>/" class="btn btn-primary btn-lg">← Go Home</a>
        </div>
    </div>
</body>
</html>
