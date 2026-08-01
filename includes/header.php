<?php
/**
 * CampusVoice — Shared Header
 * Included at the top of every authenticated page.
 * Variables expected: $pageTitle (string)
 */

require_once __DIR__ . '/../config/constants.php';
$currentRole = getCurrentRole();
$userName = getCurrentUserName();
$userInitials = '';
if ($userName) {
    $parts = explode(' ', $userName);
    $userInitials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $userInitials .= strtoupper(substr(end($parts), 0, 1));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= APP_TAGLINE ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — <?= APP_NAME ?></title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    
    <!-- CSS Cache-Busted -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/layout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pages.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/print.css?v=<?= time() ?>">
    <style>
        /* GUARANTEED CRITICAL STYLES FOR INSTANT RENDERING */
        body {
            background-color: #C7D2FE !important;
            background-image: 
                radial-gradient(at 0% 0%, rgba(14, 165, 233, 0.45) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.40) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.40) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(59, 130, 246, 0.40) 0px, transparent 50%),
                linear-gradient(135deg, #BAE6FD 0%, #E0E7FF 40%, #F3E8FF 70%, #D1FAE5 100%) !important;
            background-attachment: fixed !important;
            background-size: cover !important;
        }
        .app-sidebar {
            background: linear-gradient(180deg, #0F172A 0%, #1E293B 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3) !important;
        }
        .app-sidebar .sidebar-brand-name { color: #FFFFFF !important; }
        .app-sidebar .sidebar-brand-tagline { color: #38BDF8 !important; }
        .app-sidebar .sidebar-link { color: #94A3B8 !important; }
        .app-sidebar .sidebar-link:hover { background: rgba(255, 255, 255, 0.1) !important; color: #FFFFFF !important; }
        .app-sidebar .sidebar-link.active { background: linear-gradient(135deg, #0284C7 0%, #2563EB 100%) !important; color: #FFFFFF !important; }
        .app-sidebar .sidebar-link svg { color: #64748B !important; }
        .app-sidebar .sidebar-link.active svg { color: #FFFFFF !important; }
        .app-sidebar .sidebar-section-title { color: #64748B !important; }
        @media (min-width: 992px) {
            .app-main {
                margin-left: calc(250px + 24px) !important;
                padding-right: 24px !important;
                width: calc(100% - 274px) !important;
            }
        }
        @media (max-width: 991px) {
            .app-main {
                margin-left: 0 !important;
                padding-right: 0 !important;
                width: 100% !important;
            }
        }
        .app-content {
            max-width: 100% !important;
            width: 100% !important;
        }
        .app-header {
            background: rgba(255, 255, 255, 0.75) !important;
            backdrop-filter: blur(24px) !important;
            -webkit-backdrop-filter: blur(24px) !important;
            border: 1px solid rgba(255, 255, 255, 0.9) !important;
        }
    </style>
</head>
<body>
    <div class="ambient-blob-center"></div>
    <div class="app-layout">
        <!-- Sidebar -->
        <?php 
        if ($currentRole === ROLE_STUDENT) {
            require_once __DIR__ . '/sidebar_student.php';
        } else {
            require_once __DIR__ . '/sidebar_admin.php';
        }
        ?>
        
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay"></div>
        
        <!-- Main Content Area -->
        <div class="app-main">
            <!-- Header -->
            <header class="app-header">
                <div class="header-left">
                    <button class="hamburger-btn" aria-label="Toggle sidebar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <h1 class="header-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
                </div>
                <div class="header-right">
                    <!-- Notification Bell -->
                    <button class="header-btn" data-tooltip="Notifications" onclick="window.location.href='<?= BASE_URL ?>/<?= $currentRole === ROLE_STUDENT ? 'student' : 'admin' ?>/notifications'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="notification-dot"></span>
                    </button>
                    
                    <!-- Profile Dropdown -->
                    <div class="dropdown">
                        <div class="header-profile dropdown-trigger">
                            <div class="header-profile-info">
                                <div class="header-profile-name"><?= htmlspecialchars($userName ?? 'User') ?></div>
                                <div class="header-profile-role"><?= $currentRole === ROLE_STUDENT ? 'Student' : ((isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') ? 'Head Admin' : 'Administrator') ?></div>
                            </div>
                            <div class="avatar"><?= htmlspecialchars($userInitials) ?></div>
                        </div>
                        <div class="dropdown-menu">
                            <a href="<?= BASE_URL ?>/<?= $currentRole === ROLE_STUDENT ? 'student' : 'admin' ?>/profile" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Profile
                            </a>
                            <a href="<?= BASE_URL ?>/change-password" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Change Password
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= BASE_URL ?>/logout" class="dropdown-item danger">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="app-content">
