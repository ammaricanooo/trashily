/**
 * ============================================================================
 * PIRU (Pilah Runtah) - Smart Waste Bin Firmware v2.1
 * Terhubung ke Ekosistem Trashily.id
 * 
 * Target Hardware : ESP32 DOIT DevKit V1 (30 Pin)
 * Author          : Embedded Systems & CV Engineering Team
 * Versi           : 2.1.0 (Hybrid Serial USB + WiFi MQTT Broker)
 * 
 * Deskripsi Alur:
 * 1. Standby: Pantau level kapasitas tong (Ultrasonik JSN-SR04T) & status baki.
 * 2. Deteksi Sampah: Sensor IR aktif (LOW) pada Baki 1 / Baki 2.
 * 3. Request YOLO: ESP32 mengirim event `SCAN_REQ` ke Server via Serial USB & WiFi MQTT.
 * 4. Verifikasi YOLO:
 *    - "VALID_ORG"   -> Buka Servo 1 (Organik), timbang delta berat, kirim ke Trashily.
 *    - "VALID_ANORG" -> Buka Servo 2 (Anorganik), timbang delta berat, kirim ke Trashily.
 *    - "INVALID"     -> Servo terkunci, alarm buzzer 3x, tolak sampah ("Salah Baki!").
 * 5. Testing Manual: Mendukung input karakter Serial ('1', '2', 'x', 't', 's').
 * ============================================================================
 */

#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ESP32Servo.h>
#include "config.h"

// Load Cell HX711 hanya di-include jika USE_LOADCELL bernilai true di config.h
#if USE_LOADCELL
#include "HX711.h"
#endif

// WiFi & MQTT Client (Diaktifkan jika USE_WIFI_MQTT == true)
#if USE_WIFI_MQTT
#include <WiFi.h>
#include <PubSubClient.h>

WiFiClient espClient;
PubSubClient mqttClient(espClient);

unsigned long last_mqtt_reconnect_attempt = 0;
volatile bool mqtt_response_received = false;
String mqtt_incoming_verdict = "";
#endif

// ============================================================================
// INSTANSIASI OBJEK & VARIABEL GLOBAL
// ============================================================================

// Display LCD 16x2 I2C
LiquidCrystal_I2C lcd(LCD_I2C_ADDR, LCD_COLS, LCD_ROWS);

// Motor Servo Baki (ESP32Servo)
Servo servo1_org;
Servo servo2_anorg;

// Sensor Berat Load Cell (HX711)
#if USE_LOADCELL
HX711 scale1;
HX711 scale2;
#endif

// Status Level Tong Sampah (Ultrasonik)
float dist_org_cm   = 100.0f;
float dist_anorg_cm = 100.0f;
bool is_org_full    = false;
bool is_anorg_full  = false;

// Bobot Kumulatif
float current_weight_org   = 0.0f;
float current_weight_anorg = 0.0f;

// Pewaktu (Timers) untuk non-blocking loop
unsigned long last_ultrasonic_check = 0;
const unsigned long ULTRASONIC_INTERVAL_MS = 2000;

unsigned long last_lcd_refresh = 0;
const unsigned long LCD_REFRESH_INTERVAL_MS = 500;

// Deteksi Transisi & Anti-Spam Trigger Sensor IR
int last_ir1_state = HIGH;
int last_ir2_state = HIGH;
unsigned long last_ir_trigger_ms = 0;

// ============================================================================
// PROTOTYPE FUNGSI
// ============================================================================
void beepBuzzer(int duration_ms, int count = 1, int pause_ms = 80);
float readUltrasonicCM(uint8_t trigPin, uint8_t echoPin);
void updateBinCapacity();
void updateStandbyLCD();
void processTrayAction(int tray_num);
void handleValidWaste(int tray_num, const char* category_name);
void handleNoObjectDetected();
void handleInvalidWaste();
void handleManualSerialInput(char cmd);
void tareAllScales();
void printSensorReport();

