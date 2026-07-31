<?php
/**
 * CampusVoice — Student Sidebar Navigation
 */
$currentPage = $_GET['route'] ?? '';
?>
<aside class="app-sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"></path>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
            </svg>
        </div>
        <div class="sidebar-brand">
            <span class="sidebar-brand-name"><?= APP_NAME ?></span>
            <span class="sidebar-brand-tagline">Student Portal</span>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="sidebar-section">
            <div class="sidebar-section-title">Menu</div>
            
            <a href="<?= BASE_URL ?>/student/dashboard" class="sidebar-link <?= $currentPage === 'student/dashboard' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span class="sidebar-link-text">Dashboard</span>
            </a>
            
            <a href="<?= BASE_URL ?>/student/surveys" class="sidebar-link <?= (strpos($currentPage, 'student/surveys') === 0) ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span class="sidebar-link-text">Surveys</span>
                <?php
                // Show count of available surveys
                if (isset($pendingSurveyCount) && $pendingSurveyCount > 0):
                ?>
                <span class="sidebar-link-badge"><?= $pendingSurveyCount ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-section-title">Account</div>
            
            <a href="<?= BASE_URL ?>/student/profile" class="sidebar-link <?= $currentPage === 'student/profile' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span class="sidebar-link-text">Profile</span>
            </a>
            
            <a href="<?= BASE_URL ?>/change-password" class="sidebar-link <?= $currentPage === 'change-password' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span class="sidebar-link-text">Change Password</span>
            </a>
            
            <a href="<?= BASE_URL ?>/student/notifications" class="sidebar-link <?= $currentPage === 'student/notifications' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <span class="sidebar-link-text">Notifications</span>
            </a>
            
            <a href="<?= BASE_URL ?>/student/help" class="sidebar-link <?= $currentPage === 'student/help' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span class="sidebar-link-text">Help Center</span>
            </a>
        </div>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/logout" class="sidebar-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span class="sidebar-link-text">Logout</span>
        </a>
    </div>
</aside>
