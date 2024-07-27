<?php
// Incluimos el archivo functions.php, que contiene nuestras funciones
include_once "Funciones.php";

// Obtenemos todos los empleados llamando a la función getEmployees
$usuarios = getUsuarios();

// Convertimos el arreglo de empleados a formato JSON y lo mostramos en la salida
echo json_encode($usuarios);