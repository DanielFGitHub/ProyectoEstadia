#include <Wire.h>
#include <SPI.h>
#include <UIPEthernet.h>
#include <WiFi.h>
#include <WebServer.h>
#include <Preferences.h>
#include <Adafruit_NeoPixel.h>
#include <Adafruit_PN532.h>

TaskHandle_t Tarea0;

// Definición de pines
#define BTN 14
#define BUTTON_PIN 12
#define PIN_SPI_CS 15
#define PIN_SPI_MOSI 5
#define PIN_SPI_MISO 18
#define PIN_SPI_SCK 19
#define PIN_LED 26
#define NUMPIXELS 11
#define DELAYTIME 200

// Configuración de red
const char* apSSID = "ESP32_Config"; //Nombre de la red wifi
const char* apPassword = "12345678"; //Contraseña de la bd
const char* host = "midominio.com"; //Dominio del sitio web 
const uint16_t port = 80;
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };

// Inicialización de objetos
Adafruit_NeoPixel pixels(NUMPIXELS, PIN_LED, NEO_GRB + NEO_KHZ800);
Adafruit_PN532 nfc(21, 22); // I2C SDA 21, SCL 22
EthernetClient client;
WebServer server(80);
Preferences preferences;

// Variables globales
String ssid = "";
String pass = "";
volatile bool clearPreferencesFlag = false;
bool estadoConexion = false;

void setup() {
  xTaskCreatePinnedToCore(loop0, "Tarea_0", 2048, NULL, 1, &Tarea0, 0);
  
  Serial.begin(115200);
  pinMode(BTN, INPUT);
  pinMode(BUTTON_PIN, INPUT_PULLDOWN);
  attachInterrupt(digitalPinToInterrupt(BUTTON_PIN), handleButtonPress, RISING);

  pixels.begin();
  pixels.show();

  // Inicializa el SPI y Ethernet
  SPI.begin(PIN_SPI_SCK, PIN_SPI_MISO, PIN_SPI_MOSI, PIN_SPI_CS);

  preferences.begin("wifi-config", false);

  if (digitalRead(BTN) == LOW) {
    configurarWiFi();
  } else {
    conexionEthernet();
  }
  // iniciarPN532();
}

void loop() {
  if (estadoConexion) {
    boolean leeTarjeta;
    uint8_t uid[] = { 0, 0, 0, 0, 0, 0, 0 };
    uint8_t longitudUID;

    setAllPixelsColor(50, 50, 50); // Enciende todos los LEDs de color blanco
    pixels.show(); // Actualiza la tira LED para mostrar el cambio

    leeTarjeta = nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, &uid[0], &longitudUID);

    if (leeTarjeta) {
      Serial.println("Tarjeta encontrada!");

      setAllPixelsColor(0, 50, 50); // Enciende todos los LEDs de color celeste
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
  }else{
    setAllPixelsColor(50, 50, 0); // Todo Amarillo
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    //Serial.println("Ingresa los datos de red.");
    // delay(5000);
  }
}

void loop0(void *parameter) {
  while(1==1){
    server.handleClient();

    if (clearPreferencesFlag) {
      Serial.println("Button pressed, clearing preferences...");
      preferences.clear();
      delay(500);
      ESP.restart();
    }
    Serial.print("\t\t\t .");
    delay(200);
  }
}

