<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$pesan_error = '';
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: kelola-artikel.php?error=" . urlencode("ID artikel tidak valid."));
    exit;
}

// Fetch existing article
$stmt = $conn->prepare("SELECT * FROM artikel WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$artikel = $stmt->get_result()->fetch_assoc();

if (!$artikel) {
    header("Location: kelola-artikel.php?error=" . urlencode("Artikel tidak ditemukan."));
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul            = trim($_POST['judul'] ?? '');
    $slug             = trim($_POST['slug'] ?? '');
    $kategori         = trim($_POST['kategori'] ?? 'Edukasi Lingkungan');
    $ringkasan        = trim($_POST['ringkasan'] ?? '');
    $konten           = trim($_POST['konten'] ?? '');
    $gambar           = trim($_POST['gambar'] ?? 'assets/brand.png');
    $gambar_caption   = trim($_POST['gambar_caption'] ?? '');
    $penulis          = trim($_POST['penulis'] ?? 'Tim Edukasi Trashily');
    $penulis_role     = trim($_POST['penulis_role'] ?? 'Environmental & Waste Specialist');
    $tags             = trim($_POST['tags'] ?? '');
    $meta_title       = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $meta_keywords    = trim($_POST['meta_keywords'] ?? '');
    $baca_menit       = intval($_POST['baca_menit'] ?? 4);
    $status           = trim($_POST['status'] ?? 'published');

    if (!empty($judul) && !empty($konten)) {
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $judul), '-'));
        }
        if (empty($meta_title)) $meta_title = $judul . ' | Trashily Edukasi';
        if (empty($meta_description)) $meta_description = $ringkasan;
        if (empty($meta_keywords)) $meta_keywords = $tags;

        // Check if slug taken by another article
        $chk = $conn->prepare("SELECT id FROM artikel WHERE slug = ? AND id != ?");
        $chk->bind_param("si", $slug, $id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $slug .= '-' . rand(10, 99);
        }

        $updateStmt = $conn->prepare("
            UPDATE artikel SET
                judul = ?, slug = ?, kategori = ?, ringkasan = ?, konten = ?,
                gambar = ?, gambar_caption = ?, penulis = ?, penulis_role = ?, tags = ?,
                meta_title = ?, meta_description = ?, meta_keywords = ?,
                baca_menit = ?, status = ?
            WHERE id = ?
        ");
        $updateStmt->bind_param(
            'sssssssssssssisi',
            $judul, $slug, $kategori, $ringkasan, $konten,
            $gambar, $gambar_caption, $penulis, $penulis_role, $tags,
            $meta_title, $meta_description, $meta_keywords,
            $baca_menit, $status, $id
        );

        if ($updateStmt->execute()) {
            header("Location: kelola-artikel.php?sukses=" . urlencode("Perubahan pada artikel '$judul' berhasil disimpan!"));
            exit;
        } else {
            $pesan_error = "Gagal menyimpan perubahan: " . $conn->error;
        }
    } else {
        $pesan_error = "Mohon lengkapi judul dan konten artikel.";
    }
}

