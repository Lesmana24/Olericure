#include "DHT.h"
#include <WiFi.h>
#include <time.h>
#include <PubSubClient.h>
#include <WiFiManager.h>
#include <HTTPClient.h> 

#define DHTPIN 26
#define DHTTYPE DHT22
#define RELAY_PIN 14
#define BUZZER_PIN 25
#define TRIGGER_PIN 0  

DHT dht(DHTPIN, DHTTYPE);

// === KONFIGURASI LARAVEL (API) ===
String serverName = "https://unjoyfully-decrepit-dian.ngrok-free.dev/api/simpan-notif";

// === MQTT ===
const char* mqtt_server = "broker.emqx.io"; 
const int   mqtt_port = 1883;

// Topik Publish
const char* topic_suhu   = "AgroSquad/monitoring/suhu";
const char* topic_lembab = "AgroSquad/monitoring/lembab";
const char* topic_pompa  = "AgroSquad/outputpompa";

// Topik Subscribe
const char* sub_batas_suhu      = "AgroSquad/kontrol/batas_suhu";
const char* sub_batas_lembab    = "AgroSquad/kontrol/batas_lembab";
const char* sub_jadwal_mingguan = "AgroSquad/kontrol/jadwal_mingguan";
const char* sub_durasi_suhu   = "AgroSquad/kontrol/durasi_suhu";
const char* sub_durasi_jadwal = "AgroSquad/kontrol/durasi_jadwal";

WiFiClient espClient;
PubSubClient client(espClient);

// === KONFIGURASI SENSOR ===
float SUHU_THRESHOLD = 30.0;   
float LEMBAB_THRESHOLD = 60.0; 
int DURASI_SUHU_DETIK = 3;

// === JADWAL DINAMIS ===
bool JADWAL_HARI[7] = { false, false, false, false, false, false, false }; 
int  JADWAL_JAM = -1;
int  JADWAL_MENIT = -1;
int DURASI_JADWAL_DETIK = 5;

// === VARIABEL KONTROL ===
bool pompaSedangMenyala = false;
unsigned long waktuPompaAkanMati = 0;
int menitTerakhirDisiram = -1; 

// Variabel untuk mengingat alasan nyala
String pemicuTerakhir = ""; 

// === NTP ===
const long  gmtOffset_sec = 7 * 3600; 
const int   daylightOffset_sec = 0;
const char* ntpServer = "pool.ntp.org";

// Prototype
void callback(char* topic, byte* payload, unsigned int length); 
void reconnectMQTT();
void kontrolPompa(bool nyala, int durasi, String pemicu); 
void laporKeLaravel(String pesan);

// ================= SETUP =================
void setup() {
  Serial.begin(115200);
  dht.begin();
  pinMode(RELAY_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(TRIGGER_PIN, INPUT_PULLUP);

  digitalWrite(RELAY_PIN, HIGH);
  digitalWrite(BUZZER_PIN, LOW);

  Serial.println("\n=== IoT Penyiraman Pintar (Full Features) ===");

  // WiFiManager
  WiFiManager wifiManager;
  wifiManager.setConfigPortalTimeout(180);
  if (!wifiManager.autoConnect("IoT-Penyiraman-Config")) {
    Serial.println("Gagal konek WiFi, restart...");
    delay(3000);
    ESP.restart();
  }
  Serial.println("WiFi Terhubung!");

  // Setup MQTT
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback); 

  // Setup Waktu
  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer);
}

// ================= LOOP =================
void loop() {
  // 1. Cek Reset Button (WiFi)
  if (digitalRead(TRIGGER_PIN) == LOW) {
    Serial.println("Reset WiFi...");
    WiFiManager wm;
    wm.resetSettings();
    ESP.restart();
  }

  // 2. Cek MQTT
  if (!client.connected()) {
    reconnectMQTT();
  }
  client.loop(); 

  // 3. Cek Timer Pompa Mati Otomatis
  if (pompaSedangMenyala && millis() >= waktuPompaAkanMati) {
    // Parameter ke-3 kosong karena kita pakai ingatan 'pemicuTerakhir'
    kontrolPompa(false, 0, "timer_selesai"); 
  }

  // 4. LOGIKA JADWAL MINGGUAN
  struct tm timeinfo;
  if (getLocalTime(&timeinfo) && !pompaSedangMenyala) {
     if (timeinfo.tm_hour == JADWAL_JAM && timeinfo.tm_min == JADWAL_MENIT) {
        if (JADWAL_HARI[timeinfo.tm_wday] == true) {
           if (menitTerakhirDisiram != timeinfo.tm_min) {
              menitTerakhirDisiram = timeinfo.tm_min;
              Serial.println(">>> ALARM: Waktunya Menyiram Sesuai Jadwal!");
              
              // Nyalakan dengan alasan "Jadwal Otomatis"
              kontrolPompa(true, DURASI_JADWAL_DETIK, "Jadwal Otomatis");
           }
        }
     }
  }

  // 5. Logika Sensor & Kirim Data (Tiap 2 detik)
  static unsigned long lastMsg = 0;
  if (millis() - lastMsg > 2000) {
    lastMsg = millis();
    float t = dht.readTemperature();
    float h = dht.readHumidity();

    if (!isnan(t) && !isnan(h)) {
      char tempStr[8]; dtostrf(t, 1, 2, tempStr);
      client.publish(topic_suhu, tempStr);
      
      char humStr[8]; dtostrf(h, 1, 2, humStr);
      client.publish(topic_lembab, humStr);

      // Cek Kondisi: PANAS DAN KERING
      if (t > SUHU_THRESHOLD && h < LEMBAB_THRESHOLD) {
        if (!pompaSedangMenyala) {
          Serial.printf("PANAS & KERING! Suhu: %.1f, Lembab: %.1f\n", t, h);
          
          // Buat Pesan Detail
          String detailPemicu = "Kondisi Kritis (Suhu: " + String(t, 1) + "C, Lembab: " + String(h, 0) + "%)";
          
          // Nyalakan dengan alasan detail
          kontrolPompa(true, DURASI_SUHU_DETIK, detailPemicu);
        }
      }
    }
  }
}