void configurarWiFi() {
  // Verifica si hay datos guardados previamente
  ssid = preferences.getString("ssid", "");
  pass = preferences.getString("pass", "");

  if (ssid.length() > 0 && pass.length() > 0) {
    // Intenta conectarse a la red WiFi guardada
    WiFi.begin(ssid.c_str(), pass.c_str());

    Serial.print("Conectando a WiFi");
    uint8_t ledEncendido = NUMPIXELS - 1; // Inicializar en el último LED
    while (WiFi.status() != WL_CONNECTED && digitalRead(BUTTON_PIN) == LOW) {   // WiFi.status() != WL_CONNECTED && attempts < 10
      setAllPixelsOff();
      Serial.print(".");
      pixels.setPixelColor(ledEncendido, pixels.Color(50, 50, 0)); // Amarillo
      pixels.show();
      delay(DELAYTIME);
      ledEncendido--;
      if (ledEncendido == 0) {
        ledEncendido = NUMPIXELS - 1;
      }
    }
    Serial.println();

    if (WiFi.status() == WL_CONNECTED) {
      setAllPixelsColor(0, 0, 50); // Azul
      pixels.show();
      delay(1000);
      setAllPixelsOff();
      pixels.show();

      Serial.println("Conectado a WiFi");
      Serial.println("Dirección IP: ");
      Serial.println(WiFi.localIP());
      server.begin();
      Serial.println("Servidor HTTP iniciado");
      estadoConexion = true;
      iniciarPN532();
      return;  // Sale de setup si la conexión es exitosa
    }
  }

  // Si no se pudo conectar, inicia el punto de acceso
  WiFi.softAP(apSSID, apPassword);
  Serial.println("No se pudo conectar a WiFi. Configurando AP.");
  Serial.print("Dirección IP del AP: ");
  Serial.println(WiFi.softAPIP());

  // Configura el servidor web
  server.on("/", handleRoot);
  server.on("/setWiFi", HTTP_POST, handleSetWiFi);
  server.begin();
  Serial.println("Servidor HTTP iniciado en modo AP");
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

  // Cuando se conecta un cable
  setAllPixelsColor(255, 50, 0); // Naranja
  pixels.show(); // Actualiza la tira LED para mostrar el cambio


  if (Ethernet.begin(mac) == 0) {
    Serial.println("Error al obtener una IP con DHCP");
    Ethernet.begin(mac, IPAddress(192, 168, 1, 177));

    setAllPixelsColor(50, 0, 0); // Rojo
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    delay(1000);
    setAllPixelsOff(); // Apaga todos los LEDs
    delay(1000);
    setAllPixelsColor(50, 0, 0); // Rojo
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    delay(5000);
  }

  delay(1000);

  setAllPixelsColor(0, 0, 255); // Azul
  pixels.show();
  delay(1000);
  setAllPixelsOff();
  pixels.show();

  Serial.print("Mi dirección IP es: ");
  Serial.println(Ethernet.localIP());

  estadoConexion = true;
  iniciarPN532();
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
    String url = "/AsistenciasAlumnos/config/LeerRFID.php?serial="; //Ruta del archivo Leer RFID
    url += serial;

    Serial.println(url);

    client.print(String("GET ") + url + " HTTP/1.1\r\n" +
      "Host: " + host + "\r\n" +
      "Connection: close\r\n\r\n");

    // Esperar respuesta
    unsigned long timeout = millis();
    while (client.available() == 0) {
      if (millis() - timeout > 5000) {
        Serial.println(">>> ¡Tiempo de espera del cliente!");

        setAllPixelsColor(50, 0, 0); // Rojo
        pixels.show(); // Actualiza la tira LED para mostrar el cambio
        delay(1000); // Espera con los LEDs encendidos
        setAllPixelsOff(); // Apaga todos los LEDs
        pixels.show(); // Actualiza la tira LED para mostrar el cambio

        client.stop();
        return;
      }
    }

        // FUNCION SIN LEDs
    // Lee todos los bytes disponibles y los imprime en el monitor serial
    // while (client.available()) {
    //   char c = client.read();
    //   Serial.write(c);
    // }
        // FIN FUNCION SIN LEDs

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

      setAllPixelsColor(0, 50, 0); // Verde
      pixels.show(); // Actualiza la tira LED para mostrar el cambio
      delay(1000); // Espera con los LEDs encendidos
      setAllPixelsOff(); // Apaga todos los LEDs
      pixels.show(); // Actualiza la tira LED para mostrar el cambio

    } else {
      Serial.println("No se han guardado los datos");

      setAllPixelsColor(50, 0, 0); // Rojo
      pixels.show(); // Actualiza la tira LED para mostrar el cambio
      delay(1000); // Espera con los LEDs encendidos
      setAllPixelsOff(); // Apaga todos los LEDs
      pixels.show(); // Actualiza la tira LED para mostrar el cambio
    }

    // Cerrar conexión
    Serial.println();
    Serial.println(">>>>>>>>>> Cerrando conexión");
    Serial.println();
  } else {
    Serial.println("Conexión fallida");

    setAllPixelsColor(50, 0, 50); // Morado
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
  Serial.print("midominio.com"); // camabir el dominio
  Serial.print(':');
  Serial.println(80);

  if (client.connect("midominio.com", 80)) { // camabir el dominio
    String url = "/AsistenciasAlumnos/config/LeerRFID.php?serial=";  //Ruta del archivo Leer RFID
    url += serial;

    Serial.println(url);

    Serial.println("[Enviando solicitud]");
    client.print(String("GET ") + url + " HTTP/1.1\r\n" +
                 "Host: midominio.com\r\n" + // camabir el dominio
                 "Connection: close\r\n\r\n"
                );

    // Esperar por datos válidos
    unsigned long timeout = millis();
    while (client.available() == 0) {
      if (millis() - timeout > 5000) {
        Serial.println(">>> ¡Tiempo de espera del cliente!");

        setAllPixelsColor(50, 0, 0); // Enciende todos los LEDs de color rojo
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

      setAllPixelsColor(0, 50, 0); // Enciende todos los LEDs de color verde
      pixels.show(); // Actualiza la tira LED para mostrar el cambio
      delay(1000); // Espera con los LEDs encendidos
      setAllPixelsOff(); // Apaga todos los LEDs
      pixels.show(); // Actualiza la tira LED para mostrar el cambio

    } else {
      Serial.println("No se han guardado los datos");

      setAllPixelsColor(50, 0, 0); // Enciende todos los LEDs de color rojo
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

    setAllPixelsColor(50, 0, 50); // Morado
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    delay(1000); // Espera con los LEDs encendidos
    setAllPixelsOff(); // Apaga todos los LEDs
    pixels.show(); // Actualiza la tira LED para mostrar el cambio

    Serial.print("Error de conexión: ");
    Serial.println(client.connect("midominio.com", 80)); // camabir el dominio
  }
}

void handleRoot() {
  String html = "<html><style> body {background-color: #D6EAF8;color: #333;display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; } .main { width: 300px; padding: 20px; background-color: #5DADE2; border-radius: 10px; text-align: center; } .main h2 { margin-bottom: 20px; font-size: 1.5rem; } form { display: flex; flex-direction: column; gap: 15px; } label { font-weight: bold; font-size: 0.9rem; } input { padding: 10px; border-radius: 5px; border: 1px solid #2980B9; font-size: 1rem; } .button { padding: 10px; color: #5DADE2; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; font-weight: bold; }</style><body><div class='main'><h1>Configurar WiFi</h1><form action=\"/setWiFi\" method=\"POST\"><label for=\"ssid\">SSID:</label><input type=\"text\" id=\"ssid\" name=\"ssid\"><br><label for=\"pass\">Password:</label><input type=\"password\" id=\"pass\" name=\"pass\"><br><input class='button' type=\"submit\" value=\"Guardar\"></form></div></body></html>";
  server.send(200, "text/html", html);
}

void handleSetWiFi() {
  if (server.hasArg("ssid") && server.hasArg("pass")) {
    ssid = server.arg("ssid");
    pass = server.arg("pass");

    // Guardar las credenciales en la memoria flash
    preferences.putString("ssid", ssid);
    preferences.putString("pass", pass);

    server.send(200, "text/html", "<html><body><h1>Credenciales guardadas. Reiniciando...</h1></body></html>");

    delay(1000);
    ESP.restart();
  } else {
    server.send(400, "text/html", "<html><body><h1>Error: SSID o Password no recibidos</h1></body></html>");
  }
}

void handleButtonPress() {
  clearPreferencesFlag = true;
}