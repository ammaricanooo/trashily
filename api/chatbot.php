<?php
/**
 * API Chatbot Trashily Powered by Google Gemini AI
 */
header('Content-Type: application/json; charset=utf-8');

// Jangan tampilkan warning PHP mentah sebagai JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/gemini.php';

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan. Gunakan POST.']);
    exit;
}

// Baca input dari JSON atau POST
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

$userMessage = '';
$history = [];

if (is_array($data)) {
    $userMessage = trim($data['message'] ?? '');
    $history = is_array($data['history'] ?? null) ? $data['history'] : [];
}

if (empty($userMessage) && !empty($_POST['message'])) {
    $userMessage = trim($_POST['message']);
    if (!empty($_POST['history']) && is_string($_POST['history'])) {
        $decodedHist = json_decode($_POST['history'], true);
        if (is_array($decodedHist)) $history = $decodedHist;
    }
}

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong.']);
    exit;
}

$apiKey = getActiveGeminiApiKey($conn);
$systemPrompt = getTrashilySystemInstruction();

/**
 * Intelligent Local Knowledge Base Responder (Fallback)
 */
function getIntelligentLocalReply($msg) {
    $msgLower = strtolower($msg);
    
    if (preg_match('/(trashsmart|tempat sampah|smart bin|voice nudge|inspeksi)/i', $msgLower)) {
        return "✨ **TrashSmart (IoT Smart Bin)** adalah tempat sampah pintar edukatif dari Trashily!\n\n"
             . "🔹 **Baki Inspeksi**: Wadah cek material sebelum masuk ke tangki utama.\n"
             . "🔹 **Voice Nudge**: Pemandu suara edukatif otomatis yang mengingatkan untuk mengosongkan cairan/sisa makanan.\n"
             . "🔹 **Sensor Ultrasonik**: Memantau kapasitas tong sampah secara real-time.\n"
             . "🔹 **Instant Reward**: Menyediakan kode QR di layar untuk klaim poin langsung ke akun Trashily.\n\n"
             . "Pelajari inovasi TrashSmart selengkapnya di menu [TrashSmart IoT](trashsmart.php).";
    }
    
    if (preg_match('/(smartkolecer|kolecer|kincir|angin|baling)/i', $msgLower)) {
        return "🌀 **SmartKolecer** adalah inovasi kincir/baling-baling angin khas Sunda yang dimodernisasi oleh Trashily!\n\n"
             . "🔹 **Bilah Daur Ulang**: Dibuat dari plastik bersih berkualitas tinggi yang dipasok langsung dari tempat sampah TrashSmart.\n"
             . "🔹 **Sensor Anemometer IoT**: Mengukur kecepatan putar dan arah hembusan angin secara akurat.\n"
             . "🔹 **Integrasi Cloud**: Data sensor dikirim ke server Trashily untuk pemantauan energi bersih.\n\n"
             . "Lihat integrasi hulu-ke-hilir selengkapnya di menu [SmartKolecer](smartkolecer.php).";
    }

    if (preg_match('/(gembul|eco-kit|ular tangga|magnet kulkas|eco-pass|bingo|estafet|sekolah|smkn 4)/i', $msgLower)) {
        return "🎲 **GEMBUL (Gemar Berdampak untuk Lingkungan)** adalah gerakan anak muda pengubah kebiasaan pilah sampah melalui **Creative Eco-Kit**:\n\n"
             . "🎒 **Paket Sekolah**: Ular Tangga GEMBUL 30 petak, Estafet Pilah 10 menit, Eco-Bingo 3x3, dan Kartu Kasus Pilah.\n"
             . "🏠 **Paket Rumah**: Magnet Kulkas 'Pilah di Rumah' dari kardus & kalender bekas, Eco-Challenge 3–7 hari, dan Mini Tracker.\n"
             . "🎫 **Eco-Pass**: Kartu kertas daur ulang + QR code untuk pencatatan progres misi tanpa membuka privasi siswa.\n"
             . "📅 **Jadwal Publik**: Roadshow sekolah di SMKN 4 Bogor, aksi Ciliwung bersama Alkesa, & Bank Sampah WU.\n\n"
             . "Coba simulasi interaktif Ular Tangga & cek agenda di halaman [GEMBUL](gembul.php#jadwal).";
    }

    if (preg_match('/(harga|poin|katalog|tarif|rupiah|berapa)/i', $msgLower)) {
        return "💰 **Daftar Nilai Tukar Sampah di Trashily**:\n\n"
             . "• **Kaleng Aluminium**: Rp 3.500 / kg (35 poin)\n"
             . "• **Botol Plastik PET**: Rp 2.000 / kg (20 poin)\n"
             . "• **Kardus & Karton**: Rp 1.500 / kg (15 poin)\n"
             . "• **Kertas HVS**: Rp 1.200 / kg (12 poin)\n"
             . "• **Botol Kaca**: Rp 1.000 / kg (10 poin)\n"
             . "• **Plastik Kresek**: Rp 800 / kg (8 poin)\n"
             . "• **Organik / Daun Kering**: Rp 500 / kg (5 poin)\n\n"
             . "Semua poin bisa ditarik tunai atau ditukar hadiah menarik di [Daftar Harga](harga.php).";
    }

    if (preg_match('/(jadwal|agenda|kegiatan|acara|kapan)/i', $msgLower)) {
        return "📅 **Jadwal Kegiatan Publik GEMBUL & Trashily**:\n\n"
             . "Agenda roadshow games di sekolah, aksi susur sungai Ciliwung bersama Komunitas Alkesa, dan workshop daur ulang terbuka untuk umum!\n\n"
             . "Kamu bisa melihat jadwal lengkap, status kegiatan, dan link pendaftaran di [Jadwal Kegiatan GEMBUL](gembul.php#jadwal).";
    }

    if (preg_match('/(jemput|pick-up|pickup|setor|cara kerja)/i', $msgLower)) {
        return "🚚 **Cara Menyetor Sampah di Trashily**:\n\n"
             . "1. **Setor Sendiri**: Bawa sampah terpilah ke drop point Trashily (Gang Kosasih, Cikaret, Bogor Selatan).\n"
             . "2. **Layanan Jemput**: Buat pesanan jemput sampah melalui dashboard akun Trashily. Petugas kami akan menimbang di tempat dan poin otomatis masuk ke akun Anda (tarif hemat Rp 2.500/km)!\n\n"
             . "Daftar akun gratis sekarang di [Pendaftaran Akun](auth/register.php).";
    }

    if (preg_match('/(hadiah|reward|voucher|tukar)/i', $msgLower)) {
        return "🎁 **Program Hadiah & Penukaran Poin Trashily**:\n\n"
             . "Poin hasil pilah sampah bisa ditukarkan dengan berbagai produk ramah lingkungan:\n"
             . "• Tumbler anti bocor eco-friendly\n"
             . "• Totebag ramah lingkungan serbaguna\n"
             . "• Gelas cantik daur ulang\n"
             . "• Dompet daur ulang\n"
             . "• Saldo rupiah langsung!\n\n"
             . "Cek pilihan hadiah lengkap di bagian [Katalog Hadiah](index.php#rewards).";
    }

    if (preg_match('/(halo|hai|pagi|siang|sore|malam|hi|hello)/i', $msgLower)) {
        return "Halo! 👋 Saya **Trashy**, asisten AI cerdas Trashily.\n\n"
             . "Ada yang bisa saya bantu seputar:\n"
             . "• 🗑️ **TrashSmart** (Tempat sampah pintar IoT & baki inspeksi)\n"
             . "• 🌀 **SmartKolecer** (Kolecer energi angin dari sampah daur ulang)\n"
             . "• 🎲 **GEMBUL** (Creative Eco-Kit, Ular Tangga, & Jadwal Kegiatan)\n"
             . "• 💰 **Daftar Harga & Cara Setor Sampah**\n\n"
             . "Silakan tanyakan apa saja yang ingin kamu ketahui!";
    }

    return "Terima kasih atas pertanyaannya! Trashily adalah ekosistem bank sampah digital terpadu yang memiliki inovasi **TrashSmart IoT**, **SmartKolecer**, dan gerakan edukasi **GEMBUL**.\n\n"
         . "Kamu bisa bertanya lebih spesifik tentang:\n"
         . "1. *Cara setor & daftar harga sampah*\n"
         . "2. *Fitur tempat sampah TrashSmart*\n"
         . "3. *Program Creative Eco-Kit GEMBUL*\n"
         . "4. *Jadwal kegiatan publik terdekat*\n\n"
         . "Apa yang ingin kamu ketahui lebih lanjut?";
}

