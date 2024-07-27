<?php
   //Accedemos a la configuracion de la bd
   require '../db/config.php';
   //Verificamos la sesion
   include './sesion.php';
    
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int) $_GET['id'];
        try {
            $sql = 'DELETE FROM usuarios WHERE IdUsuario = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
            if ($stmt->execute()) {
                header('Location: ../../usuarios.php'); // Redirige de nuevo a la lista de usuarios después de eliminar
                exit;
            } else {
                echo 'Error al eliminar el registro.';
            }
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        echo 'ID inválido.';
    }
?>

