<div id="reports-section-card" class="card shadow border-0 rounded-4 overflow-hidden mb-5">
    <div class="card-header bg-body-tertiary border-0 p-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-flag-fill me-2 text-danger"></i>Gestione Segnalazioni</h5>
        <?php if($stats['pending_reports'] > 0): ?>
            <span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($stats['pending_reports']) ?> Da Gestire</span>
        <?php endif; ?>
    </div>

    <?php // Filters & Search ?>
    <div class="card-body border-bottom bg-body-tertiary p-3">
        <form method="GET" action="<?= url('/admin') ?>#reports-section" class="row g-2">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-search text-body"></i></span>
                    <input type="text" name="search_report" class="form-control" placeholder="Cerca in segnalazioni..."
                        value="<?= e($searchReport) ?>">
                </div>
            </div>

            <div class="col-md-4">
                <select name="status_report" class="form-select">
                    <option value="">Tutti gli stati</option>
                    <option value="pending" <?= $statusReport === 'pending' ? 'selected' : '' ?>>Pendenti</option>
                    <option value="resolved" <?= $statusReport === 'resolved' ? 'selected' : '' ?>>Risolte</option>
                    <option value="dismissed" <?= $statusReport === 'dismissed' ? 'selected' : '' ?>>Ignorate</option>
                </select>
            </div>

            <?php // Preserve other filters ?>
            <input type="hidden" name="search" value="<?= e($search) ?>">
            <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
            <input type="hidden" name="role" value="<?= e($roleFilter) ?>">
            <input type="hidden" name="sort" value="<?= e($sortBy) ?>">
            <input type="hidden" name="order" value="<?= e($sortOrder) ?>">
            <input type="hidden" name="search_match" value="<?= e($searchMatch) ?>">
            <input type="hidden" name="status_match" value="<?= e($statusMatch) ?>">
            <input type="hidden" name="date_match" value="<?= e($dateMatch) ?>">
            <input type="hidden" name="format_match" value="<?= e($formatMatch) ?>">
        </form>
        <?php if ($searchReport || $statusReport !== 'pending'): ?>
            <div class="mt-2">
                <a href="<?= url('/admin') ?>#reports-section" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Resetta filtri segnalazioni
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Data</th>
                    <th>Segnalatore</th>
                    <th>Segnalato</th>
                    <th>Motivo</th>
                    <th class="w-30">Descrizione</th>
                    <th class="text-center">Stato</th>
                    <th class="text-end pe-4">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reports)): ?>
                    <?php foreach ($reports as $r): ?>
                        <tr>
                            <td class="ps-4 text-muted small"><?= $r['created_at']->format('d/m/Y H:i') ?></td>
                            <td>
                                <?php if($r['reporter']): ?>
                                    <div class="fw-bold">
                                        <a href="<?= url('/profile?username=' . urlencode($r['reporter']['id'])) ?>" class="text-decoration-none text-reset">
                                            <?= e($r['reporter']['name']) ?>
                                        </a>
                                    </div>
                                    <div class="small text-muted"><?= e($r['reporter']['email']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted">Eliminato</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($r['reported']): ?>
                                    <div class="fw-bold">
                                        <a href="<?= url('/profile?username=' . urlencode($r['reported']['id'])) ?>" class="text-decoration-none text-reset">
                                            <?= e($r['reported']['name']) ?>
                                        </a>
                                    </div>
                                    <div class="small text-muted">
                                        <?= e($r['reported']['email']) ?>
                                        <?php if($r['reported']['is_banned']): ?>
                                            <span class="badge bg-danger ms-1 text-white">BANNATO</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Eliminato</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-3 px-2 py-1"><?= e($r['reason']) ?></span>
                                <?php if (!empty($r['match_id'])): ?>
                                    <div class="mt-1">
                                        <a href="<?= url('/matches/' . $r['match_id']) ?>?from=admin" class="badge bg-info-subtle text-info-emphasis text-decoration-none rounded-3 px-2 py-1" onclick="event.stopPropagation();">
                                            <i class="bi bi-calendar-event me-1"></i>Partita #<?= e($r['match_id']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-wrap admin-reports-table-desc" title="<?= e($r['description']) ?>">
                                    <?= e($r['description']) ?>
                                </div>
                                <?php if($r['admin_notes']): ?>
                                    <div class="mt-1 small border-top pt-1 text-info">
                                        <i class="bi bi-chat-right-text-fill me-1"></i><strong>Nota Admin:</strong> <?= e($r['admin_notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($r['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock-fill me-1"></i>Pendente</span>
                                <?php elseif($r['status'] === 'resolved'): ?>
                                    <span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Risolta</span>
                                <?php elseif($r['status'] === 'dismissed'): ?>
                                    <span class="badge bg-secondary text-white px-2 py-1"><i class="bi bi-eye-slash-fill me-1"></i>Ignorata</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4" onclick="event.stopPropagation();">
                                <?php if($r['status'] === 'pending'): ?>
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-sm btn-success rounded-pill fw-bold" 
                                            data-bs-toggle="modal" data-bs-target="#resolveReportModal<?= $r['id'] ?>">
                                            <i class="bi bi-check-lg me-1"></i>Risolvi
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold" 
                                            data-bs-toggle="modal" data-bs-target="#dismissReportModal<?= $r['id'] ?>">
                                            <i class="bi bi-slash-circle me-1"></i>Ignora
                                        </button>

                                        <?php if($r['reported'] && !$r['reported']['is_banned'] && $r['reported']['id'] !== $_SESSION['user']['username']): ?>
                                            <form action="<?= url('/admin/ban') ?>" method="POST" class="d-inline-block"
                                                onsubmit="return confirm('Bannare l\'utente segnalato? Questa operazione gli impedirà l\'accesso.');">
                                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="user_id" value="<?= e($r['reported']['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold">
                                                    <i class="bi bi-ban me-1"></i>Banna
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>

                                    <?php // Resolve modal ?>
                                    <div class="modal fade text-start" id="resolveReportModal<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow" style="background-color: var(--bs-body-bg); color: var(--bs-body-color);">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-success"><i class="bi bi-check-circle-fill me-2"></i>Risolvi Segnalazione #<?= $r['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                                </div>
                                                <form action="<?= url('/admin/reports/' . $r['id'] . '/resolve') ?>" method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                    <div class="modal-body py-3">
                                                        <p class="text-muted small">
                                                            Contrassegna questa segnalazione come **Risolta**. Aggiungi una nota interna per documentare l'azione intrapresa (es. "Utente ammonito" o "Nessuna violazione riscontrata").
                                                        </p>
                                                        <div class="mb-0">
                                                            <label for="admin_notes<?= $r['id'] ?>" class="form-label fw-semibold">Note dell'Amministratore (opzionale)</label>
                                                            <textarea name="admin_notes" id="admin_notes<?= $r['id'] ?>" rows="3" class="form-control rounded-3 bg-body-secondary border-secondary-subtle text-body" 
                                                                placeholder="Scrivi qui i dettagli della risoluzione..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Annulla</button>
                                                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Conferma Risoluzione</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <?php // Dismiss modal ?>
                                    <div class="modal fade text-start" id="dismissReportModal<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow" style="background-color: var(--bs-body-bg); color: var(--bs-body-color);">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-secondary"><i class="bi bi-eye-slash-fill me-2"></i>Ignora Segnalazione #<?= $r['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                                </div>
                                                <form action="<?= url('/admin/reports/' . $r['id'] . '/dismiss') ?>" method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                                    <div class="modal-body py-3">
                                                        <p class="text-muted small">
                                                            Contrassegna questa segnalazione come **Ignorata/Archiviata**. Puoi aggiungere una breve spiegazione del perché la segnalazione è stata ritenuta non fondata.
                                                        </p>
                                                        <div class="mb-0">
                                                            <label for="dismiss_notes<?= $r['id'] ?>" class="form-label fw-semibold">Note dell'Amministratore (opzionale)</label>
                                                            <textarea name="admin_notes" id="dismiss_notes<?= $r['id'] ?>" rows="3" class="form-control rounded-3 bg-body-secondary border-secondary-subtle text-body" 
                                                                placeholder="Scrivi qui il motivo dell'archiviazione..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Annulla</button>
                                                        <button type="submit" class="btn btn-secondary rounded-pill px-4 fw-bold text-white shadow-sm">Archivia Segnalazione</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Nessuna segnalazione trovata.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php // Pagination ?>
    <?php if ($totalPagesReports > 1): ?>
        <div class="card-footer bg-body-tertiary border-top p-3">
            <nav aria-label="Page navigation" class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    <?php if ($pageReports <= 1): ?>
                        <li class="page-item disabled"><span class="page-link text-danger">← Precedente</span></li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link text-danger" href="<?= url('/admin?' . http_build_query(['reports_page' => $pageReports - 1, 'search_report' => $searchReport, 'status_report' => $statusReport])) ?>#reports-section">← Precedente</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPagesReports; $i++): ?>
                        <?php if ($i == $pageReports): ?>
                            <li class="page-item active"><span class="page-link bg-danger border-danger"><?= $i ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link text-danger" href="<?= url('/admin?' . http_build_query(['reports_page' => $i, 'search_report' => $searchReport, 'status_report' => $statusReport])) ?>#reports-section"><?= $i ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pageReports >= $totalPagesReports): ?>
                        <li class="page-item disabled"><span class="page-link text-danger">Prossima →</span></li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link text-danger" href="<?= url('/admin?' . http_build_query(['reports_page' => $pageReports + 1, 'search_report' => $searchReport, 'status_report' => $statusReport])) ?>#reports-section">Prossima →</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="text-center mt-2 small text-body-secondary">
                Pagina <?= $pageReports ?> di <?= $totalPagesReports ?> (<?= $totalReportsFiltered ?> segnalazioni totali)
            </div>
        </div>
    <?php endif; ?>
</div>
