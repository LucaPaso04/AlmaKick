<?php
// views/matches/partials/show/map_and_share.php

$dateFormatted = date('d/m/Y H:i', strtotime($match['date'] . ' ' . $match['time']));
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$absoluteUrl = $protocol . '://' . $host . url('/matches/' . $match['id']);

$waTextRaw = "⚽ AlmaKick Match!\n📍 Campo: " . $match['location'] . "\n📅 Data: " . $dateFormatted . "\n💰 Quota: €" . number_format($current_quote, 2) . "\nUnisciti alla partita cliccando qui: " . $absoluteUrl;
$wa_text = urlencode($waTextRaw);
?>

<?php if(!empty($match['latitude']) && !empty($match['longitude'])): ?>
<div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden hover-scale transition-all" role="region" aria-label="Mappa del luogo della partita">
    <div id="match-map" data-lat="<?= e($match['latitude']) ?>" data-lng="<?= e($match['longitude']) ?>" data-location="<?= e($match['location']) ?>"></div>
</div>
<?php endif; ?>

<!-- Share Action Buttons Grid -->
<div class="row g-2 mb-2">
    <div class="col-6">
        <a href="https://wa.me/?text=<?= $wa_text ?>" target="_blank" rel="noopener"
           class="btn btn-success w-100 rounded-pill fw-bold shadow-sm py-2 d-flex align-items-center justify-content-center hover-scale transition-all focus-ring" style="background: #25D366; border-color: #25D366;" aria-label="Condividi i dettagli della partita su WhatsApp">
            <span class="bi bi-whatsapp me-2 fs-5"></span>WhatsApp
        </a>
    </div>
    <div class="col-6">
        <a href="https://t.me/share/url?url=<?= urlencode($absoluteUrl) ?>&text=<?= urlencode($waTextRaw) ?>" target="_blank" rel="noopener"
           class="btn btn-info w-100 rounded-pill fw-bold shadow-sm py-2 d-flex align-items-center justify-content-center hover-scale transition-all focus-ring text-white" style="background: #0088cc; border-color: #0088cc;" aria-label="Condividi su Telegram">
            <span class="bi bi-telegram me-2 fs-5"></span>Telegram
        </a>
    </div>
</div>

<div class="row g-2 mb-4">
    <div class="col-6">
        <button id="copy-link-btn" data-url="<?= $absoluteUrl ?>" class="btn btn-light w-100 rounded-pill fw-bold shadow-sm py-2 d-flex align-items-center justify-content-center hover-scale transition-all focus-ring border" aria-label="Copia link della partita">
            <span class="bi bi-link-45deg me-2 fs-5 text-secondary"></span>Copia Link
        </button>
    </div>
    <div class="col-6">
        <button onclick="downloadICS(this)" 
                data-title="Partita AlmaKick (<?= e($match['format']) ?>)"
                data-location="<?= e($match['location']) ?>"
                data-description="Organizzatore: <?= e($match['host_name'] ?? $match['host_username']) ?>\nQuota stimata: €<?= number_format($current_quote, 2) ?>\n\nIscriviti cliccando qui: <?= $absoluteUrl ?>"
                data-date="<?= $match['date'] ?>"
                data-time="<?= $match['time'] ?>"
                data-match-id="<?= $match['id'] ?>"
                data-url="<?= $absoluteUrl ?>"
                class="btn btn-light w-100 rounded-pill fw-bold shadow-sm py-2 d-flex align-items-center justify-content-center hover-scale transition-all focus-ring border" aria-label="Aggiungi al calendario">
            <span class="bi bi-calendar-event me-2 fs-5 text-danger"></span>In Calendario
        </button>
    </div>
</div>