#if USE_WIFI_MQTT
void setupWiFi();
void reconnectMQTT();
void mqttCallback(char* topic, byte* payload, unsigned int length);
void publishTelemetry();
#endif

// ============================================================================
// SETUP
// ============================================================================
void setup() {
  Serial.begin(SERIAL_BAUD_RATE);
  delay(200);
  Serial.println("\n\n========================================");
  Serial.println(" PIRU (Pilah Runtah) - System Booting v2.1");
  Serial.println(" Ecosystem: Trashily.id (Serial + MQTT)");
  Serial.println("========================================");

  // Inisialisasi Buzzer (GPIO 12)
  pinMode(PIN_BUZZER, OUTPUT);
  digitalWrite(PIN_BUZZER, LOW);

  // Inisialisasi Sensor Infrared Baki
  pinMode(PIN_IR_TRAY1_ORG, INPUT_PULLUP);
  pinMode(PIN_IR_TRAY2_ANORG, INPUT_PULLUP);
  delay(50);

  // Diagnostik awal sensor IR
  if (digitalRead(PIN_IR_TRAY1_ORG) == IR_TRIGGER_STATE) {
    Serial.printf("[DIAGNOSTIK IR 1] Terdeteksi pemicu (%d) saat startup!\n", IR_TRIGGER_STATE);
  }
  if (digitalRead(PIN_IR_TRAY2_ANORG) == IR_TRIGGER_STATE) {
    Serial.printf("[DIAGNOSTIK IR 2] Terdeteksi pemicu (%d) saat startup!\n", IR_TRIGGER_STATE);
  }

  // Inisialisasi Sensor Ultrasonik
  pinMode(PIN_US1_TRIG, OUTPUT);
  pinMode(PIN_US1_ECHO, INPUT);
  digitalWrite(PIN_US1_TRIG, LOW);

  pinMode(PIN_US2_TRIG, OUTPUT);
  pinMode(PIN_US2_ECHO, INPUT);
  digitalWrite(PIN_US2_TRIG, LOW);

  // Inisialisasi I2C & LCD
  Wire.begin(PIN_I2C_SDA, PIN_I2C_SCL);
  lcd.init();
  lcd.backlight();
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("PIRU: Inisialisasi");
  lcd.setCursor(0, 1);
  lcd.print("Trashily.id IoT ");

  // Alokasi Timer PWM untuk Servo ESP32
  ESP32PWM::allocateTimer(0);
  ESP32PWM::allocateTimer(1);
  ESP32PWM::allocateTimer(2);
  ESP32PWM::allocateTimer(3);

  servo1_org.setPeriodHertz(50);
  servo2_anorg.setPeriodHertz(50);

  servo1_org.attach(PIN_SERVO1_ORG, 500, 2400);
  servo1_org.write(SERVO_POS_CLOSED);
  delay(250);

  servo2_anorg.attach(PIN_SERVO2_ANORG, 500, 2400);
  servo2_anorg.write(SERVO_POS_CLOSED);
  delay(250);

  // Inisialisasi Load Cell HX711 jika aktif
#if USE_LOADCELL
  Serial.println("[LOADCELL] Menginisialisasi HX711...");
  scale1.begin(PIN_HX711_1_DOUT, PIN_HX711_1_SCK);
  scale2.begin(PIN_HX711_2_DOUT, PIN_HX711_2_SCK);
  scale1.set_scale(DEFAULT_CAL_FACTOR_1);
  scale2.set_scale(DEFAULT_CAL_FACTOR_2);
  tareAllScales();
#else
  Serial.println("[LOADCELL] STATUS: DINONAKTIFKAN (Sensor fisik belum dipasang). Mode simulasi aktif.");
#endif

  // Inisialisasi WiFi & MQTT jika diaktifkan
#if USE_WIFI_MQTT
  setupWiFi();
  mqttClient.setServer(MQTT_SERVER, MQTT_PORT);
  mqttClient.setCallback(mqttCallback);
#endif

  // Bunyikan nada startup
  beepBuzzer(60, 2, 60);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("PIRU Siap!");
  lcd.setCursor(0, 1);
#if USE_WIFI_MQTT
  lcd.print(WiFi.status() == WL_CONNECTED ? "WiFi: Connected " : "WiFi: Offline   ");
#else
  lcd.print("Serial Mode...  ");
#endif
  delay(1200);

  updateBinCapacity();

  last_ir1_state = digitalRead(PIN_IR_TRAY1_ORG);
  last_ir2_state = digitalRead(PIN_IR_TRAY2_ANORG);

  updateStandbyLCD();

  Serial.println("[SYSTEM] Ready! Menunggu deteksi objek pada Baki 1/2...");
}

