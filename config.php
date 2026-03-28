<?php
// Détection automatique de l'URL de base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);

// Définir BASE_URL dynamiquement
define('BASE_URL', $protocol . '://' . $host . $basePath);

// Configuration de la base de données
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'cashflow_db');
define('DB_USER', getenv('DB_USER') ?: 'sami');
define('DB_PASS', getenv('DB_PASS') ?: 'Sm/131301');

// Timezone
date_default_timezone_set('Europe/Paris');

// Mode production (désactiver l'affichage des erreurs)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/cashflow_php.log');