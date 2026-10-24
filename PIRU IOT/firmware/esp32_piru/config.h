/**
 * ============================================================================
 * PIRU (Pilah Runtah) - Smart Waste Bin System
 * Terhubung ke Ekosistem Trashily.id
 *
 * File       : config.h
 * Deskripsi  : Definisi Pin GPIO, Parameter Kalibrasi, dan Konstanta Sistem
 * Target MCU : ESP32 DOIT DevKit V1 (30 Pin)
 * ============================================================================
 */

#ifndef CONFIG_H
#define CONFIG_H

#include <Arduino.h>

// ============================================================================
// 1. PEMETAAN PIN GPIO (DOIT DevKit V1 - 30 Pin)
// ============================================================================

// --- I2C LCD 16x2 ---
#define LCD_I2C_ADDR 0x27 // Alamat I2C umum (0x27 atau 0x3F)
#define LCD_COLS 16
#define LCD_ROWS 2
#define PIN_I2C_SDA 21 // SDA (Default ESP32)
#define PIN_I2C_SCL 22 // SCL (Default ESP32)

// --- Sensor Infrared Proximity Baki ---
#define PIN_IR_TRAY1_ORG 27   // Baki 1: Organik (GPIO 27)
#define PIN_IR_TRAY2_ANORG 23 // Baki 2: Anorganik (GPIO 23)

// Logika Level Pemicu Sensor Proximity IR:
// - LOW  (0): Active LOW  -> Pin bernilai LOW (0) saat ada halangan (Standar
// FC-51 / NPN N.O)
// - HIGH (1): Active HIGH -> Pin bernilai HIGH (1) saat ada halangan (Tipe NPN
// N.C / Modul tertentu)
#define IR_TRIGGER_STATE LOW

// --- Motor Servo Baki Pemilah (MG996R) ---
#define PIN_SERVO1_ORG 13   // Servo 1: Katup Baki Organik
#define PIN_SERVO2_ANORG 14 // Servo 2: Katup Baki Anorganik

// ============================================================================
// SAKELAR SENSOR BERAT (LOAD CELL + HX711)
// Catatan: Sensor fisik belum dipasang saat ini, sehingga di-set ke 'false'.
// Ubah menjadi 'true' jika sensor Load Cell 50kg + modul HX711 sudah dipasang.
// ============================================================================
#define USE_LOADCELL false       // <-- false: Non-aktifkan sensor berat
#define DUMMY_WEIGHT_GRAM 100.0f // Bobot default (simulasi) per pemilahan

// --- Sensor Berat Load Cell 50kg + Modul HX711 (Opsional / Aktif jika
// USE_LOADCELL = true) ---
#define PIN_HX711_1_DOUT 4  // Load Cell 1 (Organik) DOUT
#define PIN_HX711_1_SCK 5   // Load Cell 1 (Organik) SCK
#define PIN_HX711_2_DOUT 18 // Load Cell 2 (Anorganik) DOUT
#define PIN_HX711_2_SCK 19  // Load Cell 2 (Anorganik) SCK

// --- Sensor Ultrasonik Waterproof JSN-SR04T (Level Kapasitas Tong) ---
#define PIN_US1_TRIG 25 // Ultrasonik 1 (Organik) TRIG
#define PIN_US1_ECHO 26 // Ultrasonik 1 (Organik) ECHO
#define PIN_US2_TRIG 32 // Ultrasonik 2 (Anorganik) TRIG
#define PIN_US2_ECHO 33 // Ultrasonik 2 (Anorganik) ECHO

// --- Indikator Audio (Active Buzzer 5V) ---
// Catatan: GPIO 12 adalah Strapping Pin pada ESP32. Pastikan di-drive LOW saat
// boot
#define PIN_BUZZER 12

// ============================================================================
// 2. PARAMETER KALIBRASI & AMBANG BATAS (THRESHOLDS)
// ============================================================================

// Batas Jarak Ultrasonik untuk status PENUH (dalam centimeter)
// Jika jarak permukaan sampah ke sensor < 15 cm, tong dinyatakan PENUH
#define DIST_FULL_THRESHOLD_CM 15.0f

// Jarak maksimal pembacaan JSN-SR04T yang valid (cm)
#define DIST_MAX_VALID_CM 250.0f
#define DIST_MIN_VALID_CM 20.0f // Blind zone JSN-SR04T sekitar 20cm

// Posisi Derajat Servo (MG996R)
#define SERVO_POS_CLOSED 0 // Tertutup / Menahan sampah di baki
#define SERVO_POS_OPEN 90  // Terbuka / Membuang sampah ke kompartemen bawah

// Durasi Operasional (milidetik)
#define SERVO_OPEN_DURATION_MS 2000 // Durasi servo terbuka
#define WEIGHT_SETTLE_DELAY_MS 1000 // Durasi jeda sebelum bacaan berat stabil
#define YOLO_TIMEOUT_MS 5000        // Batas waktu menunggu balasan YOLO laptop
#define IR_DEBOUNCE_MS 50 // Debounce filter sensor IR (50ms cepat & responsif)
#define IR_COOLDOWN_MS                                                         \
  1500 // Jeda waktu cooldown antar-deteksi (mencegah loop tak henti)

// Faktor Kalibrasi Awal Load Cell (Ubah nilai ini sesuai kalibrasi nyata)
// Rumus kalibrasi: calibration_factor = (reading_with_known_weight -
// zero_reading) / known_weight_grams
#define DEFAULT_CAL_FACTOR_1 420.0f // Kalibrasi Load Cell Organik
#define DEFAULT_CAL_FACTOR_2 420.0f // Kalibrasi Load Cell Anorganik

// Ambang Batas Minimum Kenaikan Berat (Gram) agar dihitung sebagai transaksi
// valid
#define MIN_WEIGHT_DELTA_GRAM 2.0f

// Baudrate Komunikasi Serial
#define SERIAL_BAUD_RATE 115200

// ============================================================================
// 3. KONFIGURASI WIFI & MQTT BROKER (SERVER DEBIAN MOSQUITTO)
// ============================================================================
#define USE_WIFI_MQTT true           // true: Aktifkan komunikasi wireless MQTT
#define WIFI_SSID "Redmi Note 14"    // Nama WiFi / Hotspot
#define WIFI_PASSWORD "aaaaaaab"     // Password WiFi
#define MQTT_SERVER "139.99.122.214" // IP Publik Server Debian Anda
#define MQTT_PORT 20067              // Port Publik NAT Mosquitto
#define MQTT_USER "piru" // Username yang dibuat via mosquitto_passwd
#define MQTT_PASS "piru" // Password yang dibuat via mosquitto_passwd
#define PIRU_DEVICE_ID "PIRU-DEV-001" // ID Unit PIRU Smart Bin

#endif // CONFIG_H