// ============================================================================
// MAIN LOOP
// ============================================================================
void loop() {
#if USE_WIFI_MQTT
  if (WiFi.status() == WL_CONNECTED) {
    if (!mqttClient.connected()) {
      if (millis() - last_mqtt_reconnect_attempt > 5000) {
        last_mqtt_reconnect_attempt = millis();
        reconnectMQTT();
      }
    } else {
      mqttClient.loop();
    }
  }
#endif

  // 1. Cek Kapasitas Tong secara periodik
  if (millis() - last_ultrasonic_check >= ULTRASONIC_INTERVAL_MS) {
    last_ultrasonic_check = millis();
    updateBinCapacity();
  }

  // 2. Refresh Tampilan LCD Standby
  if (millis() - last_lcd_refresh >= LCD_REFRESH_INTERVAL_MS) {
    last_lcd_refresh = millis();
    updateStandbyLCD();
  }

  // 3. Cek Input Pengujian dari Serial Monitor
  if (Serial.available() > 0) {
    char cmd = Serial.read();
    handleManualSerialInput(cmd);
  }

  // 4. Deteksi Objek pada Sensor Infrared Baki
  int ir1_state = digitalRead(PIN_IR_TRAY1_ORG);
  int ir2_state = digitalRead(PIN_IR_TRAY2_ANORG);

  if (ir1_state == IR_TRIGGER_STATE && last_ir1_state != IR_TRIGGER_STATE && (millis() - last_ir_trigger_ms >= IR_COOLDOWN_MS)) {
    delay(IR_DEBOUNCE_MS);
    if (digitalRead(PIN_IR_TRAY1_ORG) == IR_TRIGGER_STATE) {
      last_ir_trigger_ms = millis();
      processTrayAction(1);

      unsigned long release_timeout = millis();
      while (digitalRead(PIN_IR_TRAY1_ORG) == IR_TRIGGER_STATE && (millis() - release_timeout < 4000)) {
        delay(50);
      }
    }
  } else if (ir2_state == IR_TRIGGER_STATE && last_ir2_state != IR_TRIGGER_STATE && (millis() - last_ir_trigger_ms >= IR_COOLDOWN_MS)) {
    delay(IR_DEBOUNCE_MS);
    if (digitalRead(PIN_IR_TRAY2_ANORG) == IR_TRIGGER_STATE) {
      last_ir_trigger_ms = millis();
      processTrayAction(2);

      unsigned long release_timeout = millis();
      while (digitalRead(PIN_IR_TRAY2_ANORG) == IR_TRIGGER_STATE && (millis() - release_timeout < 4000)) {
        delay(50);
      }
    }
  }

  last_ir1_state = digitalRead(PIN_IR_TRAY1_ORG);
  last_ir2_state = digitalRead(PIN_IR_TRAY2_ANORG);

  delay(20);
}

