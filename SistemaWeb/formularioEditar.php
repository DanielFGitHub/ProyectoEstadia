<?php
    //Accedemos a la configuracion de la bd
    require './config/db/config.php';
    //Verificamos la sesion
    include './config/funcionalidades/sesion.php';
    //Accedemos al navbar
    include './templates/navbarAdmin.php';
    // Traemos los datos del usuario
    $id = $_GET['id'];

    try {
        $sql = "SELECT * FROM usuarios WHERE IdUsuario = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch();
    
        if (!$usuario) {
            die("Usuario no encontrado");
        }
    } catch (PDOException $e) {
        die("Error al obtener el usuario: " . $e->getMessage());
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body class='body'>
    <div class="container mt-5">
        <?php
            include './templates/retornoUsuarios.php'
        ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">Editar Usuario</div>
                    <div class="card-body">
                        <form id="editForm" class="formulario_fondo" action="./config/funcionalidades/editarUsuario.php" method="post">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['IdUsuario']); ?>">
                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input id="nombre" type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($usuario['Nombre']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="apellido_paterno">Apellido Paterno</label>
                                <input id="apellido_paterno" type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['ApPat']); ?>" name="apellido_paterno" required>
                            </div>
                            <div class="form-group">
                                <label for="apellido_materno">Apellido Materno</label>
                                <input id="apellido_materno" type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['ApMat']); ?>" name="apellido_materno" required>
                            </div>
                            <div class="form-group">
                                <label for="matricula">Matrícula</label>
                                <input id="matricula" type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['Matricula']); ?>" maxlength="14" name="matricula" required>
                            </div>
                            <!-- Campos de Correo y Contraseña -->
                            <div id="adminFields" class="hidden">
                                <div class="form-group">
                                    <label for="correo">Correo</label>
                                    <input id="correo" type="email" class="form-control" value="<?php echo htmlspecialchars($usuario['Correo']); ?>" name="correo">
                                </div>
                                <div class="form-group">
                                    <label for="password">Contraseña</label>
                                    <input id="password" type="password" class="form-control" value="<?php echo htmlspecialchars($usuario['Password']); ?>" name="password">
                                </div>
                                <div class="form-group">
                                    <label for="confirmPassword">Confirmar contraseña</label>
                                    <input id="confirmPassword" type="password" class="form-control" value="<?php echo htmlspecialchars($usuario['Password']); ?>" name="confirmPassword">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="rol">Rol</label>
                                <select id="rol" class="form-control" name="rol" required>
                                    <option value="Administrador" <?php echo ($usuario['Rol'] === 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                                    <option value="Usuario" <?php echo ($usuario['Rol'] === 'Usuario') ? 'selected' : ''; ?>>Usuario</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Guardar
                            </button>
                            <p id="errorMessage" style="color:red;"></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>  
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rolSelect = document.getElementById('rol');
            const adminFields = document.getElementById('adminFields');
            const correoField = document.getElementById('correo');
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirmPassword');

            // Función para mostrar/ocultar campos según el rol seleccionado
            function toggleAdminFields() {
                if (rolSelect.value === 'Administrador') {
                    adminFields.classList.remove('hidden');
                    correoField.required = true;
                    passwordField.required = true;
                    confirmPasswordField.required = true;
                } else {
                    adminFields.classList.add('hidden');
                    correoField.required = false;
                    passwordField.required = false;
                    confirmPasswordField.required = false;
                    correoField.value = '';
                    passwordField.value = '';
                    confirmPasswordField.value = '';
                }
            }

            // Inicializar los campos al cargar la página
            toggleAdminFields();

            // Agregar evento de cambio al selector de rol
            rolSelect.addEventListener('change', toggleAdminFields);
        });

        document.getElementById('editForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevenir el envío del formulario inicialmente

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const matricula = document.getElementById('matricula').value;
            const errorMessage = document.getElementById('errorMessage');
            const rol = document.getElementById('rol').value;

            let errorMessages = [];

            if (rol === 'Administrador') {
                if (password !== confirmPassword) {
                    errorMessages.push('Las contraseñas no coinciden.');
                }

                if (matricula.length !== 14 || !/^\d{14}$/.test(matricula)) {
                    errorMessages.push('La matrícula debe tener exactamente 14 caracteres numéricos.');
                }
            }

            if (errorMessages.length > 0) {
                errorMessage.textContent = errorMessages.join(' ');
            } else {
                errorMessage.textContent = '';
                // Permitir el envío del formulario si las validaciones son correctas
                this.submit();
            }
        });
    </script>
</body>
</html>
