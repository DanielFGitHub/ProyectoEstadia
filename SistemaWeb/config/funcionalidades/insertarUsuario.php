<?php
    require './sesion.php';
    include '../db/config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST['nombre'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $matricula = $_POST['matricula'];
        $correo = $_POST['correo'];
        $password = $_POST['password'];
        $rol = $_POST['rol'];

        try {
            $sql = "INSERT INTO usuarios (Nombre, ApPat, ApMat, Matricula, Correo, Password, Rol) 
                VALUES (:nombre, :apellido_paterno, :apellido_materno, :matricula, :correo, :password, :rol)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido_paterno', $apellido_paterno);
            $stmt->bindParam(':apellido_materno', $apellido_materno);
            $stmt->bindParam(':matricula', $matricula);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':rol', $rol);
            $stmt->execute();
            header("Location: ../../usuarios.php");
            echo "<script>alert('Usuario insertado correctamente.')</script>";
        } catch (PDOException $e) {
            echo "Error al registrar usuario: " . $e->getMessage();
        }
    }
?>