// ============================================================================
// WIFI & MQTT ROUTINES
// ============================================================================
#if USE_WIFI_MQTT
void setupWiFi() {
  Serial.printf("[WIFI] Menghubungkan ke '%s'...\n", WIFI_SSID);
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("WiFi Connecting");
  lcd.setCursor(0, 1);
  lcd.print(WIFI_SSID);

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  unsigned long start_wifi = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - start_wifi < 10000) {
    delay(500);
    Serial.print(".");
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("\n[WIFI SUCCESS] Terhubung! IP: %s\n", WiFi.localIP().toString().c_str());
  } else {
    Serial.println("\n[WIFI WARNING] Gagal terhubung ke WiFi dalam 10s. Melanjutkan via Serial.");
  }
}

void reconnectMQTT() {
  if (WiFi.status() != WL_CONNECTED) return;

  String clientId = "ESP32_PIRU_" + String(PIRU_DEVICE_ID);
  Serial.printf("[MQTT] Mencoba koneksi ke %s:%d...\n", MQTT_SERVER, MQTT_PORT);

  bool connected = false;
  if (strlen(MQTT_USER) > 0) {
    connected = mqttClient.connect(clientId.c_str(), MQTT_USER, MQTT_PASS);
  } else {
    connected = mqttClient.connect(clientId.c_str());
  }

  if (connected) {
    Serial.println("[MQTT SUCCESS] Terhubung ke Broker!");
    String cmdTopic = "trashily/piru/" + String(PIRU_DEVICE_ID) + "/servo/command";
    String sysCmdTopic = "trashily/piru/" + String(PIRU_DEVICE_ID) + "/command";
    mqttClient.subscribe(cmdTopic.c_str());
    mqttClient.subscribe(sysCmdTopic.c_str());
    Serial.printf("  -> Subscribed: %s\n", cmdTopic.c_str());
  } else {
    Serial.printf("[MQTT FAILED] rc=%d. Mencoba lagi nanti.\n", mqttClient.state());
  }
}

void mqttCallback(char* topic, byte* payload, unsigned int length) {
  char msg[256];
  if (length >= sizeof(msg)) length = sizeof(msg) - 1;
  memcpy(msg, payload, length);
  msg[length] = '\0';

  Serial.printf("[MQTT RX] [%s] %s\n", topic, msg);

  String topicStr = String(topic);
  if (topicStr.endsWith("/servo/command")) {
    // Parse verdict / action dari MQTT
    // Format payload JSON: {"action":"OPEN","tray":1,"verdict":"VALID_ORG","item_name":"banana"}
    if (strstr(msg, "\"VALID_ORG\"") || (strstr(msg, "\"OPEN\"") && strstr(msg, "\"tray\":1"))) {
      mqtt_incoming_verdict = "VALID_ORG";
      mqtt_response_received = true;
    } else if (strstr(msg, "\"VALID_ANORG\"") || (strstr(msg, "\"OPEN\"") && strstr(msg, "\"tray\":2"))) {
      mqtt_incoming_verdict = "VALID_ANORG";
      mqtt_response_received = true;
    } else if (strstr(msg, "\"NO_OBJECT\"")) {
      mqtt_incoming_verdict = "NO_OBJECT";
      mqtt_response_received = true;
    } else if (strstr(msg, "\"INVALID\"") || strstr(msg, "\"REJECT\"")) {
      mqtt_incoming_verdict = "INVALID";
      mqtt_response_received = true;
    }
  } else if (topicStr.endsWith("/command")) {
    if (strstr(msg, "TARE")) {
      tareAllScales();
    } else if (strstr(msg, "STATUS")) {
      printSensorReport();
    }
  }
}

void publishTelemetry() {
  if (!mqttClient.connected()) return;
  String topic = "trashily/piru/" + String(PIRU_DEVICE_ID) + "/telemetry";
  char payload[128];
  snprintf(payload, sizeof(payload),
           "{\"device_id\":\"%s\",\"dist_org_cm\":%.1f,\"dist_anorg_cm\":%.1f,\"org_full\":%s,\"anorg_full\":%s}",
           PIRU_DEVICE_ID, dist_org_cm, dist_anorg_cm,
           is_org_full ? "true" : "false",
           is_anorg_full ? "true" : "false");
  mqttClient.publish(topic.c_str(), payload);
}
#endif

