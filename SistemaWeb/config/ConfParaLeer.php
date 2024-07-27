<?php
session_start(); // Iniciar la sesión

// Verificar si la sesión está activa
if (!isset($_SESSION['IdUsuario'])) {
    // Si no hay una sesión activa, redirigir al usuario a la página de login
    header("Location: login.php");
    exit();
}
// Incluimos el archivo functions.php, que contiene nuestras funciones
include_once "Funciones.php";

// Llamamos a la función setReaderStatus y le pasamos como argumento "r"
setEstadoDelLector("r");
