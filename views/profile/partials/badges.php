<?php
// Calcolo sblocco dei badge basato sulle statistiche dell'utente
$badges = [
    [
        'title' => 'Veterano',
        'desc' => 'Gioca almeno 20 partite',
        'icon' => 'bi-shield-check',
        'color' => 'text-primary',
        'bg' => 'bg-primary bg-opacity-10',
        'unlocked' => ((int)$user['matches_played'] >= 20),
        'progress' => (int)$user['matches_played'] . ' / 20'
    ],
    [
        'title' => 'Bomber',
        'desc' => 'Segna almeno 15 gol',
        'icon' => 'bi-fire',
        'color' => 'text-danger',
        'bg' => 'bg-danger bg-opacity-10',
        'unlocked' => ((int)$user['total_goals'] >= 15),
        'progress' => (int)$user['total_goals'] . ' / 15'
    ],
    [
        'title' => 'Top Player',
        'desc' => 'Ottieni almeno 5 titoli MVP',
        'icon' => 'bi-trophy-fill',
        'color' => 'text-warning',
        'bg' => 'bg-warning bg-opacity-10',
        'unlocked' => ((int)$user['mvp_count'] >= 5),
        'progress' => (int)$user['mvp_count'] . ' / 5'
    ],
    [
        'title' => 'Giocatore Modello',
        'desc' => 'Mantieni un Trust Score >= 95',
        'icon' => 'bi-hand-thumbs-up-fill',
        'color' => 'text-success',
        'bg' => 'bg-success bg-opacity-10',
        'unlocked' => ((int)$user['trust_score'] >= 95),
        'progress' => (int)$user['trust_score'] . ' / 95'
    ],
    [
        'title' => 'Fuoriclasse',
        'desc' => 'Mantieni una Skill Media >= 4.5',
        'icon' => 'bi-star-fill',
        'color' => 'text-info',
        'bg' => 'bg-info bg-opacity-10',
        'unlocked' => ((float)$user['skill_rating'] >= 4.5),
        'progress' => number_format((float)$user['skill_rating'], 1) . ' / 4.5'
    ],
];
?>
<div class="card shadow-sm border rounded-4 p-4 mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-award-fill text-primary me-2"></i>I tuoi Traguardi e Badge</h5>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3">
        <?php foreach ($badges as $badge): ?>
            <div class="col">
                <div class="card h-100 rounded-4 p-3 text-center badge-card <?= !$badge['unlocked'] ? 'badge-card-locked border-secondary-subtle' : '' ?>">
                    <div class="icon-circle <?= $badge['bg'] ?> <?= $badge['color'] ?> mb-2">
                        <i class="bi <?= $badge['icon'] ?>"></i>
                    </div>
                    <h6 class="fw-bold mb-1" style="font-size: 0.95rem;"><?= e($badge['title']) ?></h6>
                    <p class="text-muted small mb-2" style="font-size: 0.75rem; line-height: 1.2;"><?= e($badge['desc']) ?></p>
                    <div class="mt-auto">
                        <?php if ($badge['unlocked']): ?>
                            <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2 py-1 small" style="font-size: 0.7rem;">
                                <i class="bi bi-patch-check-fill me-1"></i>Sbloccato
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-15 text-secondary rounded-pill px-2 py-1 small" style="font-size: 0.7rem;">
                                <i class="bi bi-lock-fill me-1"></i><?= e($badge['progress']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