// ============================================================================
// FUNGSI LOGIKA DETEKSI & INTERAKSI
// ============================================================================

void processTrayAction(int tray_num) {
  // A. Periksa kapasitas tong
  if (tray_num == 1 && is_org_full) {
    Serial.println("{\"event\":\"WARNING\",\"message\":\"Tong Organik Penuh! Pembukaan dikunci.\"}");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Tong ORGANIK");
    lcd.setCursor(0, 1);
    lcd.print("SUDAH PENUH!    ");
    beepBuzzer(400, 2, 100);
    delay(2000);
    return;
  }
  if (tray_num == 2 && is_anorg_full) {
    Serial.println("{\"event\":\"WARNING\",\"message\":\"Tong Anorganik Penuh! Pembukaan dikunci.\"}");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Tong ANORGANIK");
    lcd.setCursor(0, 1);
    lcd.print("SUDAH PENUH!    ");
    beepBuzzer(400, 2, 100);
    delay(2000);
    return;
  }

  beepBuzzer(80);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(tray_num == 1 ? "Baki 1: Organik " : "Baki 2: Anorgan ");
  lcd.setCursor(0, 1);
  lcd.print("Memindai YOLO...");

  // Kirim trigger via Serial USB
  Serial.printf("{\"event\":\"SCAN_REQ\",\"tray\":%d}\n", tray_num);

  // Kirim trigger via MQTT Broker
#if USE_WIFI_MQTT
  mqtt_response_received = false;
  mqtt_incoming_verdict = "";
  if (mqttClient.connected()) {
    String triggerTopic = "trashily/piru/" + String(PIRU_DEVICE_ID) + "/tray/trigger";
    char trigPayload[64];
    snprintf(trigPayload, sizeof(trigPayload), "{\"tray\":%d}", tray_num);
    mqttClient.publish(triggerTopic.c_str(), trigPayload);
    Serial.printf("[MQTT TX] [%s] %s\n", triggerTopic.c_str(), trigPayload);
  }
#endif

  // Tunggu balasan dari Serial ATAU MQTT
  unsigned long start_wait = millis();
  bool response_received = false;
  String verdict = "";

  while (millis() - start_wait < YOLO_TIMEOUT_MS) {
#if USE_WIFI_MQTT
    if (mqttClient.connected()) {
      mqttClient.loop();
      if (mqtt_response_received) {
        verdict = mqtt_incoming_verdict;
        response_received = true;
        break;
      }
    }
#endif

    if (Serial.available() > 0) {
      verdict = Serial.readStringUntil('\n');
      verdict.trim();
      if (verdict.length() > 0) {
        response_received = true;
        break;
      }
    }
    delay(10);
  }

  if (!response_received) {
    Serial.println("{\"event\":\"ERROR\",\"status\":\"TIMEOUT\",\"message\":\"Tidak ada respons dari YOLO\"}");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Server Timeout! ");
    lcd.setCursor(0, 1);
    lcd.print("Coba Lagi...    ");
    beepBuzzer(150, 2, 100);
    delay(2000);
    return;
  }

  Serial.printf("[YOLO RESP] Diterima: %s\n", verdict.c_str());

  if (tray_num == 1 && verdict.equalsIgnoreCase("VALID_ORG")) {
    handleValidWaste(1, "ORGANIK");
  } else if (tray_num == 2 && verdict.equalsIgnoreCase("VALID_ANORG")) {
    handleValidWaste(2, "ANORGANIK");
  } else if (verdict.equalsIgnoreCase("NO_OBJECT")) {
    handleNoObjectDetected();
  } else {
    handleInvalidWaste();
  }
}

