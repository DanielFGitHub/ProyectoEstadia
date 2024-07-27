<?php
session_start(); // Iniciar la sesión

// Verificar si la sesión está activa
if (!isset($_SESSION['IdUsuario'])) {
    // Si no hay una sesión activa, redirigir al usuario a la página de login
    header("Location: login.php");
    exit();
}
// Verificamos si el parámetro "employee_id" está presente en la URL
if (!isset($_GET["employee_id"])) {
    exit("employee_id is required"); // Si no está presente, terminamos la ejecución y mostramos un mensaje de error
}

// Incluimos el archivo functions.php, que contiene nuestras funciones
include_once "Funciones.php";

// Obtenemos el ID del empleado de la URL
$employeeId = $_GET["employee_id"];

// Llamamos a la función setReaderForEmployeePairing, pasándole el ID del empleado
setEmparejamiento($employeeId);