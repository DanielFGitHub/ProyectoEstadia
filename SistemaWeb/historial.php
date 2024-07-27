<?php
//Verificamos la sesion
include './config/funcionalidades/sesion.php';
//Accedemos al navbar
include './templates/navbarAdmin.php';
//Accedemos a la configuracion de la bd
include './config/db/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de registros</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link  rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="body">
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Historial de registros</h1>
        <h4 class="mb-4 text-center">A continuacion se muestran los registros de la ultima semana</h4>
        <?php
            include './templates/retorno.php'
        ?>
         <div class="mb-4 text-center">
                <div class="search-container">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input class="search" type="text" id="busqueda" placeholder="Buscar...">
                </div>
            </div>
        <div class="mb-3 text-center">
            <a href="./seleccionarFechas.php">
                <button class="btn btn-success">
                    <i class="fa-solid fa-download"></i>
                    Descargar Excel
                </button>
            </a>
        </div>
        <?php
        try {
            // Preparamos la sentencia sql para relizar un inner join y obtener los datos de las 2 tablas
            $sql = "SELECT IdRegistro, Fecha, HoraEntrada, HoraSalida, Nombre, ApPat, ApMat FROM `registros` INNER JOIN `usuarios` WHERE usuarios.IdUsuario = registros.IdUsuario  ORDER BY Fecha  DESC;";
            $stmt = $pdo->query($sql);
            $registros = $stmt->fetchAll();
        
            if ($registros) {
        ?>
        <table class="table table-striped" id="tabla-resultados">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Hora Entrada</th>
                    <th>Hora Salida</th>
                    <th>Nombre </th>
                </tr>
            </thead>
            <tbody>
               <?php
                foreach ($registros as $registro) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($registro['IdRegistro']) . "</td>";
                    echo "<td>" . htmlspecialchars($registro['Fecha']) . "</td>";
                    echo "<td>" . htmlspecialchars($registro['HoraEntrada']) . "</td>";
                    echo "<td>" . htmlspecialchars($registro['HoraSalida']) . "</td>";
                    echo "<td>" . htmlspecialchars($registro['Nombre']) ." ".  htmlspecialchars($registro['ApPat']) . " " .htmlspecialchars($registro['ApMat']) ."</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <?php
            } else {
                echo "No hay usuarios registrados.";
            }
        } catch (PDOException $e) {
            echo "Error al obtener usuarios: " . $e->getMessage();
        }
        ?>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#busqueda').on('input', function() {
                var termino = $(this).val();
                $.ajax({
                    url: './config/funcionalidades/busquedaEstacionamiento.php',
                    method: 'POST',
                    data: {
                        busqueda: termino
                    },
                    success: function(response) {
                        $('#tabla-resultados').html(response);
                    }
                });
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>