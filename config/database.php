<?php
// =============================================
// Konfigurasi Database
// Sesuaikan DB_USER, DB_PASS, DB_NAME dengan
// kredensial yang diberikan cPanel hosting Anda
// =============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // cPanel: biasanya format cpanelusername_dbuser
define('DB_PASS', '');           // cPanel: password database user
define('DB_NAME', 'bank_sampah');// cPanel: biasanya format cpanelusername_dbname

// =============================================
// Deteksi BASE_URL otomatis
// Lokal  : http://localhost/bank-sampah → '/bank-sampah'
// Production (root domain) : http://domain.com → ''
// =============================================
if (!defined('BASE_URL')) {
    $docRoot     = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $projectRoot = rtrim(str_replace('\\', '/', __DIR__), '/');
    // Naik 1 level dari config/ ke root project
    $projectRoot = rtrim(dirname($projectRoot), '/');
    // Hindari realpath() yang bisa false di hosting restricted
    $base = str_replace($docRoot, '', $projectRoot);
    $base = str_replace('\\', '/', $base);
    define('BASE_URL', rtrim($base, '/'));
}

// =============================================
// Koneksi Database
// =============================================
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // Jangan expose detail error ke browser di production
    error_log('DB connect error: ' . $conn->connect_error);
    $isApi = (strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false);
    if ($isApi) {
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Koneksi database gagal.']));
    }
    die('<h3 style="font-family:sans-serif;color:#c00;padding:2rem">Koneksi database gagal. Hubungi administrator.</h3>');
}

$conn->set_charset('utf8mb4');

function generateKode($prefix) {
    return $prefix . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function formatPoin($poin) {
    return number_format($poin, 0, ',', '.');
}

function formatBerat($berat) {
    return number_format($berat, 2, ',', '.') . ' kg';
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