// ================= CALLBACK MQTT =================
void callback(char* topic, byte* payload, unsigned int length) {
  String message = "";
  for (int i = 0; i < length; i++) message += (char)payload[i];
  
  Serial.print("Pesan masuk ["); Serial.print(topic); Serial.print("]: "); Serial.println(message);

  if (String(topic) == sub_batas_suhu) {
    SUHU_THRESHOLD = message.toFloat();
    tone(BUZZER_PIN, 2000, 100); 
  }

  if (String(topic) == sub_batas_lembab) {
    LEMBAB_THRESHOLD = message.toFloat();
    tone(BUZZER_PIN, 2000, 100);
  }

  if (String(topic) == sub_durasi_suhu) {
    DURASI_SUHU_DETIK = message.toInt(); // Ubah text dari MQTT jadi angka integer
    Serial.println("Durasi Suhu Diubah jadi: " + String(DURASI_SUHU_DETIK) + " detik");
    tone(BUZZER_PIN, 3000, 100); 
  }

  if (String(topic) == sub_durasi_jadwal) {
    DURASI_JADWAL_DETIK = message.toInt();
    Serial.println("Durasi Jadwal Diubah jadi: " + String(DURASI_JADWAL_DETIK) + " detik");
    tone(BUZZER_PIN, 3000, 100); 
  }

  if (String(topic) == sub_jadwal_mingguan) {
    int splitIndex = message.indexOf('#');
    if (splitIndex == -1) return; 

    String partHari = message.substring(0, splitIndex); 
    String partJam  = message.substring(splitIndex + 1); 

    int dayIndex = 0;
    for (int i = 0; i < partHari.length(); i++) {
      if (partHari[i] == ',') continue; 
      if (dayIndex < 7) {
        JADWAL_HARI[dayIndex] = (partHari[i] == '1');
        dayIndex++;
      }
    }
    int titikDua = partJam.indexOf(':');
    if (titikDua != -1) {
      JADWAL_JAM   = partJam.substring(0, titikDua).toInt();
      JADWAL_MENIT = partJam.substring(titikDua + 1).toInt();
    }
    Serial.println(">>> Jadwal Baru Tersimpan!");
    tone(BUZZER_PIN, 2000, 300); 
  }
}

// ================= KONTROL POMPA & LAPOR =================
void kontrolPompa(bool nyala, int durasi, String pemicu) {
  if (nyala) {
    pompaSedangMenyala = true;
    
    //Simpan alasan kenapa nyala ke variabel global
    pemicuTerakhir = pemicu; 
    
    waktuPompaAkanMati = millis() + (durasi * 1000UL);
    digitalWrite(RELAY_PIN, LOW); // ON
    client.publish(topic_pompa, "ON");
    tone(BUZZER_PIN, 1000);
    Serial.println("Pompa ON (" + pemicu + ")");
  } else {
    pompaSedangMenyala = false;
    digitalWrite(RELAY_PIN, HIGH); // OFF
    client.publish(topic_pompa, "OFF");
    noTone(BUZZER_PIN);
    Serial.println("Pompa OFF");

    // Saat mati, laporkan 'pemicuTerakhir' (bukan timer_selesai)
    String pesanLaporan = "Penyiraman selesai! Pemicu: " + pemicuTerakhir;
    laporKeLaravel(pesanLaporan);
  }
}

// ================= HTTP REQUEST KE LARAVEL =================
void laporKeLaravel(String pesan) {
  if(WiFi.status() == WL_CONNECTED){
    HTTPClient http;
    
    // Mulai koneksi ke URL Ngrok
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    
    // Kirim Data POST (message=....)
    String httpRequestData = "message=" + pesan;
    
    Serial.print("Melapor ke Laravel: ");
    int httpResponseCode = http.POST(httpRequestData);
    
    if (httpResponseCode > 0) {
      Serial.print("SUKSES! Code: ");
      Serial.println(httpResponseCode);
    } else {
      Serial.print("GAGAL. Error: ");
      Serial.println(httpResponseCode);
    }
    http.end();
  } else {
    Serial.println("WiFi Putus, tidak bisa lapor.");
  }
}

void reconnectMQTT() {
  while (!client.connected()) {
    Serial.print("Konek MQTT...");
    String clientId = "ESP32Client-" + String(random(0xffff), HEX);
    if (client.connect(clientId.c_str())) {
      Serial.println("Berhasil!");
      client.subscribe(sub_batas_suhu);
      client.subscribe(sub_batas_lembab);
      client.subscribe(sub_jadwal_mingguan); 
      client.subscribe(sub_durasi_suhu);
      client.subscribe(sub_durasi_jadwal);
    } else {
      Serial.print("Gagal rc=");
      Serial.print(client.state());
      delay(5000);
    }
  }
}