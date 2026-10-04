<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

$like = '%' . $conn->real_escape_string($q) . '%';
$stmt = $conn->prepare("
    SELECT id, nama, no_hp, email, poin 
    FROM users 
    WHERE role = 'customer' AND (nama LIKE ? OR no_hp LIKE ? OR email LIKE ?)
    ORDER BY nama ASC
    LIMIT 8
");
$stmt->bind_param('sss', $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();

$customers = [];
while ($row = $result->fetch_assoc()) {
    $customers[] = [
        'id'    => $row['id'],
        'nama'  => $row['nama'],
        'no_hp' => $row['no_hp'],
        'email' => $row['email'],
        'poin'  => number_format($row['poin'], 0, ',', '.')
    ];
}

echo json_encode(['success' => true, 'data' => $customers]);
