<?php
/**
 * CampusVoice — Question Management Page
 * Add, edit, delete questions and choices within a survey.
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

// Handle POST actions: add_question, delete_question
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_question') {
        $qText = trim($_POST['question_text'] ?? '');
        $qType = $_POST['question_type'] ?? 'multiple_choice';
        $isRequired = isset($_POST['is_required']) ? 1 : 0;
        $choicesInput = $_POST['choices'] ?? [];

        if (empty($qText)) {
            $error = 'Question text cannot be empty.';
        } else {
            // Get highest order_index
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(order_index), 0) + 1 FROM survey_questions WHERE survey_id = ?");
            $stmt->execute([$surveyId]);
            $nextOrder = $stmt->fetchColumn();

            $stmt = $pdo->prepare("
                INSERT INTO survey_questions (survey_id, question_text, question_type, is_required, order_index)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$surveyId, $qText, $qType, $isRequired, $nextOrder]);
            $newQId = $pdo->lastInsertId();

            // Insert choices for MC or Yes/No
            if ($qType === 'multiple_choice') {
                $choiceOrder = 1;
                foreach ($choicesInput as $cText) {
                    $cText = trim($cText);
                    if (!empty($cText)) {
                        $cStmt = $pdo->prepare("INSERT INTO survey_choices (question_id, choice_text, order_index) VALUES (?, ?, ?)");
                        $cStmt->execute([$newQId, $cText, $choiceOrder++]);
                    }
                }
            } elseif ($qType === 'yes_no') {
                $cStmt = $pdo->prepare("INSERT INTO survey_choices (question_id, choice_text, order_index) VALUES (?, 'Yes', 1), (?, 'No', 2)");
                $cStmt->execute([$newQId, $newQId]);
            }

            logActivity(getCurrentUserId(), ROLE_ADMIN, 'add_question', 'Added question to survey #' . $surveyId, 'survey', $surveyId);
            setToast('Success', 'Question added successfully!', 'success');
            redirect(BASE_URL . '/admin/surveys/' . $surveyId . '/questions');
        }

    } elseif ($action === 'delete_question') {
        $deleteQId = (int)($_POST['question_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM survey_questions WHERE question_id = ? AND survey_id = ?");
        $stmt->execute([$deleteQId, $surveyId]);

        logActivity(getCurrentUserId(), ROLE_ADMIN, 'delete_question', 'Deleted question #' . $deleteQId, 'survey', $surveyId);
        setToast('Success', 'Question deleted successfully!', 'success');
        redirect(BASE_URL . '/admin/surveys/' . $surveyId . '/questions');
    }
}

// Fetch all questions and choices
$stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY order_index ASC");
$stmt->execute([$surveyId]);
$questions = $stmt->fetchAll();

foreach ($questions as &$q) {
    $cStmt = $pdo->prepare("SELECT * FROM survey_choices WHERE question_id = ? ORDER BY order_index ASC");
    $cStmt->execute([$q['question_id']]);
    $q['choices'] = $cStmt->fetchAll();
}
unset($q);

$pageTitle = 'Manage Questions';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/surveys">Surveys</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Question Management</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">Questions for: <?= e($survey['title']) ?></h2>
        <p class="content-subtitle"><?= count($questions) ?> question<?= count($questions) !== 1 ? 's' : '' ?> in this survey</p>
    </div>
    <div class="content-actions">
        <a href="<?= BASE_URL ?>/admin/surveys/<?= $surveyId ?>/edit" class="btn btn-secondary">Edit Survey Details</a>
        <button class="btn btn-primary" onclick="openModal('add-question-modal')">+ Add Question</button>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom: var(--space-5);"><?= e($error) ?></div>
<?php endif; ?>

<!-- Questions List -->
<div class="card">
    <div class="card-body">
        <?php if (empty($questions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div class="empty-state-title">No questions yet</div>
                <p class="empty-state-description">Add your first question to build this survey.</p>
                <button class="btn btn-primary" onclick="openModal('add-question-modal')">+ Add Question</button>
            </div>
        <?php else: ?>
            <div class="question-list">
                <?php foreach ($questions as $idx => $q): ?>
                <div class="question-list-item">
                    <div class="question-list-content">
                        <div class="question-list-meta mb-1">
                            <span class="font-semibold text-primary">Q<?= $idx + 1 ?></span>
                            <?= getQuestionTypeBadge($q['question_type']) ?>
                            <?php if ($q['is_required']): ?>
                                <span class="badge badge-error">Required</span>
                            <?php endif; ?>
                        </div>
                        <div class="question-list-text"><?= e($q['question_text']) ?></div>

                        <?php if (!empty($q['choices'])): ?>
                            <div style="margin-top: var(--space-2); display: flex; flex-wrap: wrap; gap: var(--space-2);">
                                <?php foreach ($q['choices'] as $choice): ?>
                                    <span class="tag"><?= e($choice['choice_text']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="question-list-actions">
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this question?');">
                            <?php csrfField(); ?>
                            <input type="hidden" name="action" value="delete_question">
                            <input type="hidden" name="question_id" value="<?= $q['question_id'] ?>">
                            <button type="submit" class="btn btn-ghost btn-sm text-error">Delete</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal-overlay" id="add-question-modal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add New Question</h3>
            <button class="modal-close" data-modal-close="add-question-modal">✕</button>
        </div>
        <form method="POST" id="add-q-form">
            <?php csrfField(); ?>
            <input type="hidden" name="action" value="add_question">

            <div class="modal-body">
                <div class="form-group">
                    <label for="q_type" class="form-label">Question Type <span class="required">*</span></label>
                    <select id="q_type" name="question_type" class="form-select" onchange="toggleChoicesContainer(this.value)" required>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="yes_no">Yes / No</option>
                        <option value="rating">Rating Scale (1 - 5 Stars)</option>
                        <option value="short_answer">Short Answer (Free Text)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="q_text" class="form-label">Question Text <span class="required">*</span></label>
                    <textarea id="q_text" name="question_text" class="form-textarea" placeholder="Enter your question here..." required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="is_required" value="1" checked>
                        <span class="form-check-label">Is this question required?</span>
                    </label>
                </div>

                <!-- MC Choices Inputs -->
                <div id="choices-container" class="form-group">
                    <label class="form-label">Answer Choices <span class="required">*</span></label>
                    <div id="choices-list">
                        <input type="text" name="choices[]" class="form-input mb-2" placeholder="Option 1" required>
                        <input type="text" name="choices[]" class="form-input mb-2" placeholder="Option 2" required>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addChoiceInput()">+ Add Another Choice</button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="add-question-modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Question</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleChoicesContainer(type) {
    const container = document.getElementById('choices-container');
    if (type === 'multiple_choice') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

function addChoiceInput() {
    const list = document.getElementById('choices-list');
    const count = list.querySelectorAll('input').length + 1;
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'choices[]';
    input.className = 'form-input mb-2';
    input.placeholder = 'Option ' + count;
    list.appendChild(input);
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
