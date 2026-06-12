<?php
// views/matches/show.php
// Main match details view page containing modular sub-templates
?>
<link rel="stylesheet" href="<?= url('/css/matches-show.css') ?>">
<?php if (!empty($match['latitude']) && !empty($match['longitude'])): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <?php require VIEW_PATH . '/matches/partials/show/hero_banner.php'; ?>
        
        <?php require VIEW_PATH . '/matches/partials/show/info_grid.php'; ?>
        
        <?php require VIEW_PATH . '/matches/partials/show/finished_banners.php'; ?>
        
        <?php require VIEW_PATH . '/matches/partials/show/map_and_share.php'; ?>
        
        <?php require VIEW_PATH . '/matches/partials/show/pitch_formation.php'; ?>
        
        <?php require VIEW_PATH . '/matches/partials/show/actions.php'; ?>
        
        <?php require VIEW_PATH . '/matches/partials/show/post_match.php'; ?>
        
        <?php require VIEW_PATH . '/matches/partials/show/players_list.php'; ?>
    </div>
</div>

<script src="<?= url('/js/matches-show.js') ?>" defer></script>