void handleValidWaste(int tray_num, const char* category_name) {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Valid: ");
  lcd.print(category_name);
  lcd.setCursor(0, 1);
  lcd.print("Membuka Baki... ");

#if USE_LOADCELL
  HX711& target_scale = (tray_num == 1) ? scale1 : scale2;
  float baseline_weight = 0.0f;
  if (target_scale.is_ready()) {
    baseline_weight = target_scale.get_units(3);
    if (baseline_weight < 0) baseline_weight = 0.0f;
  }
#endif

  // Buka Servo
  if (tray_num == 1) {
    servo1_org.write(SERVO_POS_OPEN);
    delay(SERVO_OPEN_DURATION_MS);
    servo1_org.write(SERVO_POS_CLOSED);
  } else {
    servo2_anorg.write(SERVO_POS_OPEN);
    delay(SERVO_OPEN_DURATION_MS);
    servo2_anorg.write(SERVO_POS_CLOSED);
  }

#if USE_LOADCELL
  lcd.setCursor(0, 1);
  lcd.print("Menimbang...    ");
  delay(WEIGHT_SETTLE_DELAY_MS);

  float final_weight = baseline_weight;
  if (target_scale.is_ready()) {
    final_weight = target_scale.get_units(7);
    if (final_weight < 0) final_weight = 0.0f;
  }

  float delta_weight = final_weight - baseline_weight;
  if (delta_weight < 0) delta_weight = 0.0f;

  if (tray_num == 1) {
    current_weight_org = final_weight;
  } else {
    current_weight_anorg = final_weight;
  }
#else
  float delta_weight = DUMMY_WEIGHT_GRAM;
  if (tray_num == 1) {
    current_weight_org += delta_weight;
  } else {
    current_weight_anorg += delta_weight;
  }
  float final_weight = (tray_num == 1) ? current_weight_org : current_weight_anorg;
#endif

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(category_name);
  lcd.print(" Sukses!");
  lcd.setCursor(0, 1);
#if USE_LOADCELL
  lcd.print("+");
  lcd.print(delta_weight, 1);
  lcd.print("g (Tot:");
  lcd.print((int)final_weight);
  lcd.print("g)");
#else
  lcd.print(tray_num == 1 ? "+1 Item (+10 P) " : "+1 Item (+15 P) ");
#endif

  beepBuzzer(100, 2, 80);

  // Kirim data transaksi via Serial
  char jsonBuf[128];
  snprintf(jsonBuf, sizeof(jsonBuf),
           "{\"event\":\"WEIGHT_DATA\",\"tray\":%d,\"category\":\"%s\",\"weight_gr\":%.2f,\"total_gr\":%.2f}",
           tray_num, category_name, delta_weight, final_weight);
  Serial.println(jsonBuf);

  // Kirim data transaksi via MQTT Broker
#if USE_WIFI_MQTT
  if (mqttClient.connected()) {
    String weightTopic = "trashily/piru/" + String(PIRU_DEVICE_ID) + "/weight/data";
    mqttClient.publish(weightTopic.c_str(), jsonBuf);
    Serial.printf("[MQTT TX] [%s] %s\n", weightTopic.c_str(), jsonBuf);
  }
#endif

  delay(2500);
}

void handleInvalidWaste() {
  servo1_org.write(SERVO_POS_CLOSED);
  servo2_anorg.write(SERVO_POS_CLOSED);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Kategori Salah! ");
  lcd.setCursor(0, 1);
  lcd.print("Salah Baki! :(  ");

  Serial.println("{\"event\":\"VERDICT\",\"status\":\"INVALID\",\"message\":\"Kategori sampah tidak cocok dengan baki yang dipilih.\"}");
  beepBuzzer(350, 3, 120);
  delay(2500);
}

