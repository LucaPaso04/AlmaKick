<div id="matches-section-card" class="card shadow border-0 rounded-4 overflow-hidden mb-5">
    <div class="card-header bg-body-tertiary border-0 p-3">
        <h2 class="h5 fw-bold mb-0"><span class="bi bi-calendar-event-fill me-2 text-success"></span>Gestione Partite</h2>
    </div>

    <?php // Filters & Search ?>
    <div class="card-body border-bottom bg-body-tertiary p-3">
        <form method="GET" action="<?= url('/admin') ?>#matches-section" class="row g-2">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary"><span class="bi bi-search text-body"></span></span>
                    <label for="search-match-input" class="visually-hidden">Cerca partite</label>
                    <input type="text" id="search-match-input" name="search_match" class="form-control" placeholder="Cerca..." value="<?= e($searchMatch) ?>">
                </div>
            </div>

            <div class="col-md-2">
                <label for="filter-status-match-select" class="visually-hidden">Filtra per stato partita</label>
                <select id="filter-status-match-select" name="status_match" class="form-select">
                    <option value="">Stato</option>
                    <option value="open" <?= $statusMatch === 'open' ? 'selected' : '' ?>>Aperte</option>
                    <option value="full" <?= $statusMatch === 'full' ? 'selected' : '' ?>>Complete</option>
                    <option value="finished" <?= $statusMatch === 'finished' ? 'selected' : '' ?>>Concluse</option>
                    <option value="cancelled" <?= $statusMatch === 'cancelled' ? 'selected' : '' ?>>Annullate</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="filter-date-match-input" class="visually-hidden">Filtra per data partita</label>
                <input type="date" id="filter-date-match-input" name="date_match" class="form-control" value="<?= e($dateMatch) ?>" title="Filtra per data">
            </div>

            <div class="col-md-2">
                <label for="filter-format-match-select" class="visually-hidden">Filtra per formato partita</label>
                <select id="filter-format-match-select" name="format_match" class="form-select">
                    <option value="">Formato</option>
                    <option value="5v5" <?= $formatMatch === '5v5' ? 'selected' : '' ?>>5v5</option>
                    <option value="7v7" <?= $formatMatch === '7v7' ? 'selected' : '' ?>>7v7</option>
                    <option value="8v8" <?= $formatMatch === '8v8' ? 'selected' : '' ?>>8v8</option>
                    <option value="11v11" <?= $formatMatch === '11v11' ? 'selected' : '' ?>>11v11</option>
                </select>
            </div>
        </form>
        <?php if ($searchMatch || $statusMatch || $dateMatch || $formatMatch): ?>
            <div class="mt-2">
                <a href="<?= url('/admin') ?>#matches-section" class="btn btn-sm btn-outline-secondary">
                    <span class="bi bi-x-circle me-1"></span>Resetta filtri partite
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Data/Ora</th>
                    <th>Luogo</th>
                    <th>Host</th>
                    <th class="text-center">Formato</th>
                    <th class="text-center">Stato</th>
                    <th class="text-center">Risultato</th>
                    <th class="text-end pe-4">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($matches)): ?>
                    <?php foreach ($matches as $m): ?>
                        <?php
                            $row_class = '';
                            if ($m['status'] === 'cancelled') {
                                $row_class = 'table-cancelled';
                            }
                        ?>
                        <tr class="<?= $row_class ?> clickable-row"
                            onclick="window.location.href='<?= url('/matches/' . $m['id']) ?>?from=admin';">
                            <td class="ps-4 text-muted">#<?= e($m['id']) ?></td>
                            <td><?= $m['date']->format('d/m/Y') . ' ' . substr($m['time'], 0, 5) ?></td>
                            <td class="fw-bold"><?= e(strlen($m['location']) > 20 ? substr($m['location'], 0, 17) . '...' : $m['location']) ?></td>
                            <td><?= e($m['host']['name'] ?? 'N/D') ?></td>
                            <td class="text-center"><span class="badge bg-secondary"><?= e($m['format']) ?></span></td>
                            <td class="text-center">
                                <?php if($m['status'] === 'open'): ?>
                                    <span class="badge bg-primary">Aperta</span>
                                <?php elseif($m['status'] === 'full'): ?>
                                    <span class="badge bg-success">Completa</span>
                                <?php elseif($m['status'] === 'finished'): ?>
                                    <span class="badge bg-dark">Conclusa</span>
                                <?php elseif($m['status'] === 'cancelled'): ?>
                                    <span class="badge bg-danger">Annullata</span>
                                    <?php if($m['cancellation_reason'] === 'Meteo avverso'): ?>
                                        <small class="d-block text-danger mt-1">⛈️ Meteo</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($m['result_home'] !== null): ?>
                                    <span class="fw-bold"><?= e($m['result_home']) ?> — <?= e($m['result_away']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4" onclick="event.stopPropagation();">
                                <?php if($m['status'] === 'open' || $m['status'] === 'full'): ?>
                                    <form action="<?= url('/admin/matches/cancel') ?>" method="POST" class="d-inline-block">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill me-1"
                                            title="Annulla partita" aria-label="Annulla partita"
                                            onclick="return confirm('Annullare questa partita?');"><span
                                                class="bi bi-x-lg"></span></button>
                                    </form>
                                <?php endif; ?>
                                <form action="<?= url('/admin/matches/delete') ?>" method="POST" class="d-inline-block">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="match_id" value="<?= e($m['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill"
                                        title="Elimina partita" aria-label="Elimina partita"
                                        onclick="return confirm('ELIMINARE definitivamente la partita? Questa azione è irreversibile.');"><span
                                            class="bi bi-trash3"></span></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Nessuna partita trovata.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php // Pagination ?>
    <?php if ($totalPagesMatches > 1): ?>
        <div class="card-footer bg-body-tertiary border-top p-3">
            <nav aria-label="Page navigation" class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    <?php if ($pageMatches <= 1): ?>
                        <li class="page-item disabled"><span class="page-link text-success">← Precedente</span></li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link text-success" href="<?= url('/admin?' . http_build_query(['matches_page' => $pageMatches - 1, 'search_match' => $searchMatch, 'status_match' => $statusMatch, 'date_match' => $dateMatch, 'format_match' => $formatMatch])) ?>#matches-section">← Precedente</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPagesMatches; $i++): ?>
                        <?php if ($i == $pageMatches): ?>
                            <li class="page-item active"><span class="page-link bg-success border-success"><?= $i ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link text-success" href="<?= url('/admin?' . http_build_query(['matches_page' => $i, 'search_match' => $searchMatch, 'status_match' => $statusMatch, 'date_match' => $dateMatch, 'format_match' => $formatMatch])) ?>#matches-section"><?= $i ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pageMatches >= $totalPagesMatches): ?>
                        <li class="page-item disabled"><span class="page-link text-success">Prossima →</span></li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link text-success" href="<?= url('/admin?' . http_build_query(['matches_page' => $pageMatches + 1, 'search_match' => $searchMatch, 'status_match' => $statusMatch, 'date_match' => $dateMatch, 'format_match' => $formatMatch])) ?>#matches-section">Prossima →</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="text-center mt-2 small text-body-secondary">
                Pagina <?= $pageMatches ?> di <?= $totalPagesMatches ?> (<?= $totalMatchesFiltered ?> partite totali)
            </div>
        </div>
    <?php endif; ?>
</div>
