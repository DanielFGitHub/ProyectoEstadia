<?php
session_start(); // Iniciar la sesión

// Verificar si la sesión está activa
if (!isset($_SESSION['IdUsuario'])) {
    // Si no hay una sesión activa, redirigir al usuario a la página de login
    header("Location: login.php");
    exit();
}
include '../db/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $termino = $_POST["busqueda"];

    // Realizar la consulta utilizando el término de búsqueda
    $query = "SELECT e.* FROM usuarios e
              WHERE e.Nombre LIKE :termino 
              OR e.IdUsuario LIKE :termino
              OR e.ApPat LIKE :termino
              Or  e.ApMat LIKE :termino";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['termino' => '%' . $termino . '%']);
    $result = $stmt->fetchAll();

    if (count($result) > 0) {
        // Construir y mostrar los resultados
        echo '<table class="table table-striped">';
        echo '<thead class="thead-dark">';
        echo '<tr>';
        echo '<th scope="col">ID</th>';
        echo '<th scope="col">Nombre</th>';
        echo '<th scope="col">Apellido Paterno</th>';
        echo '<th scope="col">Apellido Materno</th>';
        echo '<th scope="col">Matricula</th>';
        echo '<th scope="col">Correo</th>';
        echo '<th scope="col">Rol</th>';
        echo '<th scope="col">Opciones</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($result as $row) {
            echo '<tr>';
            echo "<td>" . htmlspecialchars($row['IdUsuario']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ApPat']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ApMat']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Matricula']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Correo']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Rol']) . "</td>";
            echo "<td class='row'>
                    <button class='btn btn-danger btn-sm' onclick='confirmarEliminacion(" . htmlspecialchars($row['IdUsuario']) . ")'>
                        <i class='fa-solid fa-trash'></i>
                        Eliminar
                    </button>
                    <a href='./formularioEditar.php?id=" . htmlspecialchars($row['IdUsuario']) . "'>
                        <button class='btn btn-warning btn-sm'>
                            <i class='fa-solid fa-wrench'></i>
                            Editar
                        </button>
                    </a>
                </td>";
            echo "</tr>";
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo 'No se encontraron resultados.';
    }
}
?>