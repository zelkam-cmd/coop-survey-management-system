<?php
/**
 * CampusVoice — Application Constants
 * Central configuration for all application-wide values.
 */

// ── Application Info ──────────────────────────────────────
define('APP_NAME', 'CampusVoice');
define('APP_TAGLINE', 'Every Student Voice Shapes a Better Campus');
define('APP_VERSION', '1.0.0');
define('APP_SCHOOL', 'Bulacan State University');
define('APP_SUPPORT_EMAIL', 'support@campusvoice.edu');

// ── Base URL (auto-detect) ────────────────────────────────
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . '://' . $host . '/CampusVoice');

// ── Session Settings ──────────────────────────────────────
define('SESSION_TIMEOUT', 1200); // 20 minutes in seconds
define('SESSION_NAME', 'CAMPUSVOICE_SESSION');

// ── User Roles ────────────────────────────────────────────
define('ROLE_STUDENT', 'student');
define('ROLE_ADMIN', 'admin');
define('ROLE_SUPER_ADMIN', 'super_admin');

// ── Survey Categories ─────────────────────────────────────
define('SURVEY_CATEGORIES', [
    'Wi-Fi'         => ['icon' => 'wifi',       'color' => '#3B82F6'],
    'Cafeteria'     => ['icon' => 'utensils',   'color' => '#F59E0B'],
    'Library'       => ['icon' => 'book-open',  'color' => '#8B5CF6'],
    'Safety'        => ['icon' => 'shield',     'color' => '#EF4444'],
    'Wellness'      => ['icon' => 'heart',      'color' => '#EC4899'],
    'Facilities'    => ['icon' => 'building',   'color' => '#06B6D4'],
    'Academics'     => ['icon' => 'graduation-cap', 'color' => '#10B981'],
    'Events'        => ['icon' => 'calendar',   'color' => '#F97316'],
    'Organizations' => ['icon' => 'users',      'color' => '#6366F1'],
    'Laboratories'  => ['icon' => 'flask',      'color' => '#14B8A6'],
    'General'       => ['icon' => 'clipboard',  'color' => '#64748B'],
]);

// ── Survey Statuses ───────────────────────────────────────
define('SURVEY_STATUS_DRAFT', 'draft');
define('SURVEY_STATUS_ACTIVE', 'active');
define('SURVEY_STATUS_CLOSED', 'closed');
define('SURVEY_STATUS_ARCHIVED', 'archived');

// ── Question Types ────────────────────────────────────────
define('QUESTION_TYPES', [
    'multiple_choice' => 'Multiple Choice',
    'yes_no'          => 'Yes / No',
    'rating'          => 'Rating Scale',
    'short_answer'    => 'Short Answer',
]);

// ── Rating Scale ──────────────────────────────────────────
define('RATING_MIN', 1);
define('RATING_MAX', 5);
define('RATING_LABELS', [
    1 => 'Very Poor',
    2 => 'Poor',
    3 => 'Average',
    4 => 'Good',
    5 => 'Excellent',
]);

// ── Concern Threshold ─────────────────────────────────────
// If a negative response exceeds this percentage, flag it as a concern
define('CONCERN_THRESHOLD', 50);

// ── Pagination ────────────────────────────────────────────
define('ITEMS_PER_PAGE', 10);

// ── Password Requirements ─────────────────────────────────
define('PASSWORD_MIN_LENGTH', 8);

// ── Maintenance Mode ──────────────────────────────────────
define('MAINTENANCE_MODE', false);
