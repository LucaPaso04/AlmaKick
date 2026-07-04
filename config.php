<?php
// Global configuration

// Set default timezone
date_default_timezone_set('Europe/Rome');

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Path constants
define('BASE_PATH', __DIR__);
define('VIEW_PATH', BASE_PATH . '/views');
define('UPLOAD_PATH', BASE_PATH . '/public/uploads');

// Database settings
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'almakick');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Session settings
define('APP_NAME', 'AlmaKick');
define('SESSION_LIFETIME', 3600); // 1 ora

// External services
define('OPENWEATHER_KEY', 'f0273b985a53c8c146cfaa42d78e084e');



// Auto-detect base folder
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$publicPos = strpos($scriptName, '/public/index.php');
if ($publicPos !== false) {
    $baseFolder = substr($scriptName, 0, $publicPos);
} else {
    // Fallback
    $indexPos = strpos($scriptName, '/index.php');
    $baseFolder = ($indexPos !== false) ? substr($scriptName, 0, $indexPos) : '';
}
define('BASE_URL', $baseFolder);

// URL generator helper
function url($path = '')
{
    return BASE_URL . '/' . ltrim($path, '/');
}

// HTML sanitization helper
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Role formatter helper
function getRoleBadge($role)
{
    $roleClean = strtolower(trim($role ?? ''));
    if (str_contains($roleClean, 'portiere')) {
        return '🧤 Portiere';
    } elseif (str_contains($roleClean, 'difensore')) {
        return '🛡️ Difensore';
    } elseif (str_contains($roleClean, 'centrocampista')) {
        return '🛡️⚔️ Centrocampista';
    } elseif (str_contains($roleClean, 'attaccante')) {
        return '⚔️ Attaccante';
    }
    return '⚽ ' . ($role ?: 'Giocatore');
}

