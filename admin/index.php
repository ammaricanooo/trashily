<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin();
header('Location: ' . BASE_URL . '/admin/dashboard.php');
exit;
