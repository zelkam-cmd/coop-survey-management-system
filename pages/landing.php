<?php
/**
 * CampusVoice — Landing Page
 * Public marketing/intro page explaining the platform.
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    if (getCurrentRole() === ROLE_STUDENT) {
        header('Location: ' . BASE_URL . '/student/dashboard');
    } else {
        header('Location: ' . BASE_URL . '/admin/dashboard');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= APP_TAGLINE ?> — <?= APP_NAME ?> is a campus improvement platform where every student voice shapes a better campus.">
    <title><?= APP_NAME ?> — <?= APP_TAGLINE ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pages.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
</head>
<body>
    <div class="public-layout">
        <!-- Header -->
        <header class="public-header">
            <a href="<?= BASE_URL ?>/" class="public-logo">
                <div class="public-logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
                <span class="public-logo-text"><?= APP_NAME ?></span>
            </a>
            <a href="<?= BASE_URL ?>/login" class="btn btn-primary">Sign In</a>
        </header>

        <!-- Hero Section -->
        <div class="public-content" style="flex-direction: column; padding: 0 var(--space-8);">
            <div class="landing-hero">
                <div class="landing-hero-content">
                    <div class="landing-hero-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                        Campus Improvement Platform
                    </div>
                    <h1 class="landing-hero-title">
                        Every Student Voice <span>Shapes a Better Campus</span>
                    </h1>
                    <p class="landing-hero-text">
                        Share your feedback about campus life — from Wi-Fi and cafeteria to safety and wellness. 
                        Your responses are automatically compiled into actionable insights that drive real campus improvements.
                    </p>
                    <div class="landing-hero-cta">
                        <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-lg">
                            Get Started
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                        <a href="#features" class="btn btn-secondary btn-lg">Learn More</a>
                    </div>
                </div>
                <div class="landing-hero-visual">
                    <div class="landing-hero-illustration">
                        <svg width="200" height="160" viewBox="0 0 200 160" fill="none" style="position:relative;z-index:1;">
                            <!-- Survey form illustration -->
                            <rect x="30" y="20" width="140" height="120" rx="12" fill="white" stroke="#E2E8F0" stroke-width="2"/>
                            <rect x="45" y="35" width="80" height="8" rx="4" fill="#DBEAFE"/>
                            <rect x="45" y="52" width="110" height="6" rx="3" fill="#F1F5F9"/>
                            <rect x="45" y="65" width="95" height="6" rx="3" fill="#F1F5F9"/>
                            <!-- Checkmarks -->
                            <circle cx="50" cy="88" r="7" fill="#D1FAE5"/>
                            <path d="M47 88l2 2 4-4" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="62" y="85" width="70" height="6" rx="3" fill="#F1F5F9"/>
                            <circle cx="50" cy="108" r="7" fill="#DBEAFE"/>
                            <path d="M47 108l2 2 4-4" stroke="#2563EB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="62" y="105" width="55" height="6" rx="3" fill="#F1F5F9"/>
                            <!-- Stars -->
                            <text x="145" y="42" font-size="14">⭐</text>
                            <text x="140" y="90" font-size="18">📊</text>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="landing-features" id="features">
                <h2 class="landing-features-title">Built for Campus Excellence</h2>
                <p class="landing-features-subtitle">A modern platform designed to turn student feedback into measurable campus improvements.</p>
                
                <div class="landing-features-grid">
                    <div class="feature-card">
                        <div class="feature-card-icon" style="background: var(--color-primary-lighter);">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                        </div>
                        <h3 class="feature-card-title">Smart Surveys</h3>
                        <p class="feature-card-text">Multiple question types — Multiple Choice, Yes/No, Rating Scale, and Short Answer — with automated scheduling and one-click publishing.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-card-icon" style="background: var(--color-secondary-lighter);">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--color-secondary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                        </div>
                        <h3 class="feature-card-title">Real-Time Analytics</h3>
                        <p class="feature-card-text">Automatically computed statistics with interactive charts — pie, bar, line, and histograms — for instant insights into student sentiment.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-card-icon" style="background: var(--color-accent-lighter);">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                        <h3 class="feature-card-title">Printable Reports</h3>
                        <p class="feature-card-text">Generate summary, detailed, participation, and recommendation reports — print them or export to CSV for meetings and budget discussions.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-card-icon" style="background: var(--color-error-light);">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--color-error)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </div>
                        <h3 class="feature-card-title">Flagged Concerns</h3>
                        <p class="feature-card-text">Automatically detects and highlights critical issues — like "82% report slow internet" — so administrators know exactly where to act first.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-card-icon" style="background: var(--color-warning-light);">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--color-warning)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <h3 class="feature-card-title">Secure & Private</h3>
                        <p class="feature-card-text">Role-based access, encrypted passwords, CSRF protection, prepared SQL statements, and session management — your data stays safe.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-card-icon" style="background: var(--color-info-light);">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                        </div>
                        <h3 class="feature-card-title">Fully Responsive</h3>
                        <p class="feature-card-text">Beautifully designed for desktop, tablet, and mobile. Answer surveys on any device, anywhere on campus.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="public-footer">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> — <?= APP_SCHOOL ?>. <?= APP_TAGLINE ?></p>
        </footer>
    </div>
    
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
