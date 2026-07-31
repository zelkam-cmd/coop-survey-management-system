<?php
/**
 * CampusVoice — Student Profile / Edit Profile Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(ROLE_STUDENT);
checkPasswordChange();
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../utils/helpers.php';

$pdo = db();
$studentId = getCurrentUserId();
$error = '';
$success = '';

// Handle update profile POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $email = trim($_POST['email'] ?? '');
    $contactInfo = trim($_POST['contact_info'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $yearLevel = trim($_POST['year_level'] ?? '');

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("UPDATE students SET email = ?, contact_info = ?, department = ?, year_level = ? WHERE student_id = ?");
        $stmt->execute([$email, $contactInfo, $department, $yearLevel, $studentId]);

        $_SESSION['email'] = $email;
        logActivity($studentId, ROLE_STUDENT, 'update_profile', 'Updated personal profile information');
        setToast('Success', 'Profile updated successfully!', 'success');
        redirect(BASE_URL . '/student/profile');
    }
}

// Fetch student profile details
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/student/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">My Profile</div>
</div>

<div class="dashboard-grid">
    <div>
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar"><?= getInitials($student['full_name']) ?></div>
                <div class="profile-info">
                    <h2><?= e($student['full_name']) ?></h2>
                    <p>Student ID: <strong><?= e($student['student_number']) ?></strong></p>
                </div>
            </div>
            <div class="profile-body">
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Full Name</div>
                    <div class="profile-detail-value"><?= e($student['full_name']) ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Student ID Number</div>
                    <div class="profile-detail-value"><?= e($student['student_number']) ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Email Address</div>
                    <div class="profile-detail-value"><?= e($student['email'] ?: 'Not set') ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Department / Course</div>
                    <div class="profile-detail-value"><?= e($student['department'] ?: 'Not set') ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Year Level</div>
                    <div class="profile-detail-value"><?= e($student['year_level'] ?: 'Not set') ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Contact / Notes</div>
                    <div class="profile-detail-value"><?= e($student['contact_info'] ?: 'None') ?></div>
                </div>
                <div class="profile-detail-row">
                    <div class="profile-detail-label">Account Status</div>
                    <div class="profile-detail-value"><?= getUserStatusBadge($student['status']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Update Information</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: var(--space-4);"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" id="profile-form" novalidate>
                    <?php csrfField(); ?>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" value="<?= e($student['email']) ?>" placeholder="student@example.edu">
                    </div>

                    <div class="form-group">
                        <label for="department" class="form-label">Department / College</label>
                        <input type="text" id="department" name="department" class="form-input" value="<?= e($student['department']) ?>" placeholder="e.g. Computer Science">
                    </div>

                    <div class="form-group">
                        <label for="year_level" class="form-label">Year Level</label>
                        <select id="year_level" name="year_level" class="form-select">
                            <option value="">Select Year Level</option>
                            <option value="1st Year" <?= $student['year_level'] === '1st Year' ? 'selected' : '' ?>>1st Year</option>
                            <option value="2nd Year" <?= $student['year_level'] === '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
                            <option value="3rd Year" <?= $student['year_level'] === '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
                            <option value="4th Year" <?= $student['year_level'] === '4th Year' ? 'selected' : '' ?>>4th Year</option>
                            <option value="Postgraduate" <?= $student['year_level'] === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contact_info" class="form-label">Contact / Additional Info</label>
                        <textarea id="contact_info" name="contact_info" class="form-textarea" placeholder="Phone number, bio, or contact details..."><?= e($student['contact_info']) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-full">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
