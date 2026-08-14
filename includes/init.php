<?php

declare(strict_types=1);

const APP_DEBUG = false;

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

// Align PHP's clock with the MySQL server's SYSTEM timezone so that
// expiry comparisons (e.g. password reset tokens) agree with NOW() in the database.
date_default_timezone_set('Asia/Colombo');

if (!defined('BASE_URL')) {
    define('BASE_URL', '/Madixx/');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_URL,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
