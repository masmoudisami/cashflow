<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Log dans ton projet (évite dépendance au système hôte)
ini_set('error_log', '/var/www/html/logs/cashflow_php.log');

// Configuration base de données (DOIT matcher docker-compose)
define('DB_HOST', 'db');              // nom du service docker
define('DB_NAME', 'cashflow_db');        // même que MYSQL_DATABASE
define('DB_USER', 'sami');        // même que MYSQL_USER
define('DB_PASS', 'Sm/131301');        // même que MYSQL_PASSWORD

// URL dynamique (important pour portabilité)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

define('BASE_URL', $protocol . '://' . $host);

// Timezone
date_default_timezone_set('Europe/Paris');