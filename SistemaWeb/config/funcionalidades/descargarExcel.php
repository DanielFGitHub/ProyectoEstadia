<?php
require("../db/config.php");

// Configurar las cabeceras para la descarga del archivo Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename= archivo.xls");

// Preparamos la sentencia para obtener los datos de los usuarios
$sql = "SELECT * FROM usuarios";
$stmt = $pdo->query($sql);

// Guardamos los datos en la variable $usuarios
$usuarios = $stmt->fetchAll();

if ($usuarios) {
?>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido Paterno</th>
            <th>Apellido Materno</th>
            <th>Matricula</th>
            <th>Correo</th>
            <th>Rol</th>
        </tr>
    </thead>
    <tbody>
    <?php
    // Recorremos los elementos del arreglo para obtener datos de acuerdo al índice
    foreach ($usuarios as $usuario) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($usuario['IdUsuario']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['Nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['ApPat']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['ApMat']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['Matricula']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['Correo']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['Rol']) . "</td>";
        echo "</tr>";
    }
    ?>
    </tbody>
</table>
<?php
}
?>