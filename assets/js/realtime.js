/**
 * CampusVoice — Real-time AJAX Updates with jQuery
 * Polls backend API periodically for live announcement & notification updates.
 */

(function ($) {
    'use strict';

    var lastMaxAnnouncementId = null;
    var isFirstLoad = true;

    function renderStudentAnnouncementCard(ann) {
        return `
            <div class="announcement-item card" style="margin-bottom: 12px; padding: 16px; border-left: 4px solid var(--color-primary, #3B82F6); background: rgba(255,255,255,0.7); backdrop-filter: blur(8px);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                    <h4 style="margin: 0; font-size: 15px; font-weight: 600; color: var(--color-text-main, #1E293B);">${ann.title}</h4>
                    <span style="font-size: 11px; color: var(--color-text-muted, #64748B);">${ann.time_ago}</span>
                </div>
                <p style="margin: 0 0 8px 0; font-size: 13px; color: var(--color-text-secondary, #475569); line-height: 1.5;">${ann.content}</p>
                <div style="font-size: 11px; color: var(--color-text-muted, #94A3B8); font-weight: 500;">
                    Posted by <span>${ann.author_name}</span>
                </div>
            </div>
        `;
    }

    function renderAdminAnnouncementRow(ann) {
        var statusBadge = ann.is_active 
            ? '<span class="badge badge-success">Active</span>' 
            : '<span class="badge badge-secondary">Inactive</span>';
        
        return `
            <div class="announcement-admin-card" style="padding: 12px; margin-bottom: 10px; border-radius: 8px; background: rgba(255,255,255,0.6); border: 1px solid rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong style="font-size: 14px;">${ann.title}</strong>
                    <div>${statusBadge}</div>
                </div>
                <p style="font-size: 12px; color: #64748B; margin: 4px 0 8px 0;">${ann.content}</p>
                <div style="font-size: 11px; color: #94A3B8; display: flex; justify-content: space-between;">
                    <span>Target: <strong>${ann.target}</strong></span>
                    <span>${ann.time_ago}</span>
                </div>
            </div>
        `;
    }

    function fetchLiveUpdates() {
        if (typeof window.BASE_URL === 'undefined') return;

        $.ajax({
            url: window.BASE_URL + '/api/live-updates',
            method: 'GET',
            dataType: 'json',
            cache: false,
            success: function (response) {
                if (!response || !response.success) return;

                var announcements = response.announcements || [];
                var count = response.count || 0;
                var currentMaxId = announcements.length > 0 ? announcements[0].announcement_id : 0;

                // Toast notification on new announcement arrival
                if (!isFirstLoad && lastMaxAnnouncementId !== null && currentMaxId > lastMaxAnnouncementId) {
                    var latest = announcements[0];
                    if (typeof window.showToast === 'function') {
                        window.showToast('📢 New Announcement', latest.title, 'info');
                    }
                }

                lastMaxAnnouncementId = currentMaxId;
                isFirstLoad = false;

                // Update announcement count elements
                $('[data-live-announcement-count]').text(count);

                // Update Student Dashboard Announcements Feed
                var $studentContainer = $('#student-announcements-container');
                if ($studentContainer.length) {
                    if (announcements.length === 0) {
                        $studentContainer.html('<p style="font-size: 13px; color: #64748B; padding: 12px; text-align: center;">No active announcements at this time.</p>');
                    } else {
                        var htmlStr = announcements.map(renderStudentAnnouncementCard).join('');
                        $studentContainer.html(htmlStr);
                    }
                }

                // Update Student Notifications Page Feed
                var $studentNotificationsFeed = $('#student-notifications-feed');
                if ($studentNotificationsFeed.length) {
                    if (announcements.length === 0) {
                        $studentNotificationsFeed.html('<p class="empty-state-description">Check back later for system announcements and survey updates.</p>');
                    } else {
                        var feedHtml = announcements.map(renderStudentAnnouncementCard).join('');
                        $studentNotificationsFeed.html(feedHtml);
                    }
                }

                // Update Admin Dashboard Announcements List
                var $adminContainer = $('#admin-announcements-container');
                if ($adminContainer.length) {
                    if (announcements.length === 0) {
                        $adminContainer.html('<p style="font-size: 13px; color: #64748B;">No announcements created yet.</p>');
                    } else {
                        var adminHtml = announcements.map(renderAdminAnnouncementRow).join('');
                        $adminContainer.html(adminHtml);
                    }
                }
            },
            error: function (xhr, status, error) {
                // Silently handle polling errors
            }
        });
    }

    // Expose for manual triggering
    window.refreshLiveUpdates = fetchLiveUpdates;

    $(document).ready(function () {
        // Run immediately on page load
        fetchLiveUpdates();

        // Poll every 5 seconds for live AJAX updates
        setInterval(fetchLiveUpdates, 5000);
    });

})(jQuery);
