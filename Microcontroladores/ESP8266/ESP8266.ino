#include <ESP8266WiFi.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Adafruit_NeoPixel.h>

#define SSID "Nombre_de_la_red_wifi"    // Red wifi a la que se conectara la placa    
#define PASS "contraseña_de_la_red_wifi"   // password de la red wifi
const char* ssid = SSID;
const char* password = PASS;
const char* host = "slaps.000webhostapp.com"; //ejemplo de nombre de host
const uint16_t port = 80; //ejemplo de puerto 

#define PIN  D2        // Pin donde está conectado el pin de datos de la tira LED
#define NUMPIXELS 11   // Número de LEDs en la tira
#define DELAYTIME 200  // Tiempo en milisegundos que cada LED permanece encendido
#define BLINKCOUNT 3   // Número de parpadeos al final
Adafruit_NeoPixel pixels(NUMPIXELS, PIN, NEO_GRB + NEO_KHZ800);


#define RST_PIN D3
#define SS_PIN D4

MFRC522 mfrc522(SS_PIN, RST_PIN);
String serial = "";

void setup() {
  pixels.begin(); // Inicializa la tira LED
  pixels.show();  // Asegúrate de que todos los LEDs estén apagados al inicio

  Serial.begin(9600);
  while (!Serial);  // Esperar a que la conexión serial se establezca
  Serial.println("Iniciando...");
  SPI.begin();
  mfrc522.PCD_Init();
  mfrc522.PCD_SetAntennaGain(mfrc522.RxGain_max); // Aumentar la potencia de la señal
  Serial.println("MFRC522 inicializado");

  setAllPixelsOff();

  Serial.println();
  Serial.println();
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
}

void loop() {
   setAllPixelsColor(100, 100, 100); // Enciende todos los LEDs de color blanco
   pixels.show(); // Actualiza la tira LED para mostrar el cambio
  if ( ! mfrc522.PICC_IsNewCardPresent()) {
    return;
  }

  if ( ! mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  serial = "";

  Serial.println("UID: ");
  for (int x = 0; x < mfrc522.uid.size; x++)
  {
    // If it is less than 10, we add zero
    if (mfrc522.uid.uidByte[x] < 0x10)
    {
      serial += "0";
    }
    // Transform the byte to hex
    serial += String(mfrc522.uid.uidByte[x], HEX);
    // Add a hypen
    if (x + 1 != mfrc522.uid.size)
    {
      serial += "-";
    }
  }
  // Transform to uppercase
  serial.toUpperCase();
  Serial.println(serial);

   setAllPixelsColor(0, 255, 255); // Enciende todos los LEDs de color celeste
  pixels.show(); // Actualiza la tira LED para mostrar el cambio
  EnviarDatosAlLocalhost();

  mfrc522.PICC_HaltA();
  
 
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
