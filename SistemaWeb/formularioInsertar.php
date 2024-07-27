<?php
   //Verificamos la sesion
   include './config/funcionalidades/sesion.php';
   //Accedemos al navbar
   include './templates/navbarAdmin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body class="body">
    <div class="container mt-5">
        <?php
            include './templates/retornoUsuarios.php'
        ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header text-center">Registro de Usuario</div>
                    <div class="card-body">
                        <!-- Botones de selección de rol -->
                        <div class="text-center mb-3">
                            <label>Por favor seleccione el rol del usuario</label>
                            <br>
                            <button type="button" class="btn btn-primary" onclick="selectRole('Usuario')">
                                <i class="fa-solid fa-user"></i>    
                                Usuario
                            </button>
                            <button type="button" class="btn btn-success" onclick="selectRole('Administrador')">
                                <i class="fa-solid fa-user-tie"></i>
                                Admin
                            </button>
                        </div>
                        <form id="passwordForm" class="formulario_fondo" action="./config/funcionalidades/insertarUsuario.php" method="post">
                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input id="nombre" type="text" class="form-control" name="nombre" placeholder="Coloque el nombre del usuario" required>
                            </div>
                            <div class="form-group">
                                <label for="apellido_paterno">Apellido Paterno</label>
                                <input id="apellido_paterno" type="text" class="form-control" placeholder="Coloque el apellido paterno del usuario" name="apellido_paterno" required>
                            </div>
                            <div class="form-group">
                                <label for="apellido_materno">Apellido Materno</label>
                                <input id="apellido_materno" type="text" class="form-control" placeholder="Coloque el apellido materno del usuario" name="apellido_materno" required>
                            </div>
                            <div class="form-group">
                                <label for="matricula">Matrícula</label>
                                <input id="matricula" type="text" class="form-control" placeholder="Coloque la matricula del usuario" name="matricula" maxlength="14" required>
                            </div>
                            <div id="adminFields" class="hidden">
                                <div class="form-group">
                                    <label for="correo">Correo</label>
                                    <input id="correo" type="email" class="form-control" placeholder="Coloque el correo del usuario" name="correo">
                                </div>
                                <div class="form-group">
                                    <label for="password">Contraseña</label>
                                    <input id="password" type="password" class="form-control" placeholder="Coloque la contraseña del usuario" name="password">
                                </div>
                                <div class="form-group">
                                    <label for="confirmPassword">Confirmar contraseña</label>
                                    <input id="confirmPassword" type="password" class="form-control" placeholder="Rectifique la contraseña" name="confirmPassword">
                                </div>
                            </div>
                            <div class="form-group hidden">
                                <label for="rol">Rol</label>
                                <select id="rol" class="form-control" name="rol" required>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Usuario">Usuario</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-user-plus"></i>
                                Registrar
                            </button>
                            <p id="errorMessage" style="color:red;"></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function selectRole(role) {
            const adminFields = document.getElementById('adminFields');
            const rolSelect = document.getElementById('rol');
            const rolOptionUsuario = rolSelect.querySelector('option[value="Usuario"]');
            const rolOptionAdmin = rolSelect.querySelector('option[value="Administrador"]');
            if (role === 'Administrador') {
                adminFields.classList.remove('hidden');
                rolSelect.value = 'Administrador';
                rolOptionUsuario.classList.add('hidden');
                rolOptionAdmin.classList.remove('hidden');
                document.getElementById('password').required = true;
                document.getElementById('confirmPassword').required = true;
                document.getElementById('correo').required = true;
            } else {
                adminFields.classList.add('hidden');
                rolSelect.value = 'Usuario';
                rolOptionAdmin.classList.add('hidden');
                rolOptionUsuario.classList.remove('hidden');
                document.getElementById('password').required = false;
                document.getElementById('confirmPassword').required = false;
                document.getElementById('correo').required = false;
            }
        }

        document.getElementById('passwordForm').addEventListener('submit', function(event) {
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

                if (matricula.length !== 14) {
                    errorMessages.push('La matrícula debe tener exactamente 14 caracteres.');
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


