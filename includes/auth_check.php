<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Pastikan BASE_URL tersedia
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/database.php';
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . getLoginPath());
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        // Cek jika sedang di folder everwear
        if (strpos($_SERVER['REQUEST_URI'], '/everwear/') !== false) {
            header('Location: ' . BASE_URL . '/everwear/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/customer/dashboard.php');
        }
        exit;
    }
}

function requireCustomer() {
    requireLogin();
    if ($_SESSION['role'] !== 'customer') {
        // Cek jika sedang di folder everwear
        if (strpos($_SERVER['REQUEST_URI'], '/admin/everwear/') !== false) {
            header('Location: ' . BASE_URL . '/admin/everwear/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
        }
        exit;
    }
}

function getLoginPath() {
    return BASE_URL . '/auth/login.php';
}
