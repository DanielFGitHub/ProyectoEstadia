<?php
    //Accedemos a la configuracion de la bd
    require '../db/config.php';
    //Verificamos la sesion
    include './sesion.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $termino = $_POST["busqueda"];

        // Realizar la consulta utilizando el término de búsqueda
        $query = "SELECT IdRegistro, Fecha, HoraEntrada, HoraSalida, Nombre, ApPat, ApMat 
                FROM registros 
                INNER JOIN usuarios 
                ON usuarios.IdUsuario = registros.IdUsuario 
                WHERE Nombre LIKE :termino 
                OR ApPat LIKE :termino
                OR ApMat LIKE :termino
                OR usuarios.IdUsuario LIKE :termino 
                ORDER BY Fecha DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute(['termino' => '%' . $termino . '%']);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            // Construir y mostrar los resultados
            echo '<table class="table table-striped">';
            echo '<thead class="thead-dark">';
            echo '<tr>';
            echo '<th scope="col">ID</th>';
            echo '<th scope="col">Fecha</th>';
            echo '<th scope="col">Hora Entrada</th>';
            echo '<th scope="col">Hora Salida</th>';
            echo '<th scope="col">Nombre</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($result as $row) {
                echo '<tr>';
                echo "<td>" . htmlspecialchars($row['IdRegistro']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Fecha']) . "</td>";
                echo "<td>" . htmlspecialchars($row['HoraEntrada']) . "</td>";
                echo "<td>" . htmlspecialchars($row['HoraSalida']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Nombre']) . " " . htmlspecialchars($row['ApPat']) . " " . htmlspecialchars($row['ApMat']) . "</td>";
                echo "</tr>";
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo 'No se encontraron resultados.';
        }
    }
?>