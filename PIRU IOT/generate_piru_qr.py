#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
=============================================================================
PIRU (Pilah Runtah) - QR Code Standee & Sticker Generator
Menghasilkan QR Code siap cetak / tempel untuk tempat sampah pintar PIRU
Terhubung ke Web Trashily.id
=============================================================================
"""

import os
import sys
import socket
import argparse
import urllib.parse
import urllib.request

DEFAULT_DEVICE_ID = "PIRU-DEV-001"
DEFAULT_HOST = "localhost"


def get_local_ip() -> str:
    """Mendeteksi IP LAN lokal agar HP pada Wi-Fi yang sama dapat mengakses web."""
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("8.8.8.8", 80))
        ip = s.getsockname()[0]
        s.close()
        return ip
    except Exception:
        return "127.0.0.1"


def generate_qr(host: str, device_id: str, output_dir: str = "."):
    target_url = f"http://{host}/trashily/piru.php?device={device_id}"
    print(f"\n[QR GENERATOR] Target URL: {target_url}")

    encoded_url = urllib.parse.quote(target_url)
    api_url = f"https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=15&format=png&data={encoded_url}"

    os.makedirs(output_dir, exist_ok=True)
    png_path = os.path.join(output_dir, f"qr_{device_id}.png")

    try:
        print(f"[QR] Mengunduh gambar QR code resolusi tinggi...")
        urllib.request.urlretrieve(api_url, png_path)
        print(f"[QR SUCCESS] Gambar tersimpan di: {png_path}")
    except Exception as e:
        print(f"[QR WARNING] Gagal mengunduh PNG via internet ({e}). Membuat fallback HTML.")

    # Template Standee HTML Modern
    html_content = f"""<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIRU Smart Bin QR Standee — {device_id}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * {{ box-sizing: border-box; }}
        body {{
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #0f172a;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
        }}
        .standee-card {{
            width: 340px;
            background: white;
            border-radius: 32px;
            padding: 32px 26px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4);
            text-align: center;
            border: 2px solid #e2e8f0;
            position: relative;
        }}
        .brand-badge {{
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #dcfce7;
            color: #15803d;
            font-size: 11px;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 999px;
            margin-bottom: 14px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }}
        h1 {{
            font-size: 24px;
            color: #0f172a;
            margin: 0 0 4px 0;
            font-weight: 800;
            letter-spacing: -0.5px;
        }}
        p.subtitle {{
            color: #64748b;
            font-size: 12.5px;
            margin: 0 0 20px 0;
            line-height: 1.45;
        }}
        .qr-wrapper {{
            background: #f8fafc;
            border: 2px dashed #006e2f;
            border-radius: 24px;
            padding: 18px;
            display: inline-block;
            margin-bottom: 16px;
            position: relative;
        }}
        .qr-wrapper img {{
            width: 220px;
            height: 220px;
            display: block;
            border-radius: 12px;
        }}
        .device-code {{
            font-family: 'JetBrains Mono', monospace;
            background: #064e29;
            color: #34d399;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 16px;
            box-shadow: 0 2px 6px rgba(6,78,41,0.2);
        }}
        .steps {{
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 18px;
            padding: 14px 16px;
            text-align: left;
            font-size: 11.5px;
            color: #166534;
            line-height: 1.55;
        }}
        .steps b {{
            display: block;
            font-size: 12px;
            margin-bottom: 6px;
            color: #006e2f;
        }}
        .steps ol {{
            margin: 0;
            padding-left: 20px;
        }}
        .steps li {{
            margin-bottom: 3px;
        }}
        .eco-footer {{
            margin-top: 16px;
            font-size: 10px;
            color: #94a3b8;
            font-weight: 600;
        }}
        @media print {{
            body {{ background: transparent; padding: 0; }}
            .standee-card {{ box-shadow: none; border: 1px solid #cbd5e1; }}
        }}
    </style>
</head>
<body>
    <div class="standee-card">
        <div class="brand-badge">
            <i class="fa-solid fa-microchip"></i> Trashily &bull; PIRU IoT AI
        </div>
        <h1>PIRU (Pilah Runtah)</h1>
        <p class="subtitle">Arahkan kamera smartphone ke QR Code untuk memulai sesi pemilahan &amp; raih poin reward!</p>
        
        <div class="qr-wrapper">
            <img src="{api_url}" alt="QR Code PIRU">
        </div>

        <div>
            <span class="device-code"><i class="fa-solid fa-circle-check"></i> UNIT: {device_id}</span>
        </div>

        <div class="steps">
            <b><i class="fa-solid fa-wand-magic-sparkles"></i> 3 Langkah Mudah:</b>
            <ol>
                <li>Scan QR dengan kamera HP (Masuk ke akun)</li>
                <li>Letakkan sampah di <b>Baki 1 Org / 2 Anorg</b></li>
                <li>Tekan "Selesai" di HP &amp; <b>Unduh Bukti PDF</b></li>
            </ol>
        </div>

        <div class="eco-footer">
            &copy; 2026 Trashily.id &bull; Inovasi Bank Sampah Digital
        </div>
    </div>
</body>
</html>
"""
    html_path = os.path.join(output_dir, f"standee_{device_id}.html")
    with open(html_path, "w", encoding="utf-8") as f:
        f.write(html_content)

    print(f"[HTML STANDEE] Template cetak siap dibuka di browser: {html_path}\n")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="PIRU QR Code Generator")
    parser.add_argument("--host", type=str, default="", help="Host IP/Domain Trashily (contoh: 192.168.1.10)")
    parser.add_argument("--device", type=str, default=DEFAULT_DEVICE_ID, help="Kode Device PIRU (default: PIRU-DEV-001)")
    parser.add_argument("--auto-ip", action="store_true", help="Gunakan IP Wi-Fi/LAN lokal komputer secara otomatis")
    parser.add_argument("--out", type=str, default=".", help="Folder output")
    args = parser.parse_args()

    host_to_use = args.host
    if not host_to_use:
        if args.auto_ip:
            host_to_use = get_local_ip()
            print(f"[AUTO-IP] Menggunakan IP LAN komputer: {host_to_use}")
        else:
            host_to_use = DEFAULT_HOST

    generate_qr(host=host_to_use, device_id=args.device, output_dir=args.out)
