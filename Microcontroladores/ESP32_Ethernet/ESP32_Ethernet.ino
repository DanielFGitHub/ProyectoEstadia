#include <Wire.h>
#include <SPI.h>
#include <UIPEthernet.h>
#include <WiFi.h> // Biblioteca para conexión WiFi
#include <Adafruit_NeoPixel.h>
#include <Adafruit_PN532.h>

// Definición de pines
#define BTN 14
#define PIN_SPI_CS 15
#define PIN_SPI_MOSI 5
#define PIN_SPI_MISO 18
#define PIN_SPI_SCK 19
#define PIN_LED 26
#define NUMPIXELS 11
#define DELAYTIME 200

// Configuración de red
#define SSID "Nombre_de_la_red_wifi"    // Red wifi a la que se conectara la placa    
#define PASS "contraseña_de_la_red_wifi"   // password de la red wifi
const char* host = "slaps.000webhostapp.com"; //ejemplo de nombre de host
const uint16_t port = 80; //ejemplo de puerto 
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };

// Inicialización de objetos
Adafruit_NeoPixel pixels(NUMPIXELS, PIN_LED, NEO_GRB + NEO_KHZ800);
Adafruit_PN532 nfc(21, 22); // I2C SDA 21, SCL 22
EthernetClient client;

// Variables globales
String serial = "";

void setup() {
  pinMode(BTN, INPUT);

  pixels.begin();
  pixels.show();

  // Inicializa el SPI y Ethernet
  SPI.begin(PIN_SPI_SCK, PIN_SPI_MISO, PIN_SPI_MOSI, PIN_SPI_CS);

  if (digitalRead(BTN) == LOW) {
    conexionWiFi();
  } else {
    conexionEthernet();
  }

  iniciarPN532();
}

void loop() {
  boolean leeTarjeta;
  uint8_t uid[] = { 0, 0, 0, 0, 0, 0, 0 };
  uint8_t longitudUID;

  setAllPixelsColor(100, 100, 100); // Enciende todos los LEDs de color blanco
  pixels.show(); // Actualiza la tira LED para mostrar el cambio

  leeTarjeta = nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, &uid[0], &longitudUID);

  if (leeTarjeta) {
    Serial.println("Tarjeta encontrada!");

    setAllPixelsColor(0, 100, 100); // Enciende todos los LEDs de color blanco
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    
    String serial = "";
    for (uint8_t i = 0; i < longitudUID; i++) {
      // Imprimir el UID en formato hexadecimal con separadores
      if (uid[i] < 0x10) {
        serial += "0";
      }
      serial += String(uid[i], HEX);
      if (i != longitudUID - 1) {
        serial += "-";
      }
    }
    // Convertir a mayúsculas
    serial.toUpperCase();
    
    Serial.print("Valor del UID: ");
    Serial.println(serial); // Mostrar el UID formateado

    if (WiFi.status() == WL_CONNECTED) {
      enviarDatosWiFi(serial);
    } else if (Ethernet.linkStatus() == LinkON) {
      enviarDatosEthernet(serial);
    }

    // Esperar un momento para evitar múltiples envíos rápidos
    //delay(2000);
  } else {
    Serial.println("Se agotó el tiempo de espera de una tarjeta");
  }
}


void conexionWiFi() {
  Serial.begin(115200);

  Serial.println("Conectando a WiFi");
  WiFi.mode(WIFI_STA);
  WiFi.begin(SSID, PASS);

  uint8_t ledEncendido = NUMPIXELS - 1; // Inicializar en el último LED
  while (WiFi.status() != WL_CONNECTED) {
    setAllPixelsOff();
    Serial.print(".");
    pixels.setPixelColor(ledEncendido, pixels.Color(255, 255, 0)); // Amarillo
    pixels.show();
    delay(DELAYTIME);
    ledEncendido--;
    if (ledEncendido < 0) {
      ledEncendido = NUMPIXELS - 1;
    }
  }

  setAllPixelsColor(0, 0, 255); // Azul
  pixels.show();
  delay(1000);
  setAllPixelsOff();
  pixels.show();

  Serial.println("WiFi conectado");
  Serial.print("Dirección IP: ");
  Serial.println(WiFi.localIP());
}

