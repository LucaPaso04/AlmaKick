<div class="row g-4 mt-2 mb-2">
    <div class="col-12">
        <div class="card shadow-sm border rounded-4 p-4">
            <h5 class="fw-bold mb-4"><i class="bi bi-clock-history text-primary me-2"></i>Storico Partite Giocate</h5>
            <?php if(empty($matchHistory)): ?>
                <div class="text-center py-4 bg-body-tertiary rounded-3">
                    <i class="bi bi-calendar-x fs-2 text-muted mb-2"></i>
                    <p class="text-muted mb-0">Non ci sono partite giocate in archivio.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Ora</th>
                                <th>Campetto</th>
                                <th>Formato</th>
                                <th>Risultato</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($matchHistory as $reg): ?>
                                <?php if($reg['match']): ?>
                                    <tr class="cursor-pointer" onclick="window.location.href='<?= url('/matches/' . $reg['match']['id']) ?>?from=profile';">
                                        <td><strong><?= date('d/m/Y', strtotime($reg['match']['date'])) ?></strong></td>
                                        <td><?= date('H:i', strtotime($reg['match']['time'])) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                                <span class="text-truncate location-truncate"><?= e($reg['match']['location']) ?></span>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= e($reg['match']['format']) ?></span></td>
                                        <td>
                                            <?php if($reg['match']['result_home'] !== null && $reg['match']['result_away'] !== null): ?>
                                                <span class="badge bg-secondary fs-6 shadow-sm"><?= e($reg['match']['result_home']) ?> - <?= e($reg['match']['result_away']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
