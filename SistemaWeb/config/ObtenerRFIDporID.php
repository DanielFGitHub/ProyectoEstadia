<?php
// Verificamos si el parámetro "employee_id" está presente en la URL
if (!isset($_GET["employee_id"])) {
    exit("employee_id is not present"); // Si no está presente, terminamos la ejecución y mostramos un mensaje de error
}

// Incluimos el archivo functions.php, que contiene nuestras funciones
include_once "Funciones.php";

// Obtenemos el número de serie RFID del usuario llamando a la función getEmployeeRfidById con el ID del usuario como argumento
$employee = getUsuarioconRFIDporID($_GET["employee_id"]);

// Inicializamos una variable $serial
$serial = "";

// Verificamos si se encontró el usuario y si tiene un número de serie RFID asignado
if ($employee) {
    $serial = $employee->RFID; // Si se encontró el usuario, almacenamos su número de serie RFID en la variable $serial
}

// Convertimos el número de serie RFID a formato JSON y lo mostramos en la salida
echo json_encode($serial); 