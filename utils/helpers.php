<?php
/**
 * CampusVoice — Helper Functions
 * Formatting, sanitization, date helpers, and utility functions.
 */

/**
 * Escape output for HTML (XSS prevention)
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return '—';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($date, $format = 'M d, Y h:i A') {
    if (empty($date)) return '—';
    return date($format, strtotime($date));
}

/**
 * Get time ago string
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Get countdown text for survey close date
 */
function getCountdown($closeDate) {
    $now = new DateTime();
    $close = new DateTime($closeDate);
    
    if ($now > $close) return 'Closed';
    
    $diff = $now->diff($close);
    
    if ($diff->days > 30) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' left';
    if ($diff->days > 0) return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' left';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' left';
    return 'Closing soon';
}

/**
 * Get survey status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'draft'    => '<span class="badge badge-warning"><span class="badge-dot draft"></span> Draft</span>',
        'active'   => '<span class="badge badge-success"><span class="badge-dot active"></span> Active</span>',
        'closed'   => '<span class="badge badge-error"><span class="badge-dot closed"></span> Closed</span>',
        'archived' => '<span class="badge badge-gray"><span class="badge-dot inactive"></span> Archived</span>',
        'inactive' => '<span class="badge badge-gray"><span class="badge-dot inactive"></span> Inactive</span>',
    ];
    return $badges[$status] ?? '<span class="badge badge-gray">' . e($status) . '</span>';
}

/**
 * Get user status badge
 */
function getUserStatusBadge($status) {
    if ($status === 'active') {
        return '<span class="badge badge-success"><span class="badge-dot active"></span> Active</span>';
    }
    return '<span class="badge badge-gray"><span class="badge-dot inactive"></span> Inactive</span>';
}

/**
 * Get category color and icon
 */
function getCategoryInfo($category) {
    $categories = SURVEY_CATEGORIES;
    return $categories[$category] ?? $categories['General'];
}

/**
 * Get category badge HTML
 */
function getCategoryBadge($category) {
    $info = getCategoryInfo($category);
    return '<span class="survey-card-category" style="background: ' . $info['color'] . '15; color: ' . $info['color'] . ';">' . e($category) . '</span>';
}

/**
 * Get question type label
 */
function getQuestionTypeLabel($type) {
    $types = QUESTION_TYPES;
    return $types[$type] ?? $type;
}

/**
 * Get question type badge
 */
function getQuestionTypeBadge($type) {
    $labels = [
        'multiple_choice' => ['label' => 'Multiple Choice', 'class' => 'badge-primary'],
        'yes_no'          => ['label' => 'Yes / No',        'class' => 'badge-secondary'],
        'rating'          => ['label' => 'Rating Scale',    'class' => 'badge-warning'],
        'short_answer'    => ['label' => 'Short Answer',    'class' => 'badge-info'],
    ];
    $info = $labels[$type] ?? ['label' => $type, 'class' => 'badge-gray'];
    return '<span class="badge ' . $info['class'] . '">' . $info['label'] . '</span>';
}

/**
 * Calculate percentage
 */
function calcPercentage($part, $total, $decimals = 1) {
    if ($total == 0) return 0;
    return round(($part / $total) * 100, $decimals);
}

/**
 * Set a toast message in session
 */
function setToast($title, $message, $type = 'info') {
    $_SESSION['toast_title'] = $title;
    $_SESSION['toast_message'] = $message;
    $_SESSION['toast_type'] = $type;
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Log an activity
 */
function logActivity($userId, $role, $action, $description = '', $targetType = null, $targetId = null) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, role, action, description, target_type, target_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $role, $action, $description, $targetType, $targetId]);
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
}

/**
 * Get user initials from full name
 */
function getInitials($name) {
    $parts = explode(' ', trim($name));
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr(end($parts), 0, 1));
    }
    return $initials;
}

/**
 * Paginate results
 * @return array ['offset', 'limit', 'current_page', 'total_pages', 'total_items']
 */
function paginate($totalItems, $currentPage = 1, $perPage = null) {
    if ($perPage === null) $perPage = ITEMS_PER_PAGE;
    
    $totalPages = max(1, ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'offset'       => $offset,
        'limit'        => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'total_items'  => $totalItems,
    ];
}

/**
 * Render pagination HTML
 */
function renderPagination($pagination, $baseUrl) {
    if ($pagination['total_pages'] <= 1) return '';
    
    $html = '<div class="pagination">';
    
    // Previous
    $prevDisabled = $pagination['current_page'] <= 1 ? 'disabled' : '';
    $prevPage = max(1, $pagination['current_page'] - 1);
    $html .= '<a href="' . $baseUrl . '&page=' . $prevPage . '" class="pagination-btn ' . $prevDisabled . '">← Prev</a>';
    
    // Pages
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        if ($i == $pagination['current_page']) {
            $html .= '<span class="pagination-btn active">' . $i . '</span>';
        } elseif ($i <= 3 || $i > $pagination['total_pages'] - 3 || abs($i - $pagination['current_page']) <= 1) {
            $html .= '<a href="' . $baseUrl . '&page=' . $i . '" class="pagination-btn">' . $i . '</a>';
        } elseif ($i == 4 || $i == $pagination['total_pages'] - 3) {
            $html .= '<span class="pagination-btn" style="cursor:default;">…</span>';
        }
    }
    
    // Next
    $nextDisabled = $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '';
    $nextPage = min($pagination['total_pages'], $pagination['current_page'] + 1);
    $html .= '<a href="' . $baseUrl . '&page=' . $nextPage . '" class="pagination-btn ' . $nextDisabled . '">Next →</a>';
    
    $html .= '</div>';
    return $html;
}