$cats = ['Panduan Daur Ulang', 'Inovasi & IoT', 'Program GEMBUL', 'Edukasi Lingkungan', 'Kabar Komunitas', 'Gaya Hidup Hijau'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel: <?= htmlspecialchars($artikel['judul']) ?> — Panel Admin Trashily</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/output.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <style>
        .serp-box {
            background: #ffffff;
            border: 1px solid #dadce0;
            border-radius: 12px;
            padding: 16px;
            font-family: Arial, sans-serif;
        }
        .serp-url {
            color: #202124;
            font-size: 13px;
            margin-bottom: 3px;
        }
        .serp-title {
            color: #1a0dab;
            font-size: 18px;
            font-weight: 400;
            line-height: 1.3;
            margin-bottom: 4px;
        }
        .serp-desc {
            color: #4d5156;
            font-size: 13px;
            line-height: 1.4;
        }
        /* Quill Snow Custom Styling */
        .ql-toolbar.ql-snow {
            border-color: #cbd5e1 !important;
            border-radius: 0.75rem 0.75rem 0 0 !important;
            background: #f8fafc;
            padding: 0.65rem !important;
        }
        .ql-container.ql-snow {
            border-color: #cbd5e1 !important;
            border-radius: 0 0 0.75rem 0.75rem !important;
            font-size: 0.95rem;
            min-height: 380px;
            background: #ffffff;
            font-family: 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .ql-editor {
            min-height: 380px;
            line-height: 1.75;
            color: #1e293b;
        }
        .ql-editor.ql-blank::before {
            color: #94a3b8;
            font-style: normal;
        }
        .ql-editor h2 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
            color: #0f172a;
        }
        .ql-editor h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: 1rem;
            margin-bottom: 0.4rem;
            color: #0f172a;
        }
        .ql-editor p {
            margin-bottom: 0.85rem;
        }
        .ql-editor blockquote {
            border-left: 4px solid #16a34a;
            padding-left: 1rem;
            color: #166534;
            background: #f0fdf4;
            padding: 0.75rem 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="main">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h1>Edit Artikel &amp; SEO</h1>
                    <p>Perbarui konten, optimasi kata kunci, atau sesuaikan status artikel</p>
                </div>
            </div>
            <div class="topbar-right">
                <a href="../baca-artikel.php?slug=<?= urlencode($artikel['slug']) ?>" target="_blank" class="poin-badge" style="text-decoration:none;" title="Lihat di halaman web">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat di Web
                </a>
                <a href="kelola-artikel.php" class="poin-badge" style="text-decoration:none; background:#f1f5f9; color:#475569;">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>

        <div class="content" style="max-width: 1040px; margin: 0 auto;">

            <?php if ($pesan_error): ?>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan!',
                        text: '<?= addslashes($pesan_error) ?>'
                    });
                </script>
            <?php endif; ?>

            <form method="POST" id="formArtikel">
                <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem; align-items:start;">

                    <!-- KOLOM KIRI (KONTEN UTAMA) -->
                    <div style="display:flex; flex-direction:column; gap:1.5rem;">
                        
                        <!-- KARTU INFORMASI UTAMA -->
                        <div style="background:#fff; border-radius:1.25rem; padding:1.5rem; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                            <div style="margin-bottom:1.25rem;">
                                <label style="display:block; font-size:0.85rem; font-weight:700; color:#1e293b; margin-bottom:0.4rem;">
                                    Judul Artikel <span style="color:#ef4444;">*</span>
                                </label>
                                <input type="text" name="judul" id="judul" value="<?= htmlspecialchars($artikel['judul']) ?>" onkeyup="updateLiveSlugAndSERP()" required style="width:100%; padding:0.75rem 1rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.95rem; font-weight:600;">
                            </div>

                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
                                <div>
                                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Kategori Artikel</label>
                                    <select name="kategori" id="kategori" required style="width:100%; padding:0.65rem 0.85rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                                        <?php foreach ($cats as $c): ?>
                                            <option value="<?= $c ?>" <?= $artikel['kategori'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">URL Slug</label>
                                    <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($artikel['slug']) ?>" onkeyup="updateLiveSlugAndSERP()" required style="width:100%; padding:0.65rem 0.85rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem; background:#f8fafc;">
                                </div>
                            </div>

                            <div style="margin-bottom:1.25rem;">
                                <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">
                                    Ringkasan / Cuplikan Singkat <span style="color:#ef4444;">*</span>
                                </label>
                                <textarea name="ringkasan" id="ringkasan" onkeyup="updateLiveSlugAndSERP()" rows="2" required style="width:100%; padding:0.75rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem; line-height:1.5;"><?= htmlspecialchars($artikel['ringkasan']) ?></textarea>
                            </div>

                            <!-- ISI KONTEN VISUAL RICH TEXT EDITOR -->
                            <div>
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                                    <label style="font-size:0.85rem; font-weight:700; color:#1e293b;">
                                        Isi Konten Artikel <span style="color:#ef4444;">*</span>
                                    </label>
                                    <span style="font-size:0.75rem; color:#059669; background:#ecfdf5; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:700; display:flex; align-items:center; gap:0.35rem;">
                                        <i class="fa-solid fa-pen-nib"></i> Visual Rich Editor
                                    </span>
                                </div>

                                <!-- Quill Editor Container -->
                                <div id="quill-editor"></div>
                                <input type="hidden" name="konten" id="konten_hidden">
                                <div style="font-size:0.75rem; color:#64748b; margin-top:0.4rem; display:flex; align-items:center; gap:0.4rem;">
                                    <i class="fa-solid fa-circle-info text-blue-500"></i> Edit teks, pilih Heading (H2/H3), teks tebal/miring, daftar list, kutipan, tautan, atau gambar secara visual.
                                </div>
                            </div>
                        </div>

                        <!-- KARTU OPTIMASI SEO GOOGLE TINGKAT TINGGI -->
                        <div style="background:#fff; border-radius:1.25rem; padding:1.5rem; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                            <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:1rem; padding-bottom:0.75rem; border-bottom:1px solid #f1f5f9;">
                                <div style="width:32px; height:32px; border-radius:8px; background:#fef2f2; color:#ea4335; display:flex; align-items:center; justify-content:center;">
                                    <i class="fa-brands fa-google"></i>
                                </div>
                                <div>
                                    <h3 style="font-size:0.95rem; font-weight:800; color:#0f172a;">Optimasi SEO Google &amp; SERP Preview</h3>
                                    <p style="font-size:0.75rem; color:#64748b;">Pastikan artikel memenuhi kriteria algoritma ranking Google.</p>
                                </div>
                            </div>

                            <!-- Live SERP Snippet Preview -->
                            <div class="serp-box" style="margin-bottom:1.25rem;">
                                <div class="serp-url" id="serp_url">https://trashily.id › artikel › <?= htmlspecialchars($artikel['slug']) ?></div>
                                <div class="serp-title" id="serp_title"><?= htmlspecialchars($artikel['meta_title'] ?: $artikel['judul']) ?></div>
                                <div class="serp-desc" id="serp_desc"><?= htmlspecialchars($artikel['meta_description'] ?: $artikel['ringkasan']) ?></div>
                            </div>

                            <div style="margin-bottom:1rem;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:0.35rem;">
                                    <label style="font-size:0.75rem; font-weight:700; color:#475569;">Custom Meta Title</label>
                                    <span id="title_counter" style="font-size:0.7rem; color:#94a3b8;">0 / 60 karakter</span>
                                </div>
                                <input type="text" name="meta_title" id="meta_title" value="<?= htmlspecialchars($artikel['meta_title']) ?>" onkeyup="updateLiveSlugAndSERP()" placeholder="Kosongkan untuk otomatis menggunakan [Judul] | Trashily Edukasi" style="width:100%; padding:0.6rem 0.85rem; border-radius:0.6rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                            </div>

                            <div style="margin-bottom:1rem;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:0.35rem;">
                                    <label style="font-size:0.75rem; font-weight:700; color:#475569;">Custom Meta Description (Target ideal 150-160 karakter)</label>
                                    <span id="desc_counter" style="font-size:0.7rem; color:#94a3b8;">0 / 160 karakter</span>
                                </div>
                                <textarea name="meta_description" id="meta_description" onkeyup="updateLiveSlugAndSERP()" rows="2" placeholder="Kosongkan untuk otomatis menggunakan Ringkasan artikel" style="width:100%; padding:0.6rem 0.85rem; border-radius:0.6rem; border:1px solid #cbd5e1; font-size:0.85rem;"><?= htmlspecialchars($artikel['meta_description']) ?></textarea>
                            </div>

                            <div>
                                <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:0.35rem;">Focus Keywords (Pisahkan dengan koma)</label>
                                <input type="text" name="meta_keywords" value="<?= htmlspecialchars($artikel['meta_keywords']) ?>" placeholder="bank sampah bogor, daur ulang sampah, pilah botol plastik" style="width:100%; padding:0.6rem 0.85rem; border-radius:0.6rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                            </div>
                        </div>

                    </div>

                    <!-- KOLOM KANAN (METADATA & AKSI) -->
                    <div style="display:flex; flex-direction:column; gap:1.5rem;">
                        
                        <!-- KARTU PUBLIKASI -->
                        <div style="background:#fff; border-radius:1.25rem; padding:1.5rem; border:1px solid #e2e8f0;">
                            <h4 style="font-size:0.9rem; font-weight:800; color:#0f172a; margin-bottom:1rem; padding-bottom:0.5rem; border-bottom:1px solid #f1f5f9;">
                                Status &amp; Aksi
                            </h4>

                            <div style="margin-bottom:1.25rem;">
                                <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Status Publikasi</label>
                                <select name="status" style="width:100%; padding:0.65rem 0.85rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                                    <option value="published" <?= $artikel['status'] === 'published' ? 'selected' : '' ?>>Published (Tayang)</option>
                                    <option value="draft" <?= $artikel['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="archived" <?= $artikel['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                                </select>
                            </div>

                            <div style="margin-bottom:1.25rem;">
                                <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Estimasi Baca (Menit)</label>
                                <input type="number" name="baca_menit" value="<?= intval($artikel['baca_menit']) ?>" min="1" max="60" style="width:100%; padding:0.65rem 0.85rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                            </div>

                            <div style="margin-bottom:1.25rem; font-size:0.75rem; color:#64748b; background:#f8fafc; padding:0.75rem; border-radius:0.6rem;">
                                <div><i class="fa-regular fa-eye mr-1"></i> Dibaca: <strong><?= number_format($artikel['views']) ?> kali</strong></div>
                                <div style="margin-top:0.25rem;"><i class="fa-regular fa-calendar mr-1"></i> Rilis: <?= date('d M Y H:i', strtotime($artikel['created_at'])) ?></div>
                            </div>

                            <button type="submit" style="width:100%; background:#2563eb; color:#fff; border:none; padding:0.85rem; border-radius:0.75rem; font-size:0.9rem; font-weight:700; cursor:pointer; box-shadow:0 4px 10px rgba(37, 99, 235, 0.25); display:flex; align-items:center; justify-content:center; gap:0.5rem; margin-bottom:0.75rem;">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                            </button>

                            <a href="kelola-artikel.php" style="display:block; text-align:center; padding:0.65rem; border-radius:0.75rem; background:#f1f5f9; color:#475569; font-size:0.85rem; font-weight:600; text-decoration:none;">
                                Batal &amp; Kembali
                            </a>
                        </div>

                        <!-- KARTU GAMBAR UTAMA -->
                        <div style="background:#fff; border-radius:1.25rem; padding:1.5rem; border:1px solid #e2e8f0;">
                            <h4 style="font-size:0.9rem; font-weight:800; color:#0f172a; margin-bottom:1rem; padding-bottom:0.5rem; border-bottom:1px solid #f1f5f9;">
                                Gambar Utama
                            </h4>

                            <div style="margin-bottom:1rem;">
                                <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">URL / Path Gambar</label>
                                <input type="text" name="gambar" id="gambar" value="<?= htmlspecialchars($artikel['gambar']) ?>" onchange="previewImg()" style="width:100%; padding:0.65rem 0.85rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                            </div>

                            <div style="margin-bottom:1rem;">
                                <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Alt Text / Caption (SEO Gambar)</label>
                                <input type="text" name="gambar_caption" value="<?= htmlspecialchars($artikel['gambar_caption']) ?>" placeholder="Deskripsi foto untuk Google Images" style="width:100%; padding:0.65rem 0.85rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                            </div>

                            <!-- Preview Thumbnail -->
                            <div style="width:100%; height:130px; border-radius:0.75rem; overflow:hidden; background:#f8fafc; border:1px dashed #cbd5e1; display:flex; align-items:center; justify-content:center;">
                                <img id="img_preview" src="../<?= ltrim($artikel['gambar'], '/') ?>" alt="Preview" style="max-width:100%; max-height:100%; object-fit:contain;">
                            </div>
                        </div>

                        <!-- KARTU PENULIS -->
                        <div style="background:#fff; border-radius:1.25rem; padding:1.5rem; border:1px solid #e2e8f0;">
                            <h4 style="font-size:0.9rem; font-weight:800; color:#0f172a; margin-bottom:1rem; padding-bottom:0.5rem; border-bottom:1px solid #f1f5f9;">
                                Informasi Penulis
                            </h4>

                            <div style="margin-bottom:1rem;">
                                <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Nama Penulis</label>
                                <input type="text" name="penulis" value="<?= htmlspecialchars($artikel['penulis']) ?>" style="width:100%; padding:0.65rem 0.85rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                            </div>

                            <div style="margin-bottom:1rem;">
                                <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Jabatan / Peran</label>
                                <input type="text" name="penulis_role" value="<?= htmlspecialchars($artikel['penulis_role']) ?>" style="width:100%; padding:0.65rem 0.85rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                            </div>

                            <div>
                                <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Tag Artikel</label>
                                <input type="text" name="tags" value="<?= htmlspecialchars($artikel['tags']) ?>" placeholder="daur ulang, plastik, bogor" style="width:100%; padding:0.65rem 0.85rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                            </div>
                        </div>

                    </div>

                </div>
            </form>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
// Inisialisasi Quill Rich Text Editor
const quill = new Quill('#quill-editor', {
    theme: 'snow',
    placeholder: 'Tulis isi artikel secara leluasa di sini...',
    modules: {
        toolbar: [
            [{ 'header': [2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            ['blockquote', 'code-block'],
            ['link', 'image'],
            ['clean']
        ]
    }
});

// Isi konten awal dari database ke dalam visual editor
const initialContent = <?= json_encode($artikel['konten'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
if (initialContent) {
    quill.root.innerHTML = initialContent;
}

// Sinkronkan konten Quill ke hidden input sebelum form disubmit
document.getElementById('formArtikel').addEventListener('submit', function(e) {
    const rawText = quill.getText().trim();
    if (rawText.length === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Konten Artikel Masih Kosong',
            text: 'Silakan tulis isi konten artikel terlebih dahulu sebelum menyimpan.',
            confirmButtonColor: '#2563eb'
        });
        quill.focus();
        return false;
    }

    const htmlContent = quill.root.innerHTML;
    document.getElementById('konten_hidden').value = htmlContent;
});

function updateLiveSlugAndSERP() {
    const judul = document.getElementById('judul').value;
    const customTitle = document.getElementById('meta_title').value;
    const ringkasan = document.getElementById('ringkasan').value;
    const customDesc = document.getElementById('meta_description').value;
    const slugInput = document.getElementById('slug');

    const titleDisplay = customTitle || (judul ? judul + ' | Trashily Edukasi' : 'Judul Artikel | Trashily Edukasi');
    const descDisplay = customDesc || ringkasan || 'Cuplikan deskripsi artikel yang akan terlihat oleh calon pengunjung di Google...';
    const slugDisplay = (slugInput.value || 'artikel');

    document.getElementById('serp_title').textContent = titleDisplay;
    document.getElementById('serp_desc').textContent = descDisplay.substring(0, 160) + (descDisplay.length > 160 ? '...' : '');
    document.getElementById('serp_url').textContent = 'https://trashily.id › artikel › ' + slugDisplay;

    // Character counters
    document.getElementById('title_counter').textContent = titleDisplay.length + ' / 60 karakter';
    document.getElementById('desc_counter').textContent = descDisplay.length + ' / 160 karakter';
}

function previewImg() {
    const val = document.getElementById('gambar').value;
    const preview = document.getElementById('img_preview');
    if (val.startsWith('http://') || val.startsWith('https://')) {
        preview.src = val;
    } else {
        preview.src = '../' + val.replace(/^\/+/, '');
    }
}

// Initial counter update
updateLiveSlugAndSERP();
</script>

</body>
</html>
