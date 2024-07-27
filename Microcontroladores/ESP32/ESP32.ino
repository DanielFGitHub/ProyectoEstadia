#include <Wire.h>
#include <SPI.h>
#include <WiFi.h>
#include <UNIT_PN532.h>
#include <Adafruit_NeoPixel.h>

#define SSID "Nombre_de_la_red_wifi"    // Red wifi a la que se conectara la placa    
#define PASS "contraseña_de_la_red_wifi"   // password de la red wifi
const char* ssid = SSID;
const char* password = PASS;
const char* host = "slaps.000webhostapp.com"; //ejemplo de nombre de host
const uint16_t port = 80; //ejemplo de puerto 

#define PIN  2        // Pin donde está conectado el pin de datos de la tira LED
#define NUMPIXELS 11   // Número de LEDs en la tira
#define DELAYTIME 200  // Tiempo en milisegundos que cada LED permanece encendido
#define BLINKCOUNT 3   // Número de parpadeos al final
Adafruit_NeoPixel pixels(NUMPIXELS, PIN, NEO_GRB + NEO_KHZ800);

#define PN532_SCK  (18)
#define PN532_MOSI (23)
#define PN532_SS   (5)
#define PN532_MISO (19)

UNIT_PN532 nfc(PN532_SS);
String serial = "";

void setup() {
  pixels.begin(); // Inicializa la tira LED
  pixels.show();  // Asegúrate de que todos los LEDs estén apagados al inicio

  Serial.begin(115200);
  nfc.begin();

  uint32_t versiondata = nfc.getFirmwareVersion();

  if (!versiondata) {
    Serial.print("No se encontró la placa PN53x");
    while (1);
  }
  Serial.print("Chip encontrado PN5");
  Serial.println((versiondata >> 24) & 0xFF, HEX);
  Serial.print("Firmware ver. ");
  Serial.print((versiondata >> 16) & 0xFF, DEC);
  Serial.print('.'); Serial.println((versiondata >> 8) & 0xFF, DEC);

  nfc.setPassiveActivationRetries(0xFF);
  nfc.SAMConfig();

  setAllPixelsOff();

  Serial.println("Connecting to ");
  Serial.println(ssid);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  int ledEncendido = 0;
  while(WiFi.status() != WL_CONNECTED){
    setAllPixelsOff(); // Apaga todos los LEDs
    Serial.print(".");
    pixels.setPixelColor(ledEncendido, pixels.Color(255, 255, 0)); // Enciende el LED actual de color amarillo
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    delay(DELAYTIME);
    ledEncendido += 1;
    if(ledEncendido>=11){
      ledEncendido=0;
    }
  }
  

  setAllPixelsColor(0, 0, 255); // Enciende todos los LEDs de color azul
  pixels.show(); // Actualiza la tira LED para mostrar el cambio
  delay(1000); // Espera con los LEDs encendidos
  setAllPixelsOff(); // Apaga todos los LEDs
  pixels.show(); // Actualiza la tira LED para mostrar el cambio

  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP Address: ");
  Serial.println(WiFi.localIP());

  Serial.println("Esperando una tarjeta ISO14443A ...");
}

void loop() {
  boolean LeeTarjeta;
  uint8_t uid[] = { 0, 0, 0, 0, 0, 0, 0 };
  uint8_t LongitudUID;

   setAllPixelsColor(100, 100, 100); // Enciende todos los LEDs de color blanco
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    
  LeeTarjeta = nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, &uid[0], &LongitudUID);

  if (LeeTarjeta) {
    Serial.println("Tarjeta encontrada!");
    // Serial.print("Longitud del UID: ");
    // Serial.print(LongitudUID, DEC);
    // Serial.println(" bytes");
    
    serial = "";
    for (uint8_t i = 0; i < LongitudUID; i++) {
      // Imprimir el UID en formato hexadecimal con separadores
      if (uid[i] < 0x10) {
        serial += "0";
      }
      serial += String(uid[i], HEX);
      if (i != LongitudUID - 1) {
        serial += "-";
      }
    }
    // Convertir a mayúsculas
    serial.toUpperCase();
    
    Serial.print("Valor del UID: ");
    Serial.println(serial); // Mostrar el UID formateado

    EnviarDatosAlLocalhost();

    delay(1000);
  }
  else {
    Serial.println("Se agotó el tiempo de espera de una tarjeta");
  }
}


void EnviarDatosAlLocalhost(){
  Serial.print(">>>>>> Conectando a ");
  Serial.print(host);
  Serial.print(':');
  Serial.println(port);

  Serial.println("Estado de la conexión WiFi antes de la conexión:");
  Serial.print("WiFi.status(): ");
  Serial.println(WiFi.status());
  Serial.print("WiFi.localIP(): ");
  Serial.println(WiFi.localIP());

  WiFiClient client;
  if (!client.connect(host, port)) {
    Serial.println("Conexión fallida");

    setAllPixelsColor(255, 0, 255);
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    delay(1000); // Espera con los LEDs encendidos
    setAllPixelsOff(); // Apaga todos los LEDs
    pixels.show(); // Actualiza la tira LED para mostrar el cambio

    Serial.print("Error de conexión: ");
    Serial.println(client.connect(host, port));
    Serial.println("Estado de la conexión WiFi después de la conexión fallida:");
    Serial.print("WiFi.status(): ");
    Serial.println(WiFi.status());
    Serial.print("WiFi.localIP(): ");
    Serial.println(WiFi.localIP());
    Serial.println("Esperando 5 segundos antes de volver a intentar...");
    delay(5000);
    return;
  }

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
          
          setAllPixelsColor(255, 0, 0); // Enciende todos los LEDs de color amarillo
          pixels.show(); // Actualiza la tira LED para mostrar el cambio
          delay(1000); // Espera con los LEDs encendidos
          setAllPixelsOff(); // Apaga todos los LEDs
          pixels.show(); // Actualiza la tira LED para mostrar el cambio

      client.stop();
      delay(3000);
      return;
    }
  }

  // Leer todas las líneas del servidor
  Serial.println("Recibiendo del servidor remoto");
  String msg;
  while(client.available()){
    char ch = static_cast<char>(client.read());
    Serial.print(ch);
    msg += ch;
  }

  Serial.println();
  if (msg.indexOf("Operacion realizada") != -1) {
    Serial.println("Datos guardados");

    setAllPixelsColor(0, 255, 0); // Enciende todos los LEDs de color amarillo
    pixels.show(); // Actualiza la tira LED para mostrar el cambio
    delay(1000); // Espera con los LEDs encendidos
    setAllPixelsOff(); // Apaga todos los LEDs
    pixels.show(); // Actualiza la tira LED para mostrar el cambio

  } else {
    Serial.println("No se han guardado los datos");

    setAllPixelsColor(255, 0, 0); // Enciende todos los LEDs de color amarillo
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
}


void setAllPixelsOff() {
  for (int i = 0; i < NUMPIXELS; i++) {
    pixels.setPixelColor(i, pixels.Color(0, 0, 0)); // Apaga cada LED
  }
  pixels.show(); // Actualiza la tira LED para aplicar los cambios
}

void setAllPixelsColor(uint8_t red, uint8_t green, uint8_t blue) {
  for (int i = 0; i < NUMPIXELS; i++) {
    pixels.setPixelColor(i, pixels.Color(red, green, blue)); // Establece el color para cada LED
  }
}
