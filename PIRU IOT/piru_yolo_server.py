#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
=============================================================================
PIRU (Pilah Runtah) - Computer Vision & IoT Bridge Server v2.1
Terhubung ke Ekosistem Trashily.id (cPanel REST API + Debian MQTT Broker)

Fitur:
1. Ultralytics YOLOv8 (Deteksi & Klasifikasi Sampah Otomatis: waste_model.pt / yolov8n.pt)
2. OpenCV Live GUI dengan Real-Time HUD, Status Indikator & Kontrol Keyboard
3. Dual Connectivity:
   - PySerial (Komunikasi Dupleks Kabel USB dengan ESP32)
   - MQTT Bridge (Komunikasi Nirkabel via Broker Mosquitto Debian / Cloud)
   - Auto-reconnect background thread jika broker/kabel terputus
4. Integrasi REST API Trashily.id (Sinkronisasi Data Sesi, Bobot, & Poin Otomatis)
5. Text-To-Speech (TTS) Suara Vokal Bahasa Indonesia
=============================================================================
"""

import os
import sys
import time
import json
import queue
import argparse
import threading
import subprocess
from datetime import datetime
from typing import Dict, Optional, Tuple, List

import cv2
import requests
from ultralytics import YOLO

# =============================================================================
# DEFAULT CONFIGURATION & FILE MANAGEMENT
# =============================================================================

CONFIG_FILE_NAME = "piru_config.json"

DEFAULT_BAUD_RATE    = 115200
DEFAULT_CAMERA_ID    = 0
DEFAULT_CONFIDENCE   = 0.25

# Endpoint REST API Trashily.id (di cPanel Shared Hosting)
DEFAULT_TRASHILY_API = "https://trashily.id/api/piru-log.php"
DEFAULT_DEVICE_ID    = "PIRU-DEV-001"
DEFAULT_API_KEY      = "TRASHILY_SECRET_KEY_DEMO"

# Konfigurasi MQTT Broker (Debian VPS Mosquitto / Cloud)
DEFAULT_MQTT_ENABLED = True
DEFAULT_MQTT_HOST    = "139.99.122.214"   # Ganti dengan IP Publik VPS Debian Anda
DEFAULT_MQTT_PORT    = 20067          # Port MQTT NAT VPS Anda
DEFAULT_MQTT_USER    = "piru"        # User dari mosquitto_passwd
DEFAULT_MQTT_PASS    = "piru"
DEFAULT_MQTT_SSL     = False


def load_or_create_config(filepath: str = CONFIG_FILE_NAME) -> Dict:
    """Membaca konfigurasi dari piru_config.json jika ada, atau membuat file baru dengan template."""
    default_config = {
        "device_id": DEFAULT_DEVICE_ID,
        "api_key": DEFAULT_API_KEY,
        "trashily_api": DEFAULT_TRASHILY_API,
        "camera_id": DEFAULT_CAMERA_ID,
        "confidence": DEFAULT_CONFIDENCE,
        "tts_enabled": True,
        "allow_all": False,
        "no_classify": False,
        "mqtt": {
            "enabled": DEFAULT_MQTT_ENABLED,
            "host": DEFAULT_MQTT_HOST,
            "port": DEFAULT_MQTT_PORT,
            "user": DEFAULT_MQTT_USER,
            "pass": DEFAULT_MQTT_PASS,
            "ssl": DEFAULT_MQTT_SSL
        }
    }

    if os.path.exists(filepath):
        try:
            with open(filepath, "r", encoding="utf-8") as f:
                user_conf = json.load(f)
                # Merge deep
                for k, v in user_conf.items():
                    if isinstance(v, dict) and k in default_config and isinstance(default_config[k], dict):
                        default_config[k].update(v)
                    else:
                        default_config[k] = v
                return default_config
        except Exception as e:
            print(f"[CONFIG WARNING] Gagal membaca {filepath}: {e}. Menggunakan konfigurasi default.")
            return default_config
    else:
        try:
            with open(filepath, "w", encoding="utf-8") as f:
                json.dump(default_config, f, indent=4)
            print(f"[CONFIG] Template konfigurasi baru dibuat di '{filepath}'.")
        except Exception:
            pass
        return default_config


def auto_detect_model_path() -> str:
    """Mendeteksi model YOLO terbaik yang tersedia di folder proyek."""
    candidate_models = [
        "yolov8s.pt",
        "yolov8n.pt"
    ]
    for model_name in candidate_models:
        if os.path.exists(model_name):
            return model_name
    return "yolov8n.pt"


# -----------------------------------------------------------------------------
# Mapping Kelas COCO & Model Sampah Khusus (ORGANIK vs ANORGANIK)
# Objek non-sampah (manusia, kursi, motor, laptop, dll.) otomatis diabaikan.
# -----------------------------------------------------------------------------
ORGANIK_CLASSES = {
    # Makanan, buah, sayur, sisa organik (COCO & waste_model.pt)
    "banana", "apple", "sandwich", "orange", "broccoli", "carrot",
    "hot dog", "pizza", "donut", "cake", "potted plant", "leaf",
    "food", "fruit", "vegetable", "bread", "meat", "sisa_makanan", "kulit_buah",
    "biological"  # Kategori organik dari waste_model.pt
}

ANORGANIK_CLASSES = {
    # Kemasan plastik, kaleng, botol, gelas, sendok sekali pakai, kardus
    "bottle", "wine glass", "cup", "fork", "knife", "spoon", "bowl",
    "can", "tin_can", "box", "cardboard", "paper", "plastic_bag", "toothbrush", "scissors",
    # Kategori anorganik dari waste_model.pt
    "plastic", "metal", "trash", "brown-glass", "green-glass", "white-glass", "battery", "clothes", "shoes",
    # Benda padat umum untuk mempermudah pengetesan di ruangan kerja
    "cell phone", "remote", "keyboard", "mouse", "laptop", "book", "backpack",
    "handbag", "umbrella", "tie", "suitcase", "clock", "vase", "teddy bear", "hair drier"
}

# Skema Warna Visual Bounding Box (BGR OpenCV)
COLOR_ORGANIK   = (46, 204, 113)   # Hijau Emerald
COLOR_ANORGANIK = (241, 153, 56)   # Biru Cyan / Deep Sky
COLOR_UNKNOWN   = (149, 165, 166)  # Abu-abu
COLOR_ACCENT    = (255, 102, 0)    # Oranye Brand Trashily


# =============================================================================
# KELAS TEXT-TO-SPEECH (TTS) AUDIO VOKAL
# =============================================================================

class TTSEngine:
    """
    Engine Text-to-Speech (TTS) multi-threaded untuk audio feedback vokal PIRU.
    Berjalan secara asynchronous di background thread sehingga frame kamera
    dan inferensi AI tetap mulus tanpa lag/freeze.
    """

    def __init__(self, enabled: bool = True):
        self.enabled = enabled
        self.speech_queue = queue.Queue()
        self.running = True
        self.worker_thread = threading.Thread(target=self._speech_worker, daemon=True)
        self.worker_thread.start()

    def _speech_worker(self):
        while self.running:
            try:
                text = self.speech_queue.get(timeout=0.5)
                if not self.enabled or not text:
                    continue
                self._speak_sync(text)
            except queue.Empty:
                continue
            except Exception:
                pass

    def _speak_sync(self, text: str):
        if sys.platform == "win32":
            try:
                clean_text = text.replace('"', '').replace("'", "")
                ps_cmd = (
                    f'Add-Type -AssemblyName System.Speech; '
                    f'$synth = New-Object System.Speech.Synthesis.SpeechSynthesizer; '
                    f'$synth.Rate = 0; $synth.Speak("{clean_text}");'
                )
                subprocess.run(
                    ["powershell", "-NoProfile", "-NonInteractive", "-Command", ps_cmd],
                    stdout=subprocess.DEVNULL,
                    stderr=subprocess.DEVNULL,
                    timeout=4.0
                )
            except Exception:
                pass
        else:
            try:
                subprocess.run(["espeak", "-v", "id", text], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
            except Exception:
                pass

    def speak(self, text: str):
        """Memasukkan teks ke antrean ucapan non-blocking."""
        if self.enabled:
            # Cegah penumpukan antrean suara jika terlalu cepat
            while not self.speech_queue.empty():
                try:
                    self.speech_queue.get_nowait()
                except Exception:
                    break
            self.speech_queue.put(text)

    def close(self):
        self.running = False


# =============================================================================
# KELAS SERIAL BRIDGE (KABEL USB ESP32)
# =============================================================================

class ESP32SerialBridge:
    """
    Mengelola komunikasi serial kabel USB dengan ESP32.
    Jika kabel belum terpasang atau tidak ditemukan, program beralih ke mode simulasi
    atau mengandalkan koneksi MQTT tanpa crash.
    """

    def __init__(self, port: Optional[str] = None, baud_rate: int = DEFAULT_BAUD_RATE):
        self.port = port
        self.baud_rate = baud_rate
        self.ser = None
        self.is_connected = False
        self.incoming_queue = queue.Queue()
        self.running = False
        self.reader_thread = None

    def auto_detect_esp32_port(self) -> Optional[str]:
        import serial.tools.list_ports
        ports = serial.tools.list_ports.comports()
        keywords = ["CH340", "CP210", "FTDI", "UART", "USB Serial", "ESP32", "Silicon Labs"]
        for p in ports:
            desc = f"{p.description} {p.manufacturer} {p.hwid}".upper()
            if any(k.upper() in desc for k in keywords):
                return p.device
        if ports:
            return ports[0].device
        return None

    def connect(self) -> bool:
        import serial
        if not self.port:
            self.port = self.auto_detect_esp32_port()

        if not self.port:
            print("[SERIAL] Port COM ESP32 tidak terdeteksi. Berjalan dalam mode MQTT / Simulasi.")
            self.is_connected = False
            return False

        try:
            print(f"[SERIAL] Menghubungkan ke ESP32 pada {self.port} ({self.baud_rate} baud)...")
            self.ser = serial.Serial(self.port, self.baud_rate, timeout=1.0)
            time.sleep(1.5)
            self.is_connected = True
            self.running = True
            self.reader_thread = threading.Thread(target=self._read_worker, daemon=True)
            self.reader_thread.start()
            print(f"[SERIAL SUCCESS] Terhubung ke ESP32 di {self.port}!")
            return True
        except Exception as e:
            print(f"[SERIAL INFO] Port {self.port} tidak dapat dibuka ({e}). Menunggu via MQTT.")
            self.is_connected = False
            return False

    def _read_worker(self):
        while self.running and self.ser and self.ser.is_open:
            try:
                line = self.ser.readline().decode("utf-8", errors="ignore").strip()
                if line:
                    self.incoming_queue.put(line)
            except Exception:
                self.is_connected = False
                break

    def send_line(self, message: str):
        if self.is_connected and self.ser and self.ser.is_open:
            try:
                self.ser.write(f"{message.strip()}\n".encode("utf-8"))
                self.ser.flush()
                print(f"[SERIAL TX -> ESP32] {message.strip()}")
            except Exception as e:
                print(f"[SERIAL ERROR] Gagal mengirim: {e}")
                self.is_connected = False
        else:
            print(f"[SERIAL SIMULATED TX -> ESP32] {message.strip()}")

    def close(self):
        self.running = False
        if self.ser:
            try:
                if self.ser.is_open:
                    self.ser.close()
            except Exception:
                pass
        self.is_connected = False


# =============================================================================
# KELAS MQTT BRIDGE (KOMPATIBEL DENGAN PAHO-MQTT 1.X & 2.X + AUTO RECONNECT)
# =============================================================================

class PiruMQTTBridge:
    """
    Mengelola koneksi klien MQTT non-blocking untuk transmisi wireless.
    Mendukung paho-mqtt v1.x dan v2.x secara native tanpa warning.
    Memiliki background auto-reconnect thread sehingga jika broker Debian baru
    dinyalakan belakangan, Python akan otomatis terhubung kembali tanpa restart.
    """

    def __init__(self,
                 enabled: bool = DEFAULT_MQTT_ENABLED,
                 host: str = DEFAULT_MQTT_HOST,
                 port: int = DEFAULT_MQTT_PORT,
                 username: Optional[str] = DEFAULT_MQTT_USER,
                 password: Optional[str] = DEFAULT_MQTT_PASS,
                 use_ssl: bool = DEFAULT_MQTT_SSL,
                 device_id: str = DEFAULT_DEVICE_ID,
                 on_message_callback = None):
        self.enabled = enabled
        self.host = host
        self.port = port
        self.username = username
        self.password = password
        self.use_ssl = use_ssl
        self.device_id = device_id
        self.on_message_callback = on_message_callback

        self.client = None
        self.is_connected = False
        self.running = False
        self.reconnect_thread = None

    def connect(self) -> bool:
        if not self.enabled:
            return False

        try:
            import paho.mqtt.client as mqtt
        except ImportError:
            print("[MQTT WARNING] Library 'paho-mqtt' belum terinstall! Jalankan: pip install paho-mqtt")
            self.is_connected = False
            return False

        try:
            client_id = f"PIRU_YOLO_{self.device_id}_{int(time.time())}"

            # Kompatibilitas Paho-MQTT 2.0+ vs 1.x
            if hasattr(mqtt, "CallbackAPIVersion"):
                self.client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2, client_id=client_id)
            else:
                self.client = mqtt.Client(client_id=client_id)

            if self.username and self.password:
                self.client.username_pw_set(self.username, self.password)

            if self.use_ssl:
                import ssl
                self.client.tls_set(cert_reqs=ssl.CERT_REQUIRED, tls_version=ssl.PROTOCOL_TLS)

            self.client.on_connect = self._on_connect
            self.client.on_disconnect = self._on_disconnect
            self.client.on_message = self._on_message

            print(f"[MQTT] Menghubungkan ke Broker MQTT: {self.host}:{self.port} (ID: {self.device_id})...")
            try:
                self.client.connect(self.host, self.port, keepalive=60)
                self.client.loop_start()
            except Exception as conn_err:
                print(f"[MQTT INFO] Broker {self.host}:{self.port} belum siap ({conn_err}). Background auto-reconnect aktif.")
                # Tetap jalankan loop_start agar reconnect thread dapat berjalan di background
                self.client.loop_start()

            self.running = True

            # Mulai thread auto-reconnect
            if not self.reconnect_thread or not self.reconnect_thread.is_alive():
                self.reconnect_thread = threading.Thread(target=self._auto_reconnect_worker, daemon=True)
                self.reconnect_thread.start()

            return True
        except Exception as e:
            print(f"[MQTT ERROR] Gagal inisialisasi MQTT: {e}")
            self.is_connected = False
            return False

    def _auto_reconnect_worker(self):
        """Mengecek status koneksi setiap 5 detik. Jika terputus, coba sambungkan ulang otomatis."""
        while self.running:
            time.sleep(5)
            if self.enabled and not self.is_connected and self.client:
                try:
                    self.client.reconnect()
                except Exception:
                    try:
                        self.client.connect(self.host, self.port, keepalive=60)
                    except Exception:
                        pass

    def _on_connect(self, client, userdata, flags, rc, *args):
        # Handle paho-mqtt v2 ReasonCode object vs v1 integer
        is_success = False
        if hasattr(rc, "is_failure"):
            is_success = not rc.is_failure
        elif hasattr(rc, "value"):
            is_success = (rc.value == 0)
        else:
            is_success = (rc == 0)

        if is_success:
            self.is_connected = True
            print(f"\n[MQTT SUCCESS] Terhubung ke Broker MQTT ({self.host}:{self.port})!")
            topics = [
                f"trashily/piru/{self.device_id}/tray/trigger",
                f"trashily/piru/{self.device_id}/telemetry",
                f"trashily/piru/{self.device_id}/weight/data",
            ]
            for t in topics:
                client.subscribe(t)
                print(f"  -> Terlanggan (Subscribed) Topik: {t}")

            client.publish(f"trashily/piru/{self.device_id}/status", json.dumps({
                "server": "PIRU YOLO Server",
                "status": "online",
                "device_id": self.device_id,
                "timestamp": datetime.utcnow().isoformat() + "Z"
            }), retain=True)
        else:
            self.is_connected = False
            print(f"[MQTT ERROR] Koneksi ditolak oleh broker! Status/Reason: {rc}")

    def _on_disconnect(self, client, userdata, rc, *args):
        self.is_connected = False
        print(f"[MQTT WARNING] Terputus dari Broker MQTT ({self.host}:{self.port}). Menunggu rekoneksi otomatis...")

    def _on_message(self, client, userdata, msg):
        topic = msg.topic
        payload_str = msg.payload.decode("utf-8", errors="ignore").strip()
        print(f"[MQTT RX] [{topic}] {payload_str}")

        if self.on_message_callback:
            self.on_message_callback(topic, payload_str)

    def publish_servo_command(self, tray: int, action: str, verdict: str, item_name: str = ""):
        """Mengirim perintah servo dan hasil verdict ke ESP32 via MQTT."""
        if self.client and self.is_connected:
            topic = f"trashily/piru/{self.device_id}/servo/command"
            payload = json.dumps({
                "action": action,
                "tray": tray,
                "verdict": verdict,
                "item_name": item_name
            })
            self.client.publish(topic, payload)
            print(f"[MQTT TX -> ESP32] [{topic}] {payload}")

    def publish_line(self, topic_suffix: str, payload: str):
        if self.client and self.is_connected:
            topic = f"trashily/piru/{self.device_id}/{topic_suffix}"
            self.client.publish(topic, payload)

    def close(self):
        self.running = False
        if self.client:
            try:
                self.client.loop_stop()
                self.client.disconnect()
            except Exception:
                pass
        self.is_connected = False


# =============================================================================
# INTEGRASI REST API TRASHILY.ID (cPanel Shared Hosting)
# =============================================================================

def send_heartbeat_ping(api_url: str, device_id: str, api_key: str):
    """Mengirim ping heartbeat periodik ke Trashily.id agar status perangkat tetap ONLINE."""
    try:
        requests.post(
            api_url,
            json={"action": "ping", "device_id": device_id},
            headers={"Content-Type": "application/json", "Authorization": f"Bearer {api_key}"},
            timeout=2.0
        )
    except Exception:
        pass


def send_to_trashily_api(category: str, weight_grams: float, item_name: Optional[str],
                         api_url: str, device_id: str, api_key: str, on_success=None) -> bool:
    """Mengirim log transaksi sampah ke endpoint backend Trashily.id."""
    timestamp_iso = datetime.utcnow().isoformat() + "Z"
    clean_item_name = item_name or ("Sampah Organik" if category.upper() == "ORGANIK" else "Sampah Anorganik")
    payload = {
        "device_id": device_id,
        "category": category.upper(),
        "item_name": clean_item_name,
        "weight_grams": round(weight_grams, 2),
        "timestamp": timestamp_iso,
        "metadata": {
            "app": "PIRU Smart Bin",
            "model": "YOLOv8",
            "version": "2.1.0"
        }
    }

    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Bearer {api_key}"
    }

    print("\n" + "=" * 55)
    print(f"[TRASHILY.ID API] Menghubungi backend ({api_url})...")
    print(json.dumps(payload, indent=2))

    try:
        response = requests.post(api_url, json=payload, headers=headers, timeout=4.0)
        if response.status_code in [200, 201]:
            resp_data = response.json()
            print(f"[TRASHILY.ID API SUCCESS] Berhasil disinkronisasi: {resp_data}")

            # Notifikasi visual jika sesi customer aktif
            session_info = resp_data.get("session", {})
            if session_info.get("active"):
                user_name = session_info.get("customer_name", "Pengguna")
                points = session_info.get("points_earned", 0)
                print(f"[SESI AKTIF] Terhubung dengan pengguna: {user_name} (+{points} Poin)")
                if on_success:
                    on_success(True, user_name, points)
            else:
                if on_success:
                    on_success(False, "", 0)
            print("=" * 55 + "\n")
            return True
        else:
            print(f"[TRASHILY.ID API ERROR] HTTP Status {response.status_code}: {response.text}")
            print("=" * 55 + "\n")
            return False
    except Exception as e:
        print(f"[TRASHILY.ID API EXCEPTION] Gagal menghubungi backend: {e}")
        print("=" * 55 + "\n")
        return False


# =============================================================================
# KELAS UTAMA: PIRU YOLO SERVER
# =============================================================================

class PiruYoloServer:
    def __init__(self,
                 port: Optional[str] = None,
                 camera_id: int = DEFAULT_CAMERA_ID,
                 model_path: Optional[str] = None,
                 api_url: str = DEFAULT_TRASHILY_API,
                 api_key: str = DEFAULT_API_KEY,
                 device_id: str = DEFAULT_DEVICE_ID,
                 confidence: float = DEFAULT_CONFIDENCE,
                 tts_enabled: bool = True,
                 allow_all: bool = False,
                 no_classify: bool = False,
                 mqtt_enabled: bool = DEFAULT_MQTT_ENABLED,
                 mqtt_host: str = DEFAULT_MQTT_HOST,
                 mqtt_port: int = DEFAULT_MQTT_PORT,
                 mqtt_user: str = DEFAULT_MQTT_USER,
                 mqtt_pass: str = DEFAULT_MQTT_PASS,
                 mqtt_ssl: bool = DEFAULT_MQTT_SSL):

        self.camera_id = camera_id
        self.model_path = model_path or auto_detect_model_path()
        self.api_url = api_url
        self.api_key = api_key
        self.device_id = device_id
        self.confidence = confidence
        self.allow_all = allow_all
        self.no_classify = no_classify

        # Cetak Info Mode
        if no_classify:
            print("[MODE OPERASI] MODE DEMO / BEBAS: Klasifikasi di-bypass, semua baki otomatis disetujui!")
        elif allow_all:
            print("[MODE DETEKSI] MODE BEBAS: Semua benda non-manusia diizinkan sebagai Anorganik.")
        else:
            print("[MODE DETEKSI] MODE SAMPAH: Hanya benda sampah terdaftar yang diterima.")

        # 1. Inisialisasi Audio TTS
        self.tts = TTSEngine(enabled=tts_enabled)

        # 2. Inisialisasi Model YOLOv8
        print(f"[YOLO] Memuat model deteksi dari: '{self.model_path}' ...")
        self.model = YOLO(self.model_path)
        print(f"[YOLO SUCCESS] Model berhasil dimuat ({len(self.model.names)} kelas).")

        # 3. Inisialisasi Serial Bridge (Kabel USB)
        self.serial_bridge = ESP32SerialBridge(port=port)

        # 4. Inisialisasi MQTT Bridge (Nirkabel / Cloud Broker)
        self.mqtt_bridge = PiruMQTTBridge(
            enabled=mqtt_enabled,
            host=mqtt_host,
            port=mqtt_port,
            username=mqtt_user,
            password=mqtt_pass,
            use_ssl=mqtt_ssl,
            device_id=device_id,
            on_message_callback=self.process_incoming_mqtt_message
        )

        # 5. State Deteksi & Evaluasi
        self.latest_detections: List[Dict] = []
        self.last_scan_verdict: Optional[str] = None
        self.last_scan_time: float = 0
        self.last_scan_tray: Optional[int] = None
        self.last_detected_label: Optional[str] = None

        # 6. Status Sesi Pengguna Aktif
        self.active_user_name: Optional[str] = None
        self.last_points_earned: int = 0
        self.active_user_time: float = 0

    def on_api_sync_callback(self, is_active: bool, user_name: str, points: int):
        if is_active:
            self.active_user_name = user_name
            self.last_points_earned = points
            self.active_user_time = time.time()

    def classify_label(self, class_name: str) -> Tuple[str, Tuple[int, int, int]]:
        c_lower = class_name.lower().strip()
        if c_lower in ORGANIK_CLASSES:
            return "ORGANIK", COLOR_ORGANIK
        elif c_lower in ANORGANIK_CLASSES:
            return "ANORGANIK", COLOR_ANORGANIK
        elif self.allow_all and c_lower != "person":
            return "ANORGANIK", COLOR_ANORGANIK
        else:
            return "UNKNOWN", COLOR_UNKNOWN

    def evaluate_tray_request(self, tray_num: int) -> Tuple[str, Optional[Dict]]:
        if self.no_classify:
            detected_label = self.latest_detections[0]["label"] if self.latest_detections else ("Organik" if tray_num == 1 else "Anorganik")
            print(f"[EVAL DEMO] Baki {tray_num} disetujui otomatis (Label: '{detected_label}')")
            verdict = "VALID_ORG" if tray_num == 1 else "VALID_ANORG"
            cat = "ORGANIK" if tray_num == 1 else "ANORGANIK"
            return verdict, {"label": detected_label, "category": cat, "confidence": 1.0}

        if not self.latest_detections:
            print("[EVAL] Tidak ada objek sampah yang terdeteksi di kamera!")
            return "NO_OBJECT", None

        best_obj = max(self.latest_detections, key=lambda x: x["confidence"])
        category = best_obj["category"]
        label = best_obj["label"]
        conf = best_obj["confidence"]

        print(f"[EVAL] Terdeteksi: '{label}' ({category}) [Akurasi: {conf:.1%}] pada Baki {tray_num}")

        if tray_num == 1:
            return ("VALID_ORG", best_obj) if category == "ORGANIK" else ("INVALID", best_obj)
        elif tray_num == 2:
            return ("VALID_ANORG", best_obj) if category == "ANORGANIK" else ("INVALID", best_obj)
        return "INVALID", best_obj

    def trigger_scan_flow(self, tray: int, source: str = "MANUAL"):
        """Alur terpusat untuk memproses permintaan scan dari Serial, MQTT, atau Keyboard."""
        print(f"\n[TRIGGER {source}] Permintaan Scan dari Baki {tray}!")
        verdict, obj_info = self.evaluate_tray_request(tray)
        item_name = obj_info.get("label", "") if obj_info else ""
        self.last_detected_label = item_name

        # 1. Kirim respon balik ke ESP32 via Serial dan MQTT
        self.serial_bridge.send_line(verdict)
        if self.mqtt_bridge.is_connected:
            action = "OPEN" if "VALID" in verdict else "REJECT"
            self.mqtt_bridge.publish_servo_command(tray, action, verdict, item_name)

        # 2. Berikan feedback suara vokal TTS
        if verdict == "VALID_ORG":
            self.tts.speak("Organik")
        elif verdict == "VALID_ANORG":
            self.tts.speak("Anorganik")
        elif verdict == "NO_OBJECT":
            self.tts.speak("Arahkan sampah ke kamera")
        else:
            self.tts.speak("Salah baki")

        # 3. Update HUD banner
        self.last_scan_verdict = verdict
        self.last_scan_time = time.time()
        self.last_scan_tray = tray

        # 4. Jika valid, kirim data penimbangan ke REST API
        if "VALID" in verdict:
            cat = "ORGANIK" if tray == 1 else "ANORGANIK"
            threading.Thread(
                target=send_to_trashily_api,
                args=(cat, 100.0, item_name, self.api_url, self.device_id, self.api_key, self.on_api_sync_callback),
                daemon=True
            ).start()

    def process_incoming_serial_message(self, line: str):
        print(f"[SERIAL RX <- ESP32] {line}")
        try:
            data = json.loads(line)
            event = data.get("event", "")
            if event == "SCAN_REQ":
                tray = int(data.get("tray", 1))
                self.trigger_scan_flow(tray, source="SERIAL")
            elif event == "WEIGHT_DATA":
                tray = data.get("tray", 0)
                category = data.get("category", "UNKNOWN")
                weight_gr = float(data.get("weight_gr", 0.0))
                detected_name = self.last_detected_label or ("Sampah Organik" if category == "ORGANIK" else "Sampah Anorganik")
                threading.Thread(
                    target=send_to_trashily_api,
                    args=(category, weight_gr, detected_name, self.api_url, self.device_id, self.api_key, self.on_api_sync_callback),
                    daemon=True
                ).start()
        except json.JSONDecodeError:
            pass

    def process_incoming_mqtt_message(self, topic: str, payload_str: str):
        try:
            data = json.loads(payload_str)
        except Exception:
            data = {"raw": payload_str}

        # A. Permintaan Scan dari ESP32 via WiFi/MQTT
        if "tray/trigger" in topic:
            tray = int(data.get("tray", 1))
            self.trigger_scan_flow(tray, source="MQTT")

        # B. Laporan Data Penimbangan Bobot Fisik via MQTT
        elif "weight/data" in topic:
            category = data.get("category", "UNKNOWN")
            weight_gr = float(data.get("weight_gr", 100.0))
            detected_name = self.last_detected_label or ("Sampah Organik" if category == "ORGANIK" else "Sampah Anorganik")
            print(f"[DATA TIMBANGAN MQTT] Kategori: {category} | AI: {detected_name} | Berat: {weight_gr:.2f}g")
            threading.Thread(
                target=send_to_trashily_api,
                args=(category, weight_gr, detected_name, self.api_url, self.device_id, self.api_key, self.on_api_sync_callback),
                daemon=True
            ).start()

        # C. Laporan Telemetri Sensor Ultrasonik via MQTT
        elif "telemetry" in topic:
            cap_org = data.get("kapasitas_organik", data.get("org_cap", 0))
            cap_anorg = data.get("kapasitas_anorganik", data.get("anorg_cap", 0))
            try:
                requests.post(
                    self.api_url,
                    json={
                        "action": "ping",
                        "device_id": self.device_id,
                        "kapasitas_organik": cap_org,
                        "kapasitas_anorganik": cap_anorg
                    },
                    headers={"Content-Type": "application/json", "Authorization": f"Bearer {self.api_key}"},
                    timeout=2.0
                )
            except Exception:
                pass

    def draw_hud(self, frame, fps: float):
        h, w, _ = frame.shape

        # --- Header Status Bar ---
        header_color = (25, 30, 36)
        cv2.rectangle(frame, (0, 0), (w, 55), header_color, -1)

        # Judul & Nama Model
        cv2.putText(frame, "PIRU: Pilah Runtah AI", (15, 25),
                    cv2.FONT_HERSHEY_DUPLEX, 0.68, (255, 255, 255), 2)
        model_name = os.path.basename(self.model_path)
        cv2.putText(frame, f"Model: {model_name}", (15, 45),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.42, COLOR_ACCENT, 1)

        # Status Sesi Pengguna Aktif (QR Claim)
        if self.active_user_name and (time.time() - self.active_user_time < 20.0):
            user_badge = f"USER: {self.active_user_name} (+{self.last_points_earned} P)"
            cv2.putText(frame, user_badge, (w // 2 - 130, 34),
                        cv2.FONT_HERSHEY_DUPLEX, 0.55, (46, 204, 113), 2)
        elif self.no_classify:
            cv2.putText(frame, "MODE: DEMO AUTO-VALID", (w // 2 - 120, 35),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 128), 1)

        # Indikator TTS
        tts_txt = "TTS: ON" if self.tts.enabled else "TTS: OFF"
        tts_color = (46, 204, 113) if self.tts.enabled else (120, 120, 120)
        cv2.putText(frame, tts_txt, (w - 430, 33),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.40, tts_color, 1)

        # Indikator MQTT Broker
        if self.mqtt_bridge.enabled:
            if self.mqtt_bridge.is_connected:
                mqtt_txt = f"MQTT: {self.mqtt_bridge.host}:{self.mqtt_bridge.port}"
                mqtt_dot_color = (46, 204, 113) # Hijau
            else:
                mqtt_txt = "MQTT: RECONNECTING..."
                mqtt_dot_color = (0, 165, 255)  # Oranye / Kuning
            cv2.circle(frame, (w - 340, 28), 5, mqtt_dot_color, -1)
            cv2.putText(frame, mqtt_txt, (w - 330, 33),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.40, (220, 220, 220), 1)

        # Indikator ESP32 Serial / Wireless
        is_hw_active = self.serial_bridge.is_connected or self.mqtt_bridge.is_connected
        hw_txt = self.serial_bridge.port if self.serial_bridge.is_connected else ("WIRELESS" if self.mqtt_bridge.is_connected else "SIMULATION")
        hw_color = (46, 204, 113) if is_hw_active else (0, 165, 255)
        cv2.circle(frame, (w - 150, 28), 6, hw_color, -1)
        cv2.putText(frame, f"ESP32: {hw_txt}", (w - 135, 33),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.40, (220, 220, 220), 1)

        # FPS Counter
        cv2.putText(frame, f"{fps:.1f} FPS", (w - 75, 48),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.38, (150, 150, 150), 1)

        # --- Banner Notifikasi Hasil Scan Terakhir (Tampil 4 detik) ---
        if self.last_scan_verdict and (time.time() - self.last_scan_time < 4.0):
            banner_y = h - 70
            status_text = self.last_scan_verdict

            if status_text == "VALID_ORG":
                banner_bg = (39, 174, 96)
                display_msg = "BAKI 1 VALID: SAMPAH ORGANIK DISETUJUI!"
            elif status_text == "VALID_ANORG":
                banner_bg = (211, 84, 0)
                display_msg = "BAKI 2 VALID: SAMPAH ANORGANIK DISETUJUI!"
            elif status_text == "NO_OBJECT":
                banner_bg = (0, 140, 255)
                display_msg = "SAMPAH TIDAK TERDETEKSI - ARAHKAN KE KAMERA!"
            else:
                banner_bg = (44, 44, 192)
                display_msg = "SALAH BAKI! KATEGORI TIDAK SESUAI!"

            cv2.rectangle(frame, (20, banner_y), (w - 20, banner_y + 48), banner_bg, -1)
            cv2.rectangle(frame, (20, banner_y), (w - 20, banner_y + 48), (255, 255, 255), 2)
            cv2.putText(frame, display_msg, (40, banner_y + 32),
                        cv2.FONT_HERSHEY_DUPLEX, 0.70, (255, 255, 255), 2)

        # --- Footer Shortcut Keyboard ---
        footer_y = h - 10
        cv2.putText(frame, "Shortcuts: [1] Tes Baki 1  [2] Tes Baki 2  [W] Tes Timbangan  [T] Tare  [Q] Keluar",
                    (15, footer_y), cv2.FONT_HERSHEY_SIMPLEX, 0.42, (200, 200, 200), 1)

    def run(self):
        """Looping utama server PIRU."""
        # Hubungkan ke hardware
        self.serial_bridge.connect()
        if self.mqtt_bridge.enabled:
            self.mqtt_bridge.connect()

        # Buka kamera
        print(f"[KAMERA] Membuka kamera (ID: {self.camera_id})...")
        cap = cv2.VideoCapture(self.camera_id)
        if not cap.isOpened():
            print(f"[KAMERA ERROR] Gagal membuka kamera ID {self.camera_id}!")
            sys.exit(1)

        cap.set(cv2.CAP_PROP_FRAME_WIDTH, 1280)
        cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 720)

        print("\n" + "=" * 60)
        print("  PIRU YOLO Server Siap Beroperasi (Hybrid Serial + MQTT)!")
        print("  Tekan 'q' di jendela OpenCV untuk keluar.")
        print("=" * 60 + "\n")

        prev_time = time.time()
        last_heartbeat_time = 0.0

        try:
            while True:
                # Heartbeat periodik ke Trashily.id setiap 25 detik
                if time.time() - last_heartbeat_time > 25.0:
                    last_heartbeat_time = time.time()
                    threading.Thread(
                        target=send_heartbeat_ping,
                        args=(self.api_url, self.device_id, self.api_key),
                        daemon=True
                    ).start()

                # Baca pesan masuk dari kabel Serial ESP32
                while not self.serial_bridge.incoming_queue.empty():
                    msg = self.serial_bridge.incoming_queue.get()
                    self.process_incoming_serial_message(msg)

                # Baca frame kamera
                ret, frame = cap.read()
                if not ret:
                    print("[KAMERA ERROR] Frame tidak terbaca.")
                    break

                # Inferensi YOLOv8
                results = self.model(frame, conf=self.confidence, verbose=False)
                current_detections = []

                for r in results:
                    for box in r.boxes:
                        cls_id = int(box.cls[0])
                        conf = float(box.conf[0])
                        label = self.model.names[cls_id].lower().strip()

                        if label == "person":
                            continue

                        category, color = self.classify_label(label)
                        if not self.allow_all and category == "UNKNOWN":
                            continue

                        x1, y1, x2, y2 = map(int, box.xyxy[0])
                        current_detections.append({
                            "label": label,
                            "category": category,
                            "confidence": conf,
                            "bbox": (x1, y1, x2, y2)
                        })

                        # Gambar Bounding Box & Label
                        cv2.rectangle(frame, (x1, y1), (x2, y2), color, 2)
                        badge_text = f"{label} [{category}] {conf:.0%}"
                        (tw, th), _ = cv2.getTextSize(badge_text, cv2.FONT_HERSHEY_SIMPLEX, 0.55, 1)
                        cv2.rectangle(frame, (x1, y1 - 25), (x1 + tw + 10, y1), color, -1)
                        cv2.putText(frame, badge_text, (x1 + 5, y1 - 7),
                                    cv2.FONT_HERSHEY_SIMPLEX, 0.55, (255, 255, 255), 1)

                self.latest_detections = current_detections

                # Hitung FPS & Gambar HUD
                curr_time = time.time()
                fps = 1.0 / (curr_time - prev_time) if (curr_time - prev_time) > 0 else 30.0
                prev_time = curr_time

                self.draw_hud(frame, fps)
                cv2.imshow("PIRU - Trashily.id Smart Waste Classifier", frame)

                # Kontrol Keyboard
                key = cv2.waitKey(1) & 0xFF
                if key == ord('q') or key == ord('Q') or key == 27:
                    break
                elif key == ord('1'):
                    self.trigger_scan_flow(1, source="KEYBOARD_1")
                elif key == ord('2'):
                    self.trigger_scan_flow(2, source="KEYBOARD_2")
                elif key == ord('w') or key == ord('W'):
                    # Simulasi penimbangan sampah seberat 150 gram
                    print("[MANUAL TEST] Simulasi penimbangan sampah seberat 150g...")
                    threading.Thread(
                        target=send_to_trashily_api,
                        args=("ANORGANIK", 150.0, "plastic_bottle", self.api_url, self.device_id, self.api_key, self.on_api_sync_callback),
                        daemon=True
                    ).start()
                elif key == ord('t') or key == ord('T'):
                    print("[MANUAL TRIGGER] Mengirim perintah TARE ke ESP32...")
                    self.serial_bridge.send_line("t")
                    if self.mqtt_bridge.is_connected:
                        self.mqtt_bridge.publish_line("command", json.dumps({"command": "TARE"}))
                elif key == ord('s') or key == ord('S'):
                    print("[MANUAL TRIGGER] Meminta status sensor dari ESP32...")
                    self.serial_bridge.send_line("s")
                    if self.mqtt_bridge.is_connected:
                        self.mqtt_bridge.publish_line("command", json.dumps({"command": "STATUS"}))

        finally:
            cap.release()
            cv2.destroyAllWindows()
            self.tts.close()
            self.serial_bridge.close()
            if self.mqtt_bridge.enabled:
                self.mqtt_bridge.close()
            print("[SYSTEM] Program PIRU dihentikan dengan aman.")


# =============================================================================
# ENTRY POINT
# =============================================================================

def main():
    config = load_or_create_config(CONFIG_FILE_NAME)

    parser = argparse.ArgumentParser(description="PIRU YOLO Server & IoT Bridge v2.1 (Serial + MQTT)")
    parser.add_argument("--port", type=str, default=None, help="Port COM ESP32 Serial (cth: COM3, /dev/ttyUSB0)")
    parser.add_argument("--camera", type=int, default=config.get("camera_id", DEFAULT_CAMERA_ID), help="ID Kamera / Webcam (default: 0)")
    parser.add_argument("--model", type=str, default=None, help="Path model YOLO (default: auto-detect waste_model.pt / yolov8n.pt)")
    parser.add_argument("--api", type=str, default=config.get("trashily_api", DEFAULT_TRASHILY_API), help="URL API Trashily (default: cPanel atau Localhost)")
    parser.add_argument("--no-tts", action="store_true", help="Nonaktifkan feedback suara vokal Text-to-Speech")
    parser.add_argument("--allow-all", action="store_true", default=config.get("allow_all", False), help="Mode Bebas: Izinkan semua benda non-manusia dideteksi sebagai anorganik")
    parser.add_argument("--no-classify", action="store_true", default=config.get("no_classify", False), help="Mode Demo / Auto-Valid: Bypass klasifikasi, semua baki otomatis valid")

    # Argumen MQTT
    mqtt_conf = config.get("mqtt", {})
    parser.add_argument("--mqtt", action="store_true", default=mqtt_conf.get("enabled", DEFAULT_MQTT_ENABLED), help="Aktifkan koneksi MQTT Broker (Debian/Mosquitto)")
    parser.add_argument("--no-mqtt", action="store_true", help="Nonaktifkan koneksi MQTT (hanya gunakan Serial/Kabel)")
    parser.add_argument("--mqtt-host", type=str, default=mqtt_conf.get("host", DEFAULT_MQTT_HOST), help="Host/IP MQTT Broker (default: IP Debian)")
    parser.add_argument("--mqtt-port", type=int, default=mqtt_conf.get("port", DEFAULT_MQTT_PORT), help="Port MQTT Broker (default: 1883 atau port NAT)")
    parser.add_argument("--mqtt-user", type=str, default=mqtt_conf.get("user", DEFAULT_MQTT_USER), help="Username autentikasi MQTT")
    parser.add_argument("--mqtt-pass", type=str, default=mqtt_conf.get("pass", DEFAULT_MQTT_PASS), help="Password autentikasi MQTT")
    parser.add_argument("--mqtt-ssl", action="store_true", default=mqtt_conf.get("ssl", DEFAULT_MQTT_SSL), help="Gunakan SSL/TLS pada MQTT (port 8883)")
    parser.add_argument("--device", type=str, default=config.get("device_id", DEFAULT_DEVICE_ID), help="ID Perangkat PIRU (default: PIRU-DEV-001)")
    args = parser.parse_args()

    mqtt_on = args.mqtt and not args.no_mqtt

    server = PiruYoloServer(
        port=args.port,
        camera_id=args.camera,
        model_path=args.model,
        api_url=args.api,
        api_key=config.get("api_key", DEFAULT_API_KEY),
        device_id=args.device,
        confidence=config.get("confidence", DEFAULT_CONFIDENCE),
        tts_enabled=not args.no_tts and config.get("tts_enabled", True),
        allow_all=args.allow_all,
        no_classify=args.no_classify,
        mqtt_enabled=mqtt_on,
        mqtt_host=args.mqtt_host,
        mqtt_port=args.mqtt_port,
        mqtt_user=args.mqtt_user,
        mqtt_pass=args.mqtt_pass,
        mqtt_ssl=args.mqtt_ssl
    )
    server.run()


if __name__ == "__main__":
    main()
