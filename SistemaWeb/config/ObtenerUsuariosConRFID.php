<?php
// Incluimos el archivo functions.php, que contiene nuestras funciones
include_once "Funciones.php"; 

// Obtenemos los usuarios que tienen asignado un número de serie RFID llamando a la función getEmployeesWithRfid
$employeesWithRfid = getUsuariosConRFID();

// Convertimos el resultado a formato JSON y lo mostramos en la salida
echo json_encode($employeesWithRfid);
