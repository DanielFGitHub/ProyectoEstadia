<?php
  //Verificamos la sesion
  include './config/funcionalidades/sesion.php';
  //Accedemos al navbar
  include './templates/navbarAdmin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Seleccionar Fechas</title>
</head>
<body class='body'>
<div class="container mt-5 ">
    <h2 class="mb-4 text-center">Por favor seleccione las fechas</h2>
    <a href="./historial.php">
        <button class="btn btn-secondary mb-2">
        <i class="fa-solid fa-arrow-left-long"></i>
        Regresar
        </button>
    </a>
    <div class="card-header text-center"></div>
        <div class="card-body">
            <form class="formulario_fondo" action="./config/funcionalidades/descargarHistorial.php" method="post">
                <div class="form-group">
                    <label for="nombre">Fecha inicial</label>
                    <input id="fechainicio" type="date" class="form-control" name="fechainicio" required>
                </div>
                <div class="form-group">
                    <label for="apellido_paterno">Fecha final</label>
                    <input id="fechafinal" type="date" class="form-control" name="fechafinal" required>
                </div>
                <button type="submit" class="btn btn-primary ">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Descargar
                </button>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>