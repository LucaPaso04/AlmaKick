<?php
// Configurazione globale dell'applicazione AlmaKickVanilla

// Error reporting per lo sviluppo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definisce le costanti principali del path
define('BASE_PATH', __DIR__);
define('VIEW_PATH', BASE_PATH . '/views');
define('UPLOAD_PATH', BASE_PATH . '/public/uploads');

// Impostazioni Database
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'almakick');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurazione Sessione e Sicurezza
define('APP_NAME', 'AlmaKick');
define('SESSION_LIFETIME', 3600); // 1 ora

// Rilevamento automatico della sottocartella per il funzionamento in htdocs
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$publicPos = strpos($scriptName, '/public/index.php');
if ($publicPos !== false) {
    $baseFolder = substr($scriptName, 0, $publicPos);
} else {
    // Fallback se index.php è fuori da public (ad esempio con riscritture root)
    $indexPos = strpos($scriptName, '/index.php');
    $baseFolder = ($indexPos !== false) ? substr($scriptName, 0, $indexPos) : '';
}
define('BASE_URL', $baseFolder);

// Funzione di utility per generare URL corretti (sia in root che in sottocartelle)
function url($path = '')
{
    return BASE_URL . '/' . ltrim($path, '/');
}

// Funzione di utility per sanitizzare l'output HTML (prevenzione XSS)
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

