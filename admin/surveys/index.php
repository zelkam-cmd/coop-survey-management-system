<?php
/**
 * CampusVoice — Survey Management List
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';

$pdo = db();

// Filter & Search
$statusFilter = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$search = trim($_GET['search'] ?? '');

$query = "
    SELECT s.*, 
           a.full_name as creator_name,
           (SELECT COUNT(*) FROM survey_questions sq WHERE sq.survey_id = s.survey_id) as question_count,
           (SELECT COUNT(DISTINCT student_id) FROM responses r WHERE r.survey_id = s.survey_id) as response_count
    FROM surveys s
    LEFT JOIN administrators a ON s.created_by = a.admin_id
    WHERE 1=1
";
$params = [];

if (!empty($statusFilter)) {
    $query .= " AND s.status = ?";
    $params[] = $statusFilter;
}

if (!empty($categoryFilter)) {
    $query .= " AND s.category = ?";
    $params[] = $categoryFilter;
}

if (!empty($search)) {
    $query .= " AND (s.title LIKE ? OR s.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$surveys = $stmt->fetchAll();

$pageTitle = 'Survey Management';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Survey Management</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">Survey Management</h2>
        <p class="content-subtitle">Create, edit, schedule, publish, and deactivate surveys</p>
    </div>
    <div class="content-actions">
        <a href="<?= BASE_URL ?>/admin/surveys/create" class="btn btn-primary">
            + Create New Survey
        </a>
    </div>
</div>

<!-- Table Container -->
<div class="data-table-wrapper">
    <div class="data-table-header">
        <!-- Search -->
        <form method="GET" class="search-bar" style="min-width: 280px;">
            <div class="search-bar-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <input type="text" name="search" placeholder="Search surveys..." value="<?= e($search) ?>" onchange="this.form.submit()">
        </form>

        <!-- Filters -->
        <div style="display: flex; gap: var(--space-3);">
            <select name="category" class="form-select" style="width: auto;" onchange="window.location.href='<?= BASE_URL ?>/admin/surveys?status=<?= e($statusFilter) ?>&search=<?= e($search) ?>&category=' + this.value">
                <option value="">All Categories</option>
                <?php foreach (SURVEY_CATEGORIES as $cat => $info): ?>
                    <option value="<?= e($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="status" class="form-select" style="width: auto;" onchange="window.location.href='<?= BASE_URL ?>/admin/surveys?category=<?= e($categoryFilter) ?>&search=<?= e($search) ?>&status=' + this.value">
                <option value="">All Statuses</option>
                <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="closed" <?= $statusFilter === 'closed' ? 'selected' : '' ?>>Closed</option>
                <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>
    </div>

    <div class="data-table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title & Description</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Questions</th>
                    <th>Responses</th>
                    <th>Schedule</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($surveys)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: var(--space-8);">
                            <div class="empty-state" style="padding: 0;">
                                <div class="empty-state-title">No surveys found</div>
                                <p class="empty-state-description">Try adjusting your search or filters.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($surveys as $s): ?>
                    <tr>
                        <td>
                            <div class="font-semibold"><?= e($s['title']) ?></div>
                            <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); max-width: 320px;" class="truncate">
                                <?= e($s['description']) ?>
                            </div>
                        </td>
                        <td><?= getCategoryBadge($s['category']) ?></td>
                        <td><?= getStatusBadge($s['status']) ?></td>
                        <td><span class="badge badge-gray"><?= $s['question_count'] ?> Qs</span></td>
                        <td><span class="font-semibold"><?= $s['response_count'] ?></span></td>
                        <td style="font-size: var(--font-size-xs); color: var(--color-text-secondary);">
                            <div>Open: <?= formatDate($s['open_date']) ?></div>
                            <div>Close: <?= formatDate($s['close_date']) ?></div>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: var(--space-1);">
                                <a href="<?= BASE_URL ?>/admin/surveys/<?= $s['survey_id'] ?>/questions" class="btn btn-secondary btn-sm" data-tooltip="Manage Questions">
                                    Questions
                                </a>
                                <a href="<?= BASE_URL ?>/admin/surveys/<?= $s['survey_id'] ?>/edit" class="btn btn-secondary btn-sm" data-tooltip="Edit Details">
                                    Edit
                                </a>
                                <?php if ($s['status'] === 'active'): ?>
                                <button class="btn btn-danger btn-sm" onclick="confirmDeactivate(<?= $s['survey_id'] ?>, '<?= e(addslashes($s['title'])) ?>')">
                                    Deactivate
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDeactivate(id, title) {
    confirmAction('Are you sure you want to deactivate "' + title + '"? It will immediately stop accepting new student responses.', function() {
        window.location.href = '<?= BASE_URL ?>/admin/surveys/' + id + '/deactivate';
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
