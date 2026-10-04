<?php
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';

$pesan_sukses = '';
$pesan_error  = '';

// Handle direct PHP Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_lokasi'])) {
    $lat    = trim($_POST['lat'] ?? '');
    $lng    = trim($_POST['lng'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $tarif  = floatval($_POST['tarif_per_km'] ?? 2000);

    if (!empty($lat) && !empty($lng) && !empty($alamat)) {
        $updates = [
            'trashily_lat'    => $lat,
            'trashily_lng'    => $lng,
            'trashily_alamat' => $alamat,
            'tarif_per_km'    => (string)$tarif
        ];
        foreach ($updates as $kunci => $nilai) {
            $stmt = $conn->prepare("INSERT INTO pengaturan (kunci, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
            $stmt->bind_param('ss', $kunci, $nilai);
            $stmt->execute();
        }
        $pesan_sukses = "Lokasi dan tarif Trashily berhasil disimpan!";
    } else {
        $pesan_error = "Semua data lokasi dan alamat harus diisi.";
    }
}

// Ambil data lokasi saat ini dari DB
$res = $conn->query("SELECT kunci, nilai FROM pengaturan");
$setting = [
    'trashily_lat'    => '-6.200000',
    'trashily_lng'    => '106.816666',
    'trashily_alamat' => 'Trashily Central Waste Bank, Jl. Green Eco No. 12',
    'tarif_per_km'    => '2000'
];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $setting[$row['kunci']] = $row['nilai'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokasi Trashily — Panel Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <link rel="stylesheet" href="../assets/css/output.css">
    <style>
        .map-layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 1.25rem;
            align-items: start;
        }
        #mapAdmin {
            height: 520px;
            width: 100%;
            border-radius: 16px;
            border: 1.5px solid var(--border);
            z-index: 1;
        }
        @media (max-width: 900px) {
            .map-layout { grid-template-columns: 1fr; }
            #mapAdmin { height: 380px; }
        }
    </style>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <h1>Lokasi Trashily & Tarif</h1>
                    <p>Tentukan titik peta lokasi pusat Trashily dan tarif ongkos kirim per kilometer</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>

        <div class="content">
            <?php if ($pesan_sukses): ?>
            <div style="background:var(--primary-light);border:1.5px solid var(--primary);border-radius:12px;padding:.875rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;color:var(--primary-dark);font-weight:600">
                <i class="fa-solid fa-circle-check" style="font-size:1.2rem"></i>
                <div><?= htmlspecialchars($pesan_sukses) ?></div>
            </div>
            <?php elseif ($pesan_error): ?>
            <div style="background:#FFEBEE;border:1.5px solid #E53935;border-radius:12px;padding:.875rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;color:#C62828;font-weight:600">
                <i class="fa-solid fa-circle-xmark" style="font-size:1.2rem"></i>
                <div><?= htmlspecialchars($pesan_error) ?></div>
            </div>
            <?php endif; ?>

            <div class="map-layout">
                <!-- Form Pengaturan Lokasi -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-map-pin" style="color:var(--primary);margin-right:.5rem"></i>Set Lokasi Trashily</h3>
                    </div>
                    <div class="card-body">
                        <!-- Search Geocoding -->
                        <div class="form-group">
                            <label class="form-label">Cari Lokasi / Alamat di Peta</label>
                            <div class="input-wrap" style="position:relative">
                                <input type="text" id="searchAddress" class="form-control" placeholder="Ketik nama jalan atau kota..." autocomplete="off">
                                <button type="button" class="btn btn-primary btn-sm" onclick="cariAlamat()" style="position:absolute;right:6px;top:50%;transform:translateY(-50%)">
                                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="button" class="btn btn-ghost btn-sm" style="width:100%" onclick="getGPSLocation()">
                                <i class="fa-solid fa-location-crosshairs" style="color:var(--primary)"></i> Gunakan Lokasi GPS Saya Saat Ini
                            </button>
                        </div>

                        <form method="POST" action="" id="formLokasi">
                            <input type="hidden" name="simpan_lokasi" value="1">
                            
                            <div class="form-group">
                                <label class="form-label">Alamat Lengkap Trashily <span style="color:var(--danger)">*</span></label>
                                <textarea class="form-control" name="alamat" id="trashilyAlamat" rows="3" required placeholder="Jl. Raya Trashily No. 12..."><?= htmlspecialchars($setting['trashily_alamat']) ?></textarea>
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem" class="form-group">
                                <div>
                                    <label class="form-label">Latitude <span style="color:var(--danger)">*</span></label>
                                    <input type="text" class="form-control" name="lat" id="trashilyLat" value="<?= htmlspecialchars($setting['trashily_lat']) ?>" required onchange="manualCoordChange()">
                                </div>
                                <div>
                                    <label class="form-label">Longitude <span style="color:var(--danger)">*</span></label>
                                    <input type="text" class="form-control" name="lng" id="trashilyLng" value="<?= htmlspecialchars($setting['trashily_lng']) ?>" required onchange="manualCoordChange()">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tarif Ongkos Kirim per KM (Rp) <span style="color:var(--danger)">*</span></label>
                                <div class="input-wrap">
                                    <span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);font-weight:700;color:var(--text-muted)">Rp</span>
                                    <input type="number" class="form-control" name="tarif_per_km" id="tarifPerKm" value="<?= htmlspecialchars($setting['tarif_per_km']) ?>" style="padding-left:3rem" min="0" step="500" required>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary" style="width:100%" onclick="confirmSimpan()">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan Lokasi Trashily
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Interactive Leaflet Map -->
                <div class="card" style="padding:1rem">
                    <div style="margin-bottom:.75rem;display:flex;align-items:center;justify-content:space-between">
                        <div style="font-weight:700;font-size:.9rem;color:var(--text-dark)">
                            <i class="fa-solid fa-map-location-dot" style="color:var(--primary);margin-right:.4rem"></i>Peta Interaktif (Geser pin atau klik peta)
                        </div>
                        <div class="text-muted text-small" id="mapStatus">Ready</div>
                    </div>
                    <div id="mapAdmin"></div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