// Coba panggil Google Gemini API jika API Key tersedia
if (!empty($apiKey)) {
    // Siapkan conversational contents
    $contents = [];
    
    // Tambahkan maksimal 6 pesan riwayat terakhir agar konteks tetap terjaga
    $recentHistory = array_slice($history, -6);
    foreach ($recentHistory as $item) {
        $role = ($item['role'] ?? '') === 'user' ? 'user' : 'model';
        $text = trim($item['text'] ?? '');
        if (!empty($text)) {
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $text]]
            ];
        }
    }
    
    // Tambahkan pesan user saat ini
    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => $userMessage]]
    ];

    $payload = [
        'system_instruction' => [
            'parts' => [
                ['text' => $systemPrompt]
            ]
        ],
        'contents' => $contents,
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 800
        ]
    ];

    $model = defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-1.5-flash';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $result = json_decode($response, true);
        $candidateText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!empty($candidateText)) {
            echo json_encode([
                'success' => true,
                'reply' => trim($candidateText),
                'source' => 'gemini'
            ]);
            exit;
        }
    }
}

// Fallback jika API key belum ada atau ada kendala kuota/koneksi
$fallbackReply = getIntelligentLocalReply($userMessage);

echo json_encode([
    'success' => true,
    'reply' => $fallbackReply,
    'source' => empty($apiKey) ? 'local_no_key' : 'local_fallback'
]);
