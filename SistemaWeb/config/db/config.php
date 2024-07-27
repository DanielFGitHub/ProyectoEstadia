<?php 
$host = 'Host de la bd';
$dbname = 'Nombre de tu bd';
$username = 'Nombre de usuario';
$password = 'Password de la bd';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Establecer el modo de error de PDO a excepción
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Establecer el modo de búsqueda predeterminado en FETCH_ASSOC
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // No mostrar el mensaje de error al usuario final
    die("Error al conectar con la base de datos: ");
}
?>
