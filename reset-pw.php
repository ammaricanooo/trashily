<?php
// FILE SEMENTARA — HAPUS SETELAH DIPAKAI
require_once 'config/database.php';

$users = $conn->query("SELECT id, nama, email, role FROM users ORDER BY id");
echo "<pre style='font-family:monospace;padding:1rem'>";
echo "=== DAFTAR USER ===\n\n";
while ($u = $users->fetch_assoc()) {
    echo "ID: {$u['id']} | {$u['role']} | {$u['nama']} | {$u['email']}\n";
}
echo "\n";

// Ganti password di sini
$passwords = [
    // 'email@domain.com' => 'password_baru',
    'admin@trashily.id' => 'admin123',
    'ammarithm@gmail.com' => 'customer123',
];

foreach ($passwords as $email => $pw) {
    $hash = password_hash($pw, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
    $stmt->bind_param('ss', $hash, $email);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "✓ Reset: $email → $pw\n";
    } else {
        echo "✗ Tidak ditemukan: $email\n";
    }
}

echo "\n⚠ HAPUS FILE INI SEKARANG!\n";
echo "</pre>";
