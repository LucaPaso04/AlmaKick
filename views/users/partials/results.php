<div class="row g-4">
    <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>
            <?php
                $avatarUrl = null;
                if (!empty($user['avatar'])) {
                    if (strpos($user['avatar'], 'http://') === 0 || strpos($user['avatar'], 'https://') === 0) {
                        $avatarUrl = $user['avatar'];
                    } elseif (strpos($user['avatar'], 'uploads/') === 0) {
                        $avatarUrl = url('/' . $user['avatar']);
                    } else {
                        $avatarUrl = url('/uploads/' . ltrim($user['avatar'], '/'));
                    }
                }
            ?>
            <div class="col-6 col-sm-4 col-md-3">
                <a href="<?= url('/profile?username=' . urlencode($user['username'])) ?>" class="text-decoration-none text-body">
                    <div class="card h-100 user-card instagram-style-card border-0 rounded-4 p-3 d-flex flex-column align-items-center text-center shadow-sm">
                        <!-- Avatar -->
                        <div class="mb-3 position-relative mt-2">
                            <?php if ($avatarUrl): ?>
                                <img src="<?= e($avatarUrl) ?>" alt="Avatar di <?= e($user['name']) ?>" class="rounded-circle user-search-avatar shadow-sm object-fit-cover">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm fw-bold user-search-avatar fs-4">
                                    <?= strtoupper(substr($user['name'] ?? $user['username'], 0, 2)) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Name & Username -->
                        <div class="user-card-info mt-auto">
                            <span class="d-block fw-bold text-truncate username-label" style="max-width: 140px;">
                                @<?= e($user['username']) ?>
                            </span>
                            <span class="d-block text-muted small text-truncate name-label" style="max-width: 140px;">
                                <?= e($user['name']) ?> <?= e($user['last_name'] ?? '') ?>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <div class="card bg-dark bg-opacity-25 border border-secondary border-opacity-10 rounded-4 p-5 shadow-sm">
                <i class="bi bi-emoji-frown fs-1 text-muted opacity-50 mb-3 d-block"></i>
                <h4 class="fw-bold mb-1">Nessun giocatore trovato</h4>
                <p class="text-muted mb-0">Prova a modificare i termini di ricerca.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Dynamic AJAX-friendly pagination -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
    <div class="d-flex justify-content-center mt-5" id="paginationContainer">
        <nav aria-label="Navigazione pagine">
            <ul class="pagination pagination-sm justify-content-center mt-4 mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" data-page="<?= $page - 1 ?>"><i class="bi bi-chevron-left"></i></a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="#" data-page="<?= $page + 1 ?>"><i class="bi bi-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>
