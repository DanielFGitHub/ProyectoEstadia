<?php
session_start(); // Iniciar la sesión

//Accedemos a la configuracion de la bd
include '../db/config.php';

//Recibimos los campos del login
$correo = $_POST['correo'];
$pass = $_POST['pass'];

//Ejecutamos la consulta SQL
$sql = "SELECT * FROM usuarios WHERE Correo = :correo AND Password = :pass";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':correo', $correo);
$stmt->bindParam(':pass', $pass);
$stmt->execute();

//Validamos qie el inicio fue exitoso
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['Rol'] == "Administrador") {
        // Inicio de sesión exitoso
        $_SESSION['IdUsuario'] = $row['IdUsuario']; // Asigna el ID del usuario a la sesión
        header("Location: ../../index.php");
        exit();
    }else{
        // Usuario no valido
        echo "<script>alert('Acceso restringido. Solo personal autorizado')</script>";
        echo "<script>window.open('../../login.php', '_self')</script>";
        exit();
    }
} else {
    // Usuario no encontrado
    echo "<script>alert('Los datos ingresados son incorrectos.')</script>";
    echo "<script>window.open('../../login.php', '_self')</script>";
    exit();
}
?>
