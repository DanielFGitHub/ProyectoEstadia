<?php
// Verificamos si el parámetro "rfid_serial" está presente en la URL
if (!isset($_GET["rfid_serial"])) {
    exit("rfid_serial is not present"); // Si no está presente, terminamos la ejecución y mostramos un mensaje de error
}

// Incluimos el archivo functions.php, que contiene nuestras funciones
include_once "Funciones.php";

// Llamamos a la función removeRfidFromEmployee y le pasamos como argumento el número de serie RFID recibido desde la URL
RemoverRFIDeUsuario($_GET["rfid_serial"]);
