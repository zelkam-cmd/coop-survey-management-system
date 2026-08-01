<?php
/**
 * CampusVoice — Administrator Sidebar Navigation (Executive Dark Glass Theme)
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
            <span class="sidebar-brand-tagline"><?= (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin') ? 'Head Admin Panel' : 'Admin Panel' ?></span>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="sidebar-section">
            <div class="sidebar-section-title">Overview</div>
            
            <a href="<?= BASE_URL ?>/admin/dashboard" class="sidebar-link <?= $currentPage === 'admin/dashboard' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span class="sidebar-link-text">Dashboard</span>
            </a>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-section-title">Survey Management</div>
            
            <a href="<?= BASE_URL ?>/admin/surveys" class="sidebar-link <?= ($currentPage === 'admin/surveys' || strpos($currentPage, 'admin/surveys') === 0) ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <span class="sidebar-link-text">Surveys</span>
            </a>
            
            <a href="<?= BASE_URL ?>/admin/results" class="sidebar-link <?= (strpos($currentPage, 'admin/results') === 0) ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                <span class="sidebar-link-text">Results</span>
            </a>
            
            <a href="<?= BASE_URL ?>/admin/reports" class="sidebar-link <?= (strpos($currentPage, 'admin/reports') === 0) ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <span class="sidebar-link-text">Reports</span>
            </a>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-section-title">User Management</div>
            
            <a href="<?= BASE_URL ?>/admin/students" class="sidebar-link <?= (strpos($currentPage, 'admin/students') === 0) ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span class="sidebar-link-text">Students</span>
            </a>
            
            <?php if (isSuperAdmin()): ?>
            <a href="<?= BASE_URL ?>/admin/users" class="sidebar-link <?= (strpos($currentPage, 'admin/users') === 0) ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <span class="sidebar-link-text">Administrators</span>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-section-title">System</div>
            
            <a href="<?= BASE_URL ?>/admin/activity-logs" class="sidebar-link <?= $currentPage === 'admin/activity-logs' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
                <span class="sidebar-link-text">Activity Logs</span>
            </a>
            
            <a href="<?= BASE_URL ?>/admin/notifications" class="sidebar-link <?= $currentPage === 'admin/notifications' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <span class="sidebar-link-text">Notifications</span>
            </a>
            
            <a href="<?= BASE_URL ?>/admin/settings" class="sidebar-link <?= $currentPage === 'admin/settings' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <span class="sidebar-link-text">Settings</span>
            </a>
        </div>

        <!-- System Health Widget (Fills Empty Sidebar Gap) -->
        <div style="margin: 24px 12px 12px; padding: 14px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 14px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <span style="font-size: 10px; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em;">System Status</span>
                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 700; color: #34D399; background: rgba(52, 211, 153, 0.15); padding: 2px 8px; border-radius: 99px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #34D399; display: inline-block;"></span> Online
                </span>
            </div>
            <div style="font-size: 12px; font-weight: 700; color: #F8FAFC; margin-bottom: 2px;">CampusVoice SaaS v2.4</div>
            <div style="font-size: 10px; color: #94A3B8;">Active Database • SSL Secure</div>
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