void conexionEthernet() {
  setAllPixelsOff();

  Ethernet.init(PIN_SPI_CS);
  Serial.begin(115200);

  Serial.println("Iniciando Ethernet...");

  uint8_t ledEncendido = 11; // 0 - 255
  while (Ethernet.linkStatus() != LinkON) { // Mientras se conecta un cable
    setAllPixelsOff(); // Apaga todos los LEDs
    Serial.print(".");
    pixels.setPixelColor(ledEncendido, pixels.Color(255, 50, 0)); // Enciende el LED actual de color naranja
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    delay(DELAYTIME);
    ledEncendido -= 1;
    if(ledEncendido<=0){
      ledEncendido=11;
    }
  }

  setAllPixelsColor(255, 50, 0); // Enciende todos los LEDs de color naranja
  pixels.show(); // Actualiza la tira LED para mostrar el cambio


  if (Ethernet.begin(mac) == 0) {
    Serial.println("Error al obtener una IP con DHCP");
    Ethernet.begin(mac, IPAddress(192, 168, 1, 177));
  }

  delay(1000);

  setAllPixelsColor(0, 0, 255); // Azul
  pixels.show();
  delay(1000);
  setAllPixelsOff();
  pixels.show();

  Serial.print("Mi dirección IP es: ");
  Serial.println(Ethernet.localIP());
}

void iniciarPN532() {
  nfc.begin();
  uint32_t versiondata = nfc.getFirmwareVersion();
  if (!versiondata) {
    Serial.println("No se encontró el PN53x");
    setAllPixelsColor(255, 0, 0); // Rojo
    pixels.show();
    while (1);
  }

  Serial.print("Chip encontrado PN5");
  Serial.println((versiondata >> 24) & 0xFF, HEX);
  Serial.print("Firmware ver. ");
  Serial.print((versiondata >> 16) & 0xFF, DEC);
  Serial.print('.'); Serial.println((versiondata >> 8) & 0xFF, DEC);

  nfc.SAMConfig();
  Serial.println("Esperando tarjeta ISO14443A ...");
}

void setAllPixelsOff() {
  for (int i = 0; i < NUMPIXELS; i++) {
    pixels.setPixelColor(i, pixels.Color(0, 0, 0));
  }
  pixels.show();
}

void setAllPixelsColor(uint8_t red, uint8_t green, uint8_t blue) {
  for (int i = 0; i < NUMPIXELS; i++) {
    pixels.setPixelColor(i, pixels.Color(red, green, blue));
  }
  pixels.show();
}

void enviarDatosWiFi(String serial) {
  WiFiClient client;
  Serial.print(">>>>>> Conectando a ");
  Serial.print(host);
  Serial.print(':');
  Serial.println(port);

  if (client.connect(host, port)) {
      String url = "/configuracion/LeerRFID.php?serial="; //Ejemplo de direccion del archivo LeerRFID
    url += serial;

    Serial.println(url);

    Serial.println("[Enviando solicitud]");
    client.print(String("GET ") + url + " HTTP/1.1\r\n" +
                 "Host: " + host + "\r\n" +
                 "Connection: close\r\n\r\n"
                );

    // Esperar por datos válidos
    unsigned long timeout = millis();
    while (client.available() == 0) {
      if (millis() - timeout > 5000) {
        Serial.println(">>> ¡Tiempo de espera del cliente!");

        setAllPixelsColor(255, 0, 0); // Enciende todos los LEDs de color rojo
        pixels.show(); // Actualiza la tira LED para mostrar el cambio
        delay(1000); // Espera con los LEDs encendidos
        setAllPixelsOff(); // Apaga todos los LEDs
        pixels.show(); // Actualiza la tira LED para mostrar el cambio

        client.stop();
        return;
      }
    }

    // Leer todas las líneas del servidor
    Serial.println("Recibiendo del servidor remoto");
    String msg;
    while (client.available()) {
      char ch = static_cast<char>(client.read());
      Serial.print(ch);
      msg += ch;
    }

    Serial.println();
    if (msg.indexOf("Operacion realizada") != -1) {
      Serial.println("Datos guardados");

      setAllPixelsColor(0, 255, 0); // Enciende todos los LEDs de color verde
      pixels.show(); // Actualiza la tira LED para mostrar el cambio
      delay(1000); // Espera con los LEDs encendidos
      setAllPixelsOff(); // Apaga todos los LEDs
      pixels.show(); // Actualiza la tira LED para mostrar el cambio

    } else {
      Serial.println("No se han guardado los datos");

      setAllPixelsColor(255, 0, 0); // Enciende todos los LEDs de color rojo
      pixels.show(); // Actualiza la tira LED para mostrar el cambio
      delay(1000); // Espera con los LEDs encendidos
      setAllPixelsOff(); // Apaga todos los LEDs
      pixels.show(); // Actualiza la tira LED para mostrar el cambio
    }

    // Cerrar conexión
    Serial.println();
    Serial.println(">>>>>>>>>> Cerrando conexión");
    Serial.println();
    client.stop();
  } else {
    Serial.println("Conexión fallida");

    setAllPixelsColor(255, 0, 255); // Morado
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    delay(1000); // Espera con los LEDs encendidos
    setAllPixelsOff(); // Apaga todos los LEDs
    pixels.show(); // Actualiza la tira LED para mostrar el cambio

    Serial.print("Error de conexión: ");
    Serial.println(client.connect(host, port));
  }
}

