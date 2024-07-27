<?php
    //Verificamos la sesion
    include './config/funcionalidades/sesion.php';
    //Accedemos al navbar
    include './templates/navbar.php';
    //Accedemos a la configuracion de la bd
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="body">
    <div class="container mt-5 text-center">
        <h1 class="mb-4">Bienvenido</h1>
        <h3 class="mb-4">Seleccione una opción</h3>
        <div class="btn-group-vertical col-sm-6">
            <a href="./usuarios.php" class="btn btn-primary mb-2 ">
                <h5>
                    <i class="fas fa-users" style="margin-right:5px;"></i>
                    Lista de usuarios
                </h5>
            </a>
            <a href="./asignar.php" class="btn btn-secondary mb-2">
                <h5>
                    <i class="fas fa-id-card" style="margin-right:5px;"></i>
                    Asignar tarjeta
                </h5>
            </a>
            <a href="./historial.php" class="btn btn-info mb-2">
                <h5>
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Historial 
                </h5>
            </a>
            <a href="./config/funcionalidades/cerrarSesion.php" class="btn btn-danger mb-2">
                <h5>
                    <i class="fas fa-sign-out-alt" style="margin-right:5px;"></i>
                    Salir
                </h5>
            </a>
        </div>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