void handleNoObjectDetected() {
  servo1_org.write(SERVO_POS_CLOSED);
  servo2_anorg.write(SERVO_POS_CLOSED);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Tidak Terdeteksi");
  lcd.setCursor(0, 1);
  lcd.print("Arahkan Sampah! ");

  Serial.println("{\"event\":\"VERDICT\",\"status\":\"NO_OBJECT\",\"message\":\"Kamera belum melihat sampah.\"}");
  beepBuzzer(80);
  delay(1800);
}

void beepBuzzer(int duration_ms, int count, int pause_ms) {
  for (int i = 0; i < count; i++) {
    digitalWrite(PIN_BUZZER, HIGH);
    delay(duration_ms);
    digitalWrite(PIN_BUZZER, LOW);
    if (i < count - 1) delay(pause_ms);
  }
}

float readUltrasonicCM(uint8_t trigPin, uint8_t echoPin) {
  digitalWrite(trigPin, LOW);
  delayMicroseconds(4);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(20);
  digitalWrite(trigPin, LOW);

  long duration = pulseIn(echoPin, HIGH, 30000);
  if (duration == 0) return DIST_MAX_VALID_CM;

  float distance_cm = (duration * 0.0343f) / 2.0f;
  if (distance_cm < DIST_MIN_VALID_CM) distance_cm = DIST_MIN_VALID_CM;
  else if (distance_cm > DIST_MAX_VALID_CM) distance_cm = DIST_MAX_VALID_CM;
  return distance_cm;
}

void updateBinCapacity() {
  dist_org_cm   = readUltrasonicCM(PIN_US1_TRIG, PIN_US1_ECHO);
  delay(30);
  dist_anorg_cm = readUltrasonicCM(PIN_US2_TRIG, PIN_US2_ECHO);

  is_org_full   = (dist_org_cm < DIST_FULL_THRESHOLD_CM);
  is_anorg_full = (dist_anorg_cm < DIST_FULL_THRESHOLD_CM);

#if USE_WIFI_MQTT
  publishTelemetry();
#endif
}

void updateStandbyLCD() {
  lcd.setCursor(0, 0);
  char line1[17];
  snprintf(line1, sizeof(line1), "O:%-5s A:%-5s", 
           is_org_full ? "PENUH" : "READY", 
           is_anorg_full ? "PENUH" : "READY");
  lcd.print(line1);

  lcd.setCursor(0, 1);
  int ir1 = digitalRead(PIN_IR_TRAY1_ORG);
  int ir2 = digitalRead(PIN_IR_TRAY2_ANORG);
  bool ir1_trig = (ir1 == IR_TRIGGER_STATE);
  bool ir2_trig = (ir2 == IR_TRIGGER_STATE);

  char line2[17];
  if (is_org_full && is_anorg_full) {
    snprintf(line2, sizeof(line2), "SEMUA TONG PENUH");
  } else if (ir1_trig && ir2_trig) {
    snprintf(line2, sizeof(line2), "IR:%d,%d TERHALANG", ir1, ir2);
  } else if (ir1_trig) {
    snprintf(line2, sizeof(line2), "IR1:%d TERHALANG ", ir1);
  } else if (ir2_trig) {
    snprintf(line2, sizeof(line2), "IR2:%d TERHALANG ", ir2);
  } else {
#if USE_WIFI_MQTT
    snprintf(line2, sizeof(line2), "%s M:%s", 
             WiFi.status() == WL_CONNECTED ? "WF:OK" : "WF:NO", 
             mqttClient.connected() ? "OK" : "NO");
#else
    snprintf(line2, sizeof(line2), "IR:%d,%d Siap... ", ir1, ir2);
#endif
  }
  lcd.print(line2);
}

