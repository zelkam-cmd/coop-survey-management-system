<?php
/**
 * CampusVoice — Student Management List
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';

$pdo = db();
$search = trim($_GET['search'] ?? '');
$deptFilter = $_GET['department'] ?? '';

$query = "
    SELECT s.*, 
           (SELECT COUNT(DISTINCT survey_id) FROM responses r WHERE r.student_id = s.student_id) as answered_count
    FROM students s
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (s.student_number LIKE ? OR s.full_name LIKE ? OR s.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($deptFilter)) {
    $query .= " AND s.department = ?";
    $params[] = $deptFilter;
}

$query .= " ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Get unique departments for filter dropdown
$deptStmt = $pdo->query("SELECT DISTINCT department FROM students WHERE department IS NOT NULL AND department != ''");
$departments = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Student Management';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Student Management</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">Student Management</h2>
        <p class="content-subtitle">Manage student accounts, view submission history, and reset passwords</p>
    </div>
    <div class="content-actions">
        <a href="<?= BASE_URL ?>/admin/students/add" class="btn btn-primary">+ Add New Student</a>
    </div>
</div>

<div class="data-table-wrapper">
    <div class="data-table-header">
        <form method="GET" class="search-bar" style="min-width: 280px;">
            <div class="search-bar-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <input type="text" name="search" placeholder="Search ID, name, or email..." value="<?= e($search) ?>" onchange="this.form.submit()">
        </form>

        <div style="display: flex; gap: var(--space-3);">
            <select name="department" class="form-select" style="width: auto;" onchange="window.location.href='<?= BASE_URL ?>/admin/students?search=<?= e($search) ?>&department=' + this.value">
                <option value="">All Departments</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= e($dept) ?>" <?= $deptFilter === $dept ? 'selected' : '' ?>><?= e($dept) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="data-table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Department</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th>Surveys Taken</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: var(--space-8);">
                            <div class="empty-state" style="padding: 0;">
                                <div class="empty-state-title">No students found</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $stu): ?>
                    <tr>
                        <td><span class="font-semibold"><?= e($stu['student_number']) ?></span></td>
                        <td>
                            <div class="font-semibold"><?= e($stu['full_name']) ?></div>
                            <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);"><?= e($stu['email'] ?: 'No email') ?></div>
                        </td>
                        <td><?= e($stu['department'] ?: '—') ?></td>
                        <td><?= e($stu['year_level'] ?: '—') ?></td>
                        <td><?= getUserStatusBadge($stu['status']) ?></td>
                        <td><span class="badge badge-primary"><?= $stu['answered_count'] ?> completed</span></td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: var(--space-1);">
                                <a href="<?= BASE_URL ?>/admin/students/<?= $stu['student_id'] ?>" class="btn btn-secondary btn-sm">History</a>
                                <a href="<?= BASE_URL ?>/admin/students/reset_password?id=<?= $stu['student_id'] ?>" class="btn btn-ghost btn-sm text-warning" onclick="return confirm('Reset password for <?= e(addslashes($stu['full_name'])) ?>?');">Reset PW</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
