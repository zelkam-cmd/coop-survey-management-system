<?php
/**
 * CampusVoice — Reset Student Password Handler
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';

$studentId = (int)($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if ($student) {
    // Reset password hash to student_number and set must_change_password = 1
    $newHash = password_hash($student['student_number'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE students SET password_hash = ?, must_change_password = 1 WHERE student_id = ?");
    $stmt->execute([$newHash, $studentId]);

    logActivity(getCurrentUserId(), ROLE_ADMIN, 'reset_student_password', 'Reset password for student ' . $student['student_number'], 'student', $studentId);
    setToast('Success', 'Password reset successfully! Default password is set to Student ID: ' . $student['student_number'], 'success');
} else {
    setToast('Error', 'Student account not found.', 'error');
}

redirect(BASE_URL . '/admin/students');
