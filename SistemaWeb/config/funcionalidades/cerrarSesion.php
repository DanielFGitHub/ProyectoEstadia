<?php
session_start(); // Iniciar la sesión

if (isset($_SESSION['IdUsuario'])) {
    // Cerrar la sesión y eliminar todos los datos de la sesión
    session_destroy();
    // Redirigir a la página de inicio de sesión u otra página relevante
    header("Location: ../../login.php");
    exit();
  } else {
    session_destroy();
    header("Location: ../../login.php");
  }
?>
