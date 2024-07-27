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
    <title>Lista de usuarios</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link  rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        function confirmarEliminacion(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {
                window.location.href = '../config/funcionalidades/eliminarUsuario.php?id=' + id;
            }
        }
    </script>
</head>
<body class="body">
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Usuarios registrados</h1>
        <h4 class="text-center">A continuación se muestran la lista de usuarios registrados</h4>
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
            <a href="./config/funcionalidades/descargarExcel.php">
                <button class="btn btn-success">
                    <i class="fa-solid fa-download"></i>
                    Descargar Excel
                </button>
            </a>
            <a href="./formularioInsertar.php">
                <button class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Agregar usuario
                </button>
            </a>
        </div>
        <?php
        try {
            //Preparamos la sentencia para obtener los datos de los usuarios
            $sql = "SELECT * FROM usuarios";
            $stmt = $pdo->query($sql);
            //Guardamos los datos en la variable $usuarios
            $usuarios = $stmt->fetchAll();
        
            if ($usuarios) {
        ?>
            <div id="tabla-resultados">   
                <table class='table table-striped'>
                    <thead class='thead-dark'>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido Paterno</th>
                            <th>Apellido Materno</th>
                            <th>Matrícula</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Recorremos los elementos del arreglos para obtener datos de acuerdo al indice
                        foreach ($usuarios as $usuario) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($usuario['IdUsuario']) . "</td>";
                            echo "<td>" . htmlspecialchars($usuario['Nombre']) . "</td>";
                            echo "<td>" . htmlspecialchars($usuario['ApPat']) . "</td>";
                            echo "<td>" . htmlspecialchars($usuario['ApMat']) . "</td>";
                            echo "<td>" . htmlspecialchars($usuario['Matricula']) . "</td>";
                            echo "<td>" . htmlspecialchars($usuario['Correo']) . "</td>";
                            echo "<td>" . htmlspecialchars($usuario['Rol']) . "</td>";
                            echo "<td class='row'>
                                <button class='btn btn-danger btn-sm' onclick='confirmarEliminacion(". htmlspecialchars($usuario['IdUsuario']) .")'>
                                    <i class='fa-solid fa-trash'></i>
                                    Eliminar
                                </button>
                                <a href='./formularioEditar.php?id=". htmlspecialchars($usuario['IdUsuario'])."'>
                                    <button class='btn btn-warning btn-sm'>
                                        <i class='fa-solid fa-wrench'></i>
                                        Editar
                                    </button>
                                </a>
                            </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
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
                    url: './config/funcionalidades/busquedaUsuario.php',
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