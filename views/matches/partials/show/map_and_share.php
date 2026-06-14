<?php
// views/matches/partials/show/map_and_share.php

$dateFormatted = date('d/m/Y H:i', strtotime($match['date'] . ' ' . $match['time']));
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$absoluteUrl = $protocol . '://' . $host . url('/matches/' . $match['id']);

$waTextRaw = "⚽ Partita di Calcetto!\n📍 " . $match['location'] . "\n📅 " . $dateFormatted . "\n💰 Quota: €" . number_format($current_quote, 2) . "\nUnisciti qui: " . $absoluteUrl;
$wa_text = urlencode($waTextRaw);
?>

<?php if(!empty($match['latitude']) && !empty($match['longitude'])): ?>
<div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden hover-scale transition-all" role="region" aria-label="Mappa del luogo della partita">
    <div id="match-map" data-lat="<?= e($match['latitude']) ?>" data-lng="<?= e($match['longitude']) ?>" data-location="<?= e($match['location']) ?>"></div>
</div>
<?php endif; ?>

<div class="mb-4">
    <a href="https://wa.me/?text=<?= $wa_text ?>" target="_blank" rel="noopener"
       class="btn btn-success w-100 rounded-pill fw-bold shadow-sm py-3 d-flex align-items-center justify-content-center hover-scale transition-all focus-ring" style="background: #25D366; border-color: #25D366;" aria-label="Condividi i dettagli della partita su WhatsApp">
        <span class="bi bi-whatsapp me-2 fs-5" aria-hidden="true"></span>Condividi su WhatsApp
    </a>
</div>
