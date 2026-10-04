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
        header('Location: ' . BASE_URL . '/customer/dashboard.php');
        exit;
    }
}

function requireCustomer() {
    requireLogin();
    if ($_SESSION['role'] !== 'customer') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }
}

function getLoginPath() {
    return BASE_URL . '/auth/login.php';
}