void enviarDatosEthernet(String serial) {
  EthernetClient client;
  Serial.print(">>>>>> Conectando a ");
  Serial.print("192.168.100.8");
  Serial.print(':');
  Serial.println(80);

  if (client.connect("192.168.100.8", 80)) {
    String url = "/configuracion/LeerRFID.php?serial="; //Ejemplo de direccion del archivo LeerRFID
    url += serial;

    Serial.println(url);

    Serial.println("[Enviando solicitud]");
    client.print(String("GET ") + url + " HTTP/1.1\r\n" +
                 "Host: 192.168.100.8\r\n" +
                 "Connection: close\r\n\r\n"
                );

    // Esperar por datos válidos
    unsigned long timeout = millis();
    while (client.available() == 0) {
      if (millis() - timeout > 5000) {
        Serial.println(">>> ¡Tiempo de espera del cliente!");

        setAllPixelsColor(255, 0, 0); // Enciende todos los LEDs de color rojo
        pixels.show(); // Actualiza la tira LED para mostrar el cambio
        delay(1000); // Espera con los LEDs encendidos
        setAllPixelsOff(); // Apaga todos los LEDs
        pixels.show(); // Actualiza la tira LED para mostrar el cambio

        client.stop();
        return;
      }
    }

    // Leer todas las líneas del servidor
    Serial.println("Recibiendo del servidor remoto");
    String msg;
    while (client.available()) {
      char ch = static_cast<char>(client.read());
      Serial.print(ch);
      msg += ch;
    }

    Serial.println();
    if (msg.indexOf("Operacion realizada") != -1) {
      Serial.println("Datos guardados");

      setAllPixelsColor(0, 255, 0); // Enciende todos los LEDs de color verde
      pixels.show(); // Actualiza la tira LED para mostrar el cambio
      delay(1000); // Espera con los LEDs encendidos
      setAllPixelsOff(); // Apaga todos los LEDs
      pixels.show(); // Actualiza la tira LED para mostrar el cambio

    } else {
      Serial.println("No se han guardado los datos");

      setAllPixelsColor(255, 0, 0); // Enciende todos los LEDs de color rojo
      pixels.show(); // Actualiza la tira LED para mostrar el cambio
      delay(1000); // Espera con los LEDs encendidos
      setAllPixelsOff(); // Apaga todos los LEDs
      pixels.show(); // Actualiza la tira LED para mostrar el cambio
    }

    // Cerrar conexión
    Serial.println();
    Serial.println(">>>>>>>>>> Cerrando conexión");
    Serial.println();
    client.stop();
  } else {
    Serial.println("Conexión fallida");

    setAllPixelsColor(255, 0, 255); // Morado
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    delay(1000); // Espera con los LEDs encendidos
    setAllPixelsOff(); // Apaga todos los LEDs
    pixels.show(); // Actualiza la tira LED para mostrar el cambio

    Serial.print("Error de conexión: ");
    Serial.println(client.connect("192.168.100.8", 80));
  }
}