void tareAllScales() {
#if USE_LOADCELL
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Kalibrasi Berat ");
  lcd.setCursor(0, 1);
  lcd.print("Tare Sensor...  ");

  if (scale1.is_ready()) scale1.tare();
  if (scale2.is_ready()) scale2.tare();

  current_weight_org = 0.0f;
  current_weight_anorg = 0.0f;
  Serial.println("[LOADCELL] Tare kedua load cell selesai (0.00 g).");
  delay(600);
#else
  current_weight_org = 0.0f;
  current_weight_anorg = 0.0f;
  Serial.println("[LOADCELL] Tare reset bobot simulasi selesai (0.00 g).");
#endif
}

void printSensorReport() {
  updateBinCapacity();
#if USE_LOADCELL
  float w1 = scale1.is_ready() ? scale1.get_units(5) : -1.0f;
  float w2 = scale2.is_ready() ? scale2.get_units(5) : -1.0f;
#endif
  int ir1 = digitalRead(PIN_IR_TRAY1_ORG);
  int ir2 = digitalRead(PIN_IR_TRAY2_ANORG);

  Serial.println("\n--- [LAPORAN STATUS SENSOR PIRU] ---");
  Serial.printf("IR Tray 1 (Org)   : %s (Raw Pin: %d | Level Trigger: %d)\n", 
                ir1 == IR_TRIGGER_STATE ? "TERHALANG" : "KOSONG", ir1, IR_TRIGGER_STATE);
  Serial.printf("IR Tray 2 (Anorg) : %s (Raw Pin: %d | Level Trigger: %d)\n", 
                ir2 == IR_TRIGGER_STATE ? "TERHALANG" : "KOSONG", ir2, IR_TRIGGER_STATE);
  Serial.printf("Ultrasonik 1 (Org): %.1f cm -> Status: %s\n", dist_org_cm, is_org_full ? "PENUH" : "READY");
  Serial.printf("Ultrasonik 2 (An) : %.1f cm -> Status: %s\n", dist_anorg_cm, is_anorg_full ? "PENUH" : "READY");
#if USE_LOADCELL
  Serial.printf("Load Cell 1 (Org) : %.2f gram\n", w1);
  Serial.printf("Load Cell 2 (An)  : %.2f gram\n", w2);
#else
  Serial.printf("Load Cell Status  : NON-AKTIF (USE_LOADCELL = false)\n");
  Serial.printf("Bobot Sim. 1 (Org): %.2f gram\n", current_weight_org);
  Serial.printf("Bobot Sim. 2 (An) : %.2f gram\n", current_weight_anorg);
#endif
#if USE_WIFI_MQTT
  Serial.printf("WiFi Status       : %s (IP: %s)\n", WiFi.status() == WL_CONNECTED ? "CONNECTED" : "DISCONNECTED", WiFi.localIP().toString().c_str());
  Serial.printf("MQTT Status       : %s (Broker: %s:%d)\n", mqttClient.connected() ? "CONNECTED" : "DISCONNECTED", MQTT_SERVER, MQTT_PORT);
#endif
  Serial.println("------------------------------------\n");
}

void handleManualSerialInput(char cmd) {
  switch (cmd) {
    case '1':
      Serial.println("[MANUAL TEST] Simulasi Valid Organik (Baki 1)...");
      handleValidWaste(1, "ORGANIK");
      break;
    case '2':
      Serial.println("[MANUAL TEST] Simulasi Valid Anorganik (Baki 2)...");
      handleValidWaste(2, "ANORGANIK");
      break;
    case 'x':
    case 'X':
      Serial.println("[MANUAL TEST] Simulasi Salah Baki (Invalid)...");
      handleInvalidWaste();
      break;
    case 't':
    case 'T':
      Serial.println("[MANUAL TEST] Melakukan Tare Load Cell...");
      tareAllScales();
      break;
    case 's':
    case 'S':
      printSensorReport();
      break;
    case '\r':
    case '\n':
    case ' ':
      break;
    default:
      Serial.printf("[MANUAL TEST] Karakter tidak dikenali: '%c'. Gunakan '1','2','x','t','s'\n", cmd);
      break;
  }
}
