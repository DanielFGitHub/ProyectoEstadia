<?php
session_set_cookie_params([
    'lifetime' => 0 // Sesión expira al cerrar el navegador
]);

session_start(); // Iniciar la sesión

// Regenerar el ID de la sesión para prevenir ataques de fijación de sesión
if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Tiempo de expiración de la sesión
$session_timeout = 1800; // Tiempo en segundos (ej. 30 minutos)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    session_unset();
    session_destroy();
    header("Location: ../../login.php");
    exit();
}
$_SESSION['last_activity'] = time(); // Actualizar el tiempo de última actividad

// Verificar si la sesión está activa
if (!isset($_SESSION['IdUsuario'])) {
    // Si no hay una sesión activa, redirigir al usuario a la página de login
    header("Location: ../../login.php");
    exit();
}

?>