<?php
require 'config/database.php';
$r = $conn->query("SELECT id, user_id, device_kode, status, item_count, created_at FROM piru_sessions ORDER BY id DESC LIMIT 5");
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
