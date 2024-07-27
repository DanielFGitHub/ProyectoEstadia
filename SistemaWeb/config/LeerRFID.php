<?php
// Incluimos el archivo functions.php, que contiene nuestras funciones
include_once "Funciones.php";

// Verificamos si el parámetro "serial" está presente en la URL
if (!isset($_GET["serial"])) {
    exit("No hay un serial presente"); // Si no está presente, terminamos la ejecución y mostramos un mensaje de error
}

// Llamamos a la función onRfidSerialRead y le pasamos como argumento el número de serie RFID recibido desde la URL
LecturaRFID($_GET["serial"]);
