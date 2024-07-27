<?php
require("../db/config.php");

// Configurar las cabeceras para la descarga del archivo Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=historial.xls");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fechainicio = $_POST['fechainicio'];  
    $fechafinal = $_POST['fechafinal'];
    
    // Preparar la consulta SQL
    $sql = "SELECT IdRegistro, Fecha, HoraEntrada, HoraSalida, Nombre, ApPat, ApMat 
            FROM registros 
            INNER JOIN usuarios ON usuarios.IdUsuario = registros.IdUsuario 
            WHERE Fecha BETWEEN :fechainicio AND :fechafinal 
            ORDER BY Fecha DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':fechainicio', $fechainicio);
    $stmt->bindParam(':fechafinal', $fechafinal);
    $stmt->execute();
    $registros = $stmt->fetchAll();

    if ($registros) {
        ?>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Hora Entrada</th>
                    <th>Hora Salida</th>
                    <th>Nombre</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Recorremos los elementos del arreglo para obtener datos de acuerdo al índice
            foreach ($registros as $registro) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($registro['IdRegistro']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['Fecha']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['HoraEntrada']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['HoraSalida']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['Nombre']) . " " . htmlspecialchars($registro['ApPat']) . " " . htmlspecialchars($registro['ApMat']) . "</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        <?php
    } else {
        echo "No se encontraron registros en el rango de fechas especificado.";
    }
} else {
    echo "Solicitud no válida.";
}
?>