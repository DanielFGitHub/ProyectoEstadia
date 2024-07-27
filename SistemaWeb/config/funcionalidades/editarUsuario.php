<?php
    require './sesion.php';
    include '../db/config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $matricula = $_POST['matricula'];
        $correo = $_POST['correo'];
        $password = $_POST['password'];
        $rol = $_POST['rol'];
        $exito= false;
        try {
            $sql = "UPDATE usuarios SET Nombre = :nombre, Appat = :apellido_paterno, ApMat = :apellido_materno, Matricula = :matricula, Correo = :correo, Password = :password,   Rol = :rol WHERE IdUsuario = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido_paterno', $apellido_paterno);
            $stmt->bindParam(':apellido_materno', $apellido_materno);
            $stmt->bindParam(':matricula', $matricula);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':rol', $rol);
            $stmt->execute();
            $exito= true;
        } catch (PDOException $e) {
            echo "Error al actualizar usuario: " . $e->getMessage();
        }
        if($exito == true){
            echo "<script>alert('Cambios guardados correctamente')</script>";
            header("Location: ../../usuarios.php");
            
        }
    }
?>