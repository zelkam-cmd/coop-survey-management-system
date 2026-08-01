<?php
/**
 * CampusVoice — Add Student Account
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../utils/helpers.php';

$error = '';
$studentNumber = '';
$fullName = '';
$email = '';
$department = '';
$yearLevel = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $studentNumber = trim($_POST['student_number'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $yearLevel = trim($_POST['year_level'] ?? '');

    if (empty($studentNumber) || empty($fullName)) {
        $error = 'Student ID Number and Full Name are required.';
    } else {
        $pdo = db();
        
        // Check uniqueness
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_number = ?");
        $stmt->execute([$studentNumber]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'A student with this ID Number already exists.';
        } else {
            // Default password is set to Student ID Number
            $defaultHash = password_hash($studentNumber, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("
                INSERT INTO students (student_number, full_name, email, password_hash, must_change_password, department, year_level, status, created_at)
                VALUES (?, ?, ?, ?, 1, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$studentNumber, $fullName, $email, $defaultHash, $department, $yearLevel]);
            $newId = $pdo->lastInsertId();

            logActivity(getCurrentUserId(), ROLE_ADMIN, 'add_student', 'Created student account: ' . $studentNumber, 'student', $newId);
            setToast('Success', "Student {$fullName} added! Default password is set to their Student ID.", 'success');

            redirect(BASE_URL . '/admin/students');
        }
    }
}

$pageTitle = 'Add New Student';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/students">Students</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Add Student</div>
</div>

<div style="max-width: 600px;">
    <div class="card glass-card">
        <div class="card-header">
            <h2 class="card-title">Add New Student Account</h2>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: var(--space-5);"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="add-student-form" novalidate>
                <?php csrfField(); ?>

                <div class="form-group">
                    <label for="student_number" class="form-label">Student ID Number <span class="required">*</span></label>
                    <input type="text" id="student_number" name="student_number" class="form-input" placeholder="e.g. STU-2024-006" value="<?= e($studentNumber) ?>" required autofocus>
                    <div class="form-hint">Used as login username. Initial default password will equal this ID.</div>
                </div>

                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" class="form-input" placeholder="First Name Last Name" value="<?= e($fullName) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="student@example.edu" value="<?= e($email) ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="department" class="form-label">Department / Course</label>
                        <input type="text" id="department" name="department" class="form-input" placeholder="e.g. Information Technology" value="<?= e($department) ?>">
                    </div>

                    <div class="form-group">
                        <label for="year_level" class="form-label">Year Level</label>
                        <select id="year_level" name="year_level" class="form-select">
                            <option value="">Select Year Level</option>
                            <option value="1st Year" <?= $yearLevel === '1st Year' ? 'selected' : '' ?>>1st Year</option>
                            <option value="2nd Year" <?= $yearLevel === '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
                            <option value="3rd Year" <?= $yearLevel === '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
                            <option value="4th Year" <?= $yearLevel === '4th Year' ? 'selected' : '' ?>>4th Year</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
                    <button type="submit" class="btn btn-primary">Create Student Account</button>
                    <a href="<?= BASE_URL ?>/admin/students" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
