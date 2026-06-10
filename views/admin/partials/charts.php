<?php
// Calcola le percentuali dinamiche
$totalM = $stats['total_matches'] ?: 1;
$pctActive = round(($stats['active_matches'] / $totalM) * 100);
$pctFinished = round(($stats['finished_matches'] / $totalM) * 100);
$pctCancelled = round(($stats['cancelled_matches'] / $totalM) * 100);

$totalU = $stats['total_users'] ?: 1;
$pctActiveUsers = round((($stats['total_users'] - $stats['banned_users']) / $totalU) * 100);
$pctBannedUsers = round(($stats['banned_users'] / $totalU) * 100);

$db = \App\Database::getInstance()->getConnection();
$resolvedReports = (int) $db->query("SELECT COUNT(*) FROM reports WHERE status = 'resolved'")->fetchColumn();
$dismissedReports = (int) $db->query("SELECT COUNT(*) FROM reports WHERE status = 'dismissed'")->fetchColumn();
$totalR = ($resolvedReports + $dismissedReports + $stats['pending_reports']) ?: 1;
$pctResolved = round((($resolvedReports + $dismissedReports) / $totalR) * 100);
$pctPending = round(($stats['pending_reports'] / $totalR) * 100);
?>

<div class="row g-4 mb-5">
    <!-- Partite State Chart -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-success me-2"></i>Stato Partite</h5>
            <p class="small text-muted mb-4">Percentuale delle partite suddivise per stato corrente.</p>
            
            <div class="d-flex flex-column gap-3">
                <div>
                    <div class="d-flex justify-between mb-1 align-items-center">
                        <span class="small fw-semibold">Attive</span>
                        <span class="small text-success fw-bold"><?= $pctActive ?>% (<?= $stats['active_matches'] ?>)</span>
                    </div>
                    <div class="progress rounded-pill bg-body-secondary" style="height: 10px;">
                        <div class="progress-bar rounded-pill bg-primary" role="progressbar" style="width: <?= $pctActive ?>%;" aria-valuenow="<?= $pctActive ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-between mb-1 align-items-center">
                        <span class="small fw-semibold">Concluse</span>
                        <span class="small text-muted fw-bold"><?= $pctFinished ?>% (<?= $stats['finished_matches'] ?>)</span>
                    </div>
                    <div class="progress rounded-pill bg-body-secondary" style="height: 10px;">
                        <div class="progress-bar rounded-pill bg-dark" role="progressbar" style="width: <?= $pctFinished ?>%;" aria-valuenow="<?= $pctFinished ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-between mb-1 align-items-center">
                        <span class="small fw-semibold">Annullate</span>
                        <span class="small text-danger fw-bold"><?= $pctCancelled ?>% (<?= $stats['cancelled_matches'] ?>)</span>
                    </div>
                    <div class="progress rounded-pill bg-body-secondary" style="height: 10px;">
                        <div class="progress-bar rounded-pill bg-danger" role="progressbar" style="width: <?= $pctCancelled ?>%;" aria-valuenow="<?= $pctCancelled ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Utenti State Chart -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-person-fill-check text-primary me-2"></i>Stato Account Utenti</h5>
            <p class="small text-muted mb-4">Percentuale di utenti attivi rispetto a quelli sospesi/bannati.</p>

            <div class="d-flex flex-column gap-3">
                <div>
                    <div class="d-flex justify-between mb-1 align-items-center">
                        <span class="small fw-semibold">Utenti Attivi</span>
                        <span class="small text-primary fw-bold"><?= $pctActiveUsers ?>% (<?= ($stats['total_users'] - $stats['banned_users']) ?>)</span>
                    </div>
                    <div class="progress rounded-pill bg-body-secondary" style="height: 10px;">
                        <div class="progress-bar rounded-pill bg-success" role="progressbar" style="width: <?= $pctActiveUsers ?>%;" aria-valuenow="<?= $pctActiveUsers ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-between mb-1 align-items-center">
                        <span class="small fw-semibold">Bannati/Sospesi</span>
                        <span class="small text-danger fw-bold"><?= $pctBannedUsers ?>% (<?= $stats['banned_users'] ?>)</span>
                    </div>
                    <div class="progress rounded-pill bg-body-secondary" style="height: 10px;">
                        <div class="progress-bar rounded-pill bg-danger" role="progressbar" style="width: <?= $pctBannedUsers ?>%;" aria-valuenow="<?= $pctBannedUsers ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segnalazioni Resolution State -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 glass-panel h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-shield-fill-check text-danger me-2"></i>Risoluzione Segnalazioni</h5>
            <p class="small text-muted mb-4">Stato delle segnalazioni ricevute e gestite.</p>

            <div class="d-flex flex-column gap-3">
                <div>
                    <div class="d-flex justify-between mb-1 align-items-center">
                        <span class="small fw-semibold">Gestite (Risolte/Ignorate)</span>
                        <span class="small text-success fw-bold"><?= $pctResolved ?>% (<?= ($resolvedReports + $dismissedReports) ?>)</span>
                    </div>
                    <div class="progress rounded-pill bg-body-secondary" style="height: 10px;">
                        <div class="progress-bar rounded-pill bg-success" role="progressbar" style="width: <?= $pctResolved ?>%;" aria-valuenow="<?= $pctResolved ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-between mb-1 align-items-center">
                        <span class="small fw-semibold">Pendenti (Da Gestire)</span>
                        <span class="small text-warning fw-bold"><?= $pctPending ?>% (<?= $stats['pending_reports'] ?>)</span>
                    </div>
                    <div class="progress rounded-pill bg-body-secondary" style="height: 10px;">
                        <div class="progress-bar rounded-pill bg-warning" role="progressbar" style="width: <?= $pctPending ?>%;" aria-valuenow="<?= $pctPending ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Utility helper per d-flex space-between */
.justify-between {
    justify-content: space-between;
}
</style>