let map, marker;
const initialLat = parseFloat("<?= $setting['trashily_lat'] ?>") || -6.200000;
const initialLng = parseFloat("<?= $setting['trashily_lng'] ?>") || 106.816666;

document.addEventListener('DOMContentLoaded', () => {
    initMap(initialLat, initialLng);
});

function initMap(lat, lng) {
    map = L.map('mapAdmin').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Custom Icon Leaflet
    const trashIcon = L.divIcon({
        className: 'custom-map-pin',
        html: `<div style="background:#4CAF50;color:#fff;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(0,0,0,.3);border:3px solid #fff;font-size:1.1rem"><i class="fa-solid fa-leaf"></i></div>`,
        iconSize: [38, 38],
        iconAnchor: [19, 19]
    });

    marker = L.marker([lat, lng], { draggable: true, icon: trashIcon }).addTo(map);
    marker.bindPopup("<b>Lokasi Trashily Bank Sampah</b><br>Geser pin ini ke lokasi yang tepat.").openPopup();

    marker.on('dragend', function (e) {
        const pos = e.target.getLatLng();
        updateCoords(pos.lat, pos.lng);
        reverseGeocode(pos.lat, pos.lng);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateCoords(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });
}

function updateCoords(lat, lng) {
    document.getElementById('trashilyLat').value = lat.toFixed(6);
    document.getElementById('trashilyLng').value = lng.toFixed(6);
}

function manualCoordChange() {
    const lat = parseFloat(document.getElementById('trashilyLat').value);
    const lng = parseFloat(document.getElementById('trashilyLng').value);
    if (!isNaN(lat) && !isNaN(lng) && map && marker) {
        map.setView([lat, lng], 15);
        marker.setLatLng([lat, lng]);
        reverseGeocode(lat, lng);
    }
}

function reverseGeocode(lat, lng) {
    document.getElementById('mapStatus').textContent = 'Mencari nama jalan...';
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        .then(r => r.json())
        .then(res => {
            if (res && res.display_name) {
                document.getElementById('trashilyAlamat').value = res.display_name;
            }
            document.getElementById('mapStatus').textContent = 'Lokasi terpilih';
        })
        .catch(() => {
            document.getElementById('mapStatus').textContent = 'Lokasi terpilih';
        });
}

function cariAlamat() {
    const q = document.getElementById('searchAddress').value.trim();
    if (!q) return;

    document.getElementById('mapStatus').textContent = 'Mencari...';
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(res => {
            if (res && res.length > 0) {
                const item = res[0];
                const lat = parseFloat(item.lat);
                const lon = parseFloat(item.lon);
                map.setView([lat, lon], 16);
                marker.setLatLng([lat, lon]);
                updateCoords(lat, lon);
                document.getElementById('trashilyAlamat').value = item.display_name;
                document.getElementById('mapStatus').textContent = 'Ketemu!';
            } else {
                Swal.fire({ icon: 'warning', title: 'Tidak ditemukan', text: 'Lokasi tidak ditemukan. Coba ketik nama kota/jalan yang lebih lengkap.', confirmButtonColor: '#4CAF50' });
                document.getElementById('mapStatus').textContent = 'Tidak ditemukan';
            }
        });
}

function getGPSLocation() {
    if (!navigator.geolocation) {
        Swal.fire({ icon: 'error', title: 'Tidak Didukung', text: 'Browser tidak mendukung GPS Geolocation.', confirmButtonColor: '#4CAF50' });
        return;
    }
    document.getElementById('mapStatus').textContent = 'Mengambil GPS...';
    navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        map.setView([lat, lng], 16);
        marker.setLatLng([lat, lng]);
        updateCoords(lat, lng);
        reverseGeocode(lat, lng);
    }, err => {
        Swal.fire({ icon: 'error', title: 'Gagal GPS', text: 'Tidak dapat mengaktifkan lokasi GPS: ' + err.message, confirmButtonColor: '#4CAF50' });
        document.getElementById('mapStatus').textContent = 'Gagal GPS';
    });
}

function confirmSimpan() {
    const lat = document.getElementById('trashilyLat').value.trim();
    const lng = document.getElementById('trashilyLng').value.trim();
    const alamat = document.getElementById('trashilyAlamat').value.trim();
    const tarif_per_km = parseFloat(document.getElementById('tarifPerKm').value || 2000);

    if (!lat || !lng || !alamat) {
        Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Pilih lokasi pada peta dan isi alamat Trashily.', confirmButtonColor: '#4CAF50' });
        return;
    }

    Swal.fire({
        title: 'Simpan Lokasi Trashily?',
        html: `Alamat: <b>${alamat}</b><br>Koordinat: <b>${lat}, ${lng}</b><br>Tarif Ongkir: <b>Rp ${tarif_per_km.toLocaleString('id-ID')} / km</b>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Simpan'
    }).then(r => {
        if (!r.isConfirmed) return;
        // Submit form langsung
        document.getElementById('formLokasi').submit();
    });
}
</script>
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}
</script>
<?php include_once '../includes/notif_scripts.php'; ?>
</body>
</html>
