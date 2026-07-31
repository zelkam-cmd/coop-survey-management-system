<?php
/**
 * CampusVoice — Edit Survey Details
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../utils/helpers.php';

$surveyId = (int)($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM surveys WHERE survey_id = ?");
$stmt->execute([$surveyId]);
$survey = $stmt->fetch();

if (!$survey) {
    setToast('Error', 'Survey not found.', 'error');
    redirect(BASE_URL . '/admin/surveys');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'General';
    $openDate = $_POST['open_date'] ?? '';
    $closeDate = $_POST['close_date'] ?? '';
    $status = $_POST['status'] ?? 'draft';

    if (empty($title)) {
        $error = 'Survey title is required.';
    } elseif (empty($openDate) || empty($closeDate)) {
        $error = 'Open and close dates are required.';
    } elseif (strtotime($closeDate) <= strtotime($openDate)) {
        $error = 'Close date must be after the open date.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE surveys 
            SET title = ?, description = ?, category = ?, open_date = ?, close_date = ?, status = ?
            WHERE survey_id = ?
        ");
        $stmt->execute([$title, $description, $category, $openDate, $closeDate, $status, $surveyId]);

        logActivity(getCurrentUserId(), ROLE_ADMIN, 'edit_survey', 'Updated survey details: ' . $title, 'survey', $surveyId);
        setToast('Success', 'Survey details updated successfully!', 'success');

        redirect(BASE_URL . '/admin/surveys');
    }
} else {
    $title = $survey['title'];
    $description = $survey['description'];
    $category = $survey['category'];
    $openDate = date('Y-m-d\TH:i', strtotime($survey['open_date']));
    $closeDate = date('Y-m-d\TH:i', strtotime($survey['close_date']));
    $status = $survey['status'];
}

$pageTitle = 'Edit Survey';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/surveys">Surveys</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Edit Survey</div>
</div>

<div style="max-width: 680px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Edit Survey: <?= e($survey['title']) ?></h2>
            <a href="<?= BASE_URL ?>/admin/surveys/<?= $surveyId ?>/questions" class="btn btn-secondary btn-sm">Manage Questions →</a>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: var(--space-5);"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="edit-survey-form" novalidate>
                <?php csrfField(); ?>

                <div class="form-group">
                    <label for="title" class="form-label">Survey Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-input" value="<?= e($title) ?>" required>
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">Category <span class="required">*</span></label>
                    <select id="category" name="category" class="form-select" required>
                        <?php foreach (SURVEY_CATEGORIES as $cat => $info): ?>
                            <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description / Instructions</label>
                    <textarea id="description" name="description" class="form-textarea"><?= e($description) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="open_date" class="form-label">Open Date & Time <span class="required">*</span></label>
                        <input type="datetime-local" id="open_date" name="open_date" class="form-input" value="<?= e($openDate) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="close_date" class="form-label">Close Date & Time <span class="required">*</span></label>
                        <input type="datetime-local" id="close_date" name="close_date" class="form-input" value="<?= e($closeDate) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status <span class="required">*</span></label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>

                <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
                    <button type="submit" class="btn btn-primary">
                        Save Changes
                    </button>
                    <a href="<?= BASE_URL ?>/admin/surveys" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
