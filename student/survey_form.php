<?php
/**
 * CampusVoice — Survey Form Page
 * Renders all questions dynamically by type (Multiple Choice, Yes/No, Rating Scale, Short Answer) with progress bar and JS validation.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(ROLE_STUDENT);
checkPasswordChange();
require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../utils/survey_engine.php';
require_once __DIR__ . '/../includes/csrf.php';

$surveyId = (int)($_GET['id'] ?? 0);
$studentId = getCurrentUserId();

$availability = isSurveyAvailableForStudent($surveyId, $studentId);

if (!$availability['available']) {
    if ($availability['reason'] === 'already_answered') {
        setToast('Notice', 'You have already answered this survey.', 'info');
        redirect(BASE_URL . '/student/dashboard');
    } elseif ($availability['reason'] === 'closed' || $availability['reason'] === 'inactive') {
        redirect(BASE_URL . '/student/surveys/' . $surveyId . '/closed');
    } else {
        setToast('Error', 'This survey is currently unavailable.', 'error');
        redirect(BASE_URL . '/student/surveys');
    }
}

$survey = $availability['survey'];

$pdo = db();
// Fetch questions and choices
$qStmt = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY order_index ASC, question_id ASC");
$qStmt->execute([$surveyId]);
$questions = $qStmt->fetchAll();

foreach ($questions as &$q) {
    if ($q['question_type'] === 'multiple_choice' || $q['question_type'] === 'yes_no') {
        $cStmt = $pdo->prepare("SELECT * FROM survey_choices WHERE question_id = ? ORDER BY order_index ASC, choice_id ASC");
        $cStmt->execute([$q['question_id']]);
        $q['choices'] = $cStmt->fetchAll();
    }
}
unset($q);

$pageTitle = $survey['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/student/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/student/surveys">Available Surveys</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active"><?= e($survey['title']) ?></div>
</div>

<div class="survey-form-container">
    <div class="survey-form-header">
        <div style="margin-bottom: var(--space-2);"><?= getCategoryBadge($survey['category'] ?? null) ?></div>
        <h2 class="survey-form-title"><?= e($survey['title']) ?></h2>
        <?php if (!empty($survey['description'])): ?>
            <p class="survey-form-description"><?= e($survey['description']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Progress Indicator -->
    <div class="survey-progress">
        <div class="survey-progress-header">
            <span class="survey-progress-text">Survey Progress</span>
            <span class="survey-progress-count" id="progress-text">0 of <?= count($questions) ?> answered</span>
        </div>
        <div class="progress-bar">
            <div class="progress-bar-fill" id="progress-fill" style="width: 0%;"></div>
        </div>
    </div>

    <!-- Form -->
    <form action="<?= BASE_URL ?>/student/submit_survey.php" method="POST" id="survey-form" novalidate>
        <?php csrfField(); ?>
        <input type="hidden" name="survey_id" value="<?= $surveyId ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="question-card" 
                 data-question-id="<?= $q['question_id'] ?>" 
                 data-question-type="<?= $q['question_type'] ?>"
                 data-required="<?= $q['is_required'] ?>">
                
                <div class="question-number">Question <?= $index + 1 ?> of <?= count($questions) ?></div>
                <div class="question-text">
                    <?= e($q['question_text']) ?>
                    <?php if ($q['is_required']): ?>
                        <span class="question-required">*</span>
                    <?php endif; ?>
                </div>

                <!-- Render according to question_type -->
                <?php if ($q['question_type'] === 'multiple_choice' || $q['question_type'] === 'yes_no'): ?>
                    <div class="question-options">
                        <?php foreach ($q['choices'] as $choice): ?>
                            <label class="question-option">
                                <input type="radio" 
                                       name="answer[<?= $q['question_id'] ?>]" 
                                       value="<?= $choice['choice_id'] ?>" 
                                       <?= $q['is_required'] ? 'required' : '' ?>
                                       onchange="updateProgress(); this.closest('.question-card').querySelectorAll('.question-option').forEach(o => o.classList.remove('selected')); this.closest('.question-option').classList.add('selected');">
                                <span><?= e($choice['choice_text']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                <?php elseif ($q['question_type'] === 'rating'): ?>
                    <input type="hidden" name="answer[<?= $q['question_id'] ?>]" id="rating-input-<?= $q['question_id'] ?>" data-type="rating" value="" <?= $q['is_required'] ? 'required' : '' ?>>
                    <div class="rating-control" id="rating-control-<?= $q['question_id'] ?>">
                        <div class="rating-stars-wrapper" onmouseleave="resetRatingPreview(<?= $q['question_id'] ?>)">
                            <?php for ($r = 1; $r <= 5; $r++): ?>
                                <svg class="rating-star" data-value="<?= $r ?>" 
                                     onclick="setRating(<?= $q['question_id'] ?>, <?= $r ?>)" 
                                     onmouseenter="previewRating(<?= $q['question_id'] ?>, <?= $r ?>)" 
                                     viewBox="0 0 24 24" fill="currentColor">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-label" id="rating-label-<?= $q['question_id'] ?>">Select a rating</span>
                    </div>

                <?php elseif ($q['question_type'] === 'short_answer'): ?>
                    <textarea name="answer[<?= $q['question_id'] ?>]" 
                              class="form-textarea" 
                              rows="4" 
                              placeholder="Type your response here..." 
                              <?= $q['is_required'] ? 'required' : '' ?>
                              oninput="updateProgress()"></textarea>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="survey-form-footer">
            <a href="<?= BASE_URL ?>/student/surveys" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg" id="submit-survey-btn">
                Submit Survey
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </div>
    </form>
</div>

<script>
    const ratingLabels = {
        1: '1 - Very Poor',
        2: '2 - Poor',
        3: '3 - Average',
        4: '4 - Good',
        5: '5 - Excellent'
    };

    function setRating(qId, val) {
        document.getElementById('rating-input-' + qId).value = val;
        const container = document.getElementById('rating-control-' + qId);
        const stars = container.querySelectorAll('.rating-star');
        stars.forEach((star, idx) => {
            if (idx < val) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
        document.getElementById('rating-label-' + qId).textContent = ratingLabels[val];
        updateProgress();
    }

    function previewRating(qId, val) {
        const container = document.getElementById('rating-control-' + qId);
        const stars = container.querySelectorAll('.rating-star');
        stars.forEach((star, idx) => {
            if (idx < val) {
                star.classList.add('hovered');
            } else {
                star.classList.remove('hovered');
            }
        });
    }

    function resetRatingPreview(qId) {
        const container = document.getElementById('rating-control-' + qId);
        const stars = container.querySelectorAll('.rating-star');
        stars.forEach(star => star.classList.remove('hovered'));
    }

    function updateProgress() {
        const total = <?= count($questions) ?>;
        let answered = 0;

        document.querySelectorAll('.question-card').forEach(card => {
            const type = card.dataset.questionType;
            const qId = card.dataset.questionId;
            let isAnswered = false;

            if (type === 'multiple_choice' || type === 'yes_no') {
                isAnswered = card.querySelector('input[type="radio"]:checked') !== null;
            } else if (type === 'rating') {
                const val = document.getElementById('rating-input-' + qId).value;
                isAnswered = val !== '' && parseInt(val) >= 1;
            } else if (type === 'short_answer') {
                const text = card.querySelector('textarea').value.trim();
                isAnswered = text.length > 0;
            }

            if (isAnswered) answered++;
        });

        const pct = total > 0 ? Math.round((answered / total) * 100) : 0;
        document.getElementById('progress-fill').style.width = pct + '%';
        document.getElementById('progress-text').textContent = answered + ' of ' + total + ' answered';
    }

    document.getElementById('survey-form').addEventListener('submit', function(e) {
        if (!validateSurveyForm('survey-form')) {
            e.preventDefault();
            return false;
        }
        disableSubmitButton(document.getElementById('submit-survey-btn'));
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
