<?php
$badges = [
    'bomber' => [
        'id' => 'bomber',
        'title' => 'Bomber',
        'desc' => 'Segna gol nelle tue partite.',
        'icon' => 'bi-bullseye',
        'value' => $user['total_goals'] ?? 0,
        'tiers' => [1, 10, 50],
        'unit' => 'gol'
    ],
    'veteran' => [
        'id' => 'veteran',
        'title' => 'Veterano',
        'desc' => 'Scendi in campo e gioca partite.',
        'icon' => 'bi-shield-check',
        'value' => $user['matches_played'] ?? 0,
        'tiers' => [1, 10, 50],
        'unit' => 'partite'
    ],
    'star' => [
        'id' => 'star',
        'title' => 'Stella',
        'desc' => 'Fatti votare come Migliore in Campo.',
        'icon' => 'bi-star-fill',
        'value' => $user['mvp_count'] ?? 0,
        'tiers' => [1, 5, 20],
        'unit' => 'titoli MVP'
    ],
    'mister' => [
        'id' => 'mister',
        'title' => 'Mister',
        'desc' => 'Organizza partite per i tuoi amici e non solo.',
        'icon' => 'bi-calendar-heart',
        'value' => $matches_hosted ?? 0,
        'tiers' => [1, 5, 20],
        'unit' => 'organizzate'
    ]
];

if (!function_exists('getBadgeStatus')) {
    function getBadgeStatus($value, $tiers) {
        if ($value >= $tiers[2]) {
            return ['level' => 'Oro', 'color_hex' => '#ffd700', 'next_target' => 'MAX', 'progress' => 100];
        } elseif ($value >= $tiers[1]) {
            return ['level' => 'Argento', 'color_hex' => '#c0c0c0', 'next_target' => $tiers[2], 'progress' => ($value / $tiers[2]) * 100];
        } elseif ($value >= $tiers[0]) {
            return ['level' => 'Bronzo', 'color_hex' => '#cd7f32', 'next_target' => $tiers[1], 'progress' => ($value / $tiers[1]) * 100];
        } else {
            return ['level' => 'Bloccato', 'color_hex' => '#6c757d', 'next_target' => $tiers[0], 'progress' => ($value / $tiers[0]) * 100];
        }
    }
}
?>

<!-- BADGE E OBIETTIVI -->
<div class="row g-4 mt-2 mb-2">
    <div class="col-12">
        <div class="card shadow-sm border rounded-4 p-4 bg-body">
            <h5 class="fw-bold mb-4"><i class="bi bi-award-fill text-warning me-2"></i>Obiettivi e Trofei</h5>
            <div class="row g-3 justify-content-center">
                <?php foreach($badges as $b): ?>
                    <?php 
                        $status = getBadgeStatus($b['value'], $b['tiers']);
                        $isLocked = $status['level'] === 'Bloccato';
                        $borderStyle = $isLocked ? 'border-secondary border-opacity-25 border-dashed' : 'border shadow-sm';
                    ?>
                    
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="card h-100 <?= $borderStyle ?> bg-body-tertiary text-center p-3 badge-card animate-hover" 
                             data-bs-toggle="modal" data-bs-target="#badgeModal<?= $b['id'] ?>" style="cursor: pointer;">
                            
                            <!-- Icona -->
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 badge-card-avatar" 
                                 style="background-color: <?= $isLocked ? 'rgba(108, 117, 125, 0.1)' : $status['color_hex'].'33' ?>;
                                        color: <?= $isLocked ? '#6c757d' : $status['color_hex'] ?>;
                                        border: 2px solid <?= $isLocked ? 'transparent' : $status['color_hex'] ?>;">
                                <i class="bi <?= $b['icon'] ?>"></i>
                            </div>
                            
                            <!-- Titolo -->
                            <span class="small fw-bold d-block text-truncate"><?= e($b['title']) ?></span>
                            <span class="badge mb-2 font-size-2xs" style="background-color: <?= $status['color_hex'] ?>; color: <?= $status['level'] === 'Oro' ? '#000' : '#fff' ?>">
                                <?= e($status['level']) ?>
                            </span>
                            
                            <!-- Barra di Progresso -->
                            <div class="progress mt-auto bg-light border height-6px">
                                <div class="progress-bar" role="progressbar" style="width: <?= $status['progress'] ?>%; background-color: <?= $status['color_hex'] ?>;" 
                                     aria-valuenow="<?= $status['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted mt-1 font-size-2xs">
                                <?php if($status['level'] === 'Oro'): ?>
                                    <?= e($b['value']) ?> (MAX)
                                <?php else: ?>
                                    <?= e($b['value']) ?> / <?= e($status['next_target']) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modali per Info Badge (inserite fuori dalla griglia per evitare problemi di z-index) -->
<?php foreach($badges as $b): ?>
    <?php 
        $status = getBadgeStatus($b['value'], $b['tiers']);
        $isLocked = $status['level'] === 'Bloccato';
    ?>
    <div class="modal fade" id="badgeModal<?= $b['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow rounded-4 text-center p-3" style="background-color: var(--bs-body-bg); color: var(--bs-body-color);">
                <div class="modal-header border-0 p-0 justify-content-end">
                    <button type="button" class="btn-close badge-modal-btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0 mt-n2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 badge-modal-avatar" 
                         style="background-color: <?= $isLocked ? 'rgba(108, 117, 125, 0.1)' : $status['color_hex'].'33' ?>;
                                color: <?= $isLocked ? '#6c757d' : $status['color_hex'] ?>;
                                border: 3px solid <?= $isLocked ? 'transparent' : $status['color_hex'] ?>;">
                        <i class="bi <?= $b['icon'] ?>"></i>
                    </div>
                    <h4 class="fw-bold mb-1"><?= e($b['title']) ?></h4>
                    <span class="badge mb-3 px-3 py-2" style="background-color: <?= $status['color_hex'] ?>; color: <?= $status['level'] === 'Oro' ? '#000' : '#fff' ?>">
                        Livello Attuale: <?= e($status['level']) ?>
                    </span>
                    <p class="text-muted small mb-4"><?= e($b['desc']) ?></p>
                    
                    <div class="text-start bg-body-tertiary rounded-3 p-3 border">
                        <div class="d-flex justify-content-between mb-1 small fw-semibold">
                            <span>Progresso</span>
                            <?php if($status['level'] === 'Oro'): ?>
                                <span><?= e($b['value']) ?> (MAX)</span>
                            <?php else: ?>
                                <span><?= e($b['value']) ?> / <?= e($status['next_target']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="progress mb-2 bg-light border height-8px">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                                 style="width: <?= $status['progress'] ?>%; background-color: <?= $status['color_hex'] ?>;"></div>
                        </div>
                        <small class="text-muted d-block text-center font-size-2xs">
                            <?php if($status['level'] === 'Oro'): ?>
                                Hai raggiunto il livello massimo di questo trofeo!
                            <?php else: ?>
                                Mancano <?= e($status['next_target'] - $b['value']) ?> <?= e($b['unit']) ?> al prossimo livello (<?= $status['level'] === 'Bloccato' ? 'Bronzo' : ($status['level'] === 'Bronzo' ? 'Argento' : 'Oro') ?>).
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
