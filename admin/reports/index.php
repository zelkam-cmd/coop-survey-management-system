<?php
/**
 * CampusVoice — Reports Hub
 */
require_once __DIR__ . '/../../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../../utils/helpers.php';

$pdo = db();
$stmt = $pdo->query("SELECT survey_id, title, category, status FROM surveys ORDER BY created_at DESC");
$surveys = $stmt->fetchAll();

$pageTitle = 'Reports Hub';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></div>
    <span class="breadcrumb-separator">▸</span>
    <div class="breadcrumb-item active">Reports Hub</div>
</div>

<div class="content-header">
    <div>
        <h2 class="content-title">Reports & Export Hub</h2>
        <p class="content-subtitle">Generate printable reports, participation statistics, and CSV exports</p>
    </div>
</div>

<div class="dashboard-grid">
    <div>
        <!-- Available Reports Cards -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-header">
                <h3 class="card-title">Survey Summary & Detailed Reports</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="data-table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Survey Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th style="text-align: right;">Export / Print</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($surveys as $s): ?>
                            <tr>
                                <td class="font-semibold"><?= e($s['title']) ?></td>
                                <td><?= getCategoryBadge($s['category']) ?></td>
                                <td><?= getStatusBadge($s['status']) ?></td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: var(--space-2);">
                                        <a href="<?= BASE_URL ?>/admin/reports/<?= $s['survey_id'] ?>/print" target="_blank" class="btn btn-secondary btn-sm">
                                            🖨️ Print Report
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/reports/export?id=<?= $s['survey_id'] ?>" class="btn btn-primary btn-sm">
                                            📥 CSV Export
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div>
        <!-- Quick Report Types -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Report Types Included</h3>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h4 class="font-semibold text-primary">1. Participation Report</h4>
                    <p class="text-secondary" style="font-size: var(--font-size-sm); margin-bottom: 0;">Overall student submission counts and department participation rates.</p>
                </div>

                <div class="mb-4">
                    <h4 class="font-semibold text-primary">2. Survey Summary Report</h4>
                    <p class="text-secondary" style="font-size: var(--font-size-sm); margin-bottom: 0;">Headline percentages, average rating metrics, and distribution overview.</p>
                </div>

                <div class="mb-4">
                    <h4 class="font-semibold text-primary">3. Detailed Qualitative Report</h4>
                    <p class="text-secondary" style="font-size: var(--font-size-sm); margin-bottom: 0;">Full text responses for Short Answer questions listed verbatim.</p>
                </div>

                <div>
                    <h4 class="font-semibold text-primary">4. Recommendation Report</h4>
                    <p class="text-secondary" style="font-size: var(--font-size-sm); margin-bottom: 0;">Auto-generated action items tied to flagged concerns.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
