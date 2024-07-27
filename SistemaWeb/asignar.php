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
    <title>ListaEmpleados</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body class='body'>
    <div class="row" id="app">
        <section class="table-container container mt-5">
            <h1 class="mb-4 text-center">Asignación de RFID</h1>
            <p class="mb-4 text-center">A continuación tenemos el apartado para asignar a los usuarios sus respectivas tarjetas RFID</p>
            <div class="table-responsive">
            <?php
                include './templates/retorno.php'
            ?>
            <!-- Barra de busqueda-->
                <div class="mb-4 text-center">
                    <div class="search-container">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input class="search" type="text" v-model="searchQuery" placeholder="Buscar...">
                    </div>
                    <div class="search-container">
                        <select v-model="filter" @change="updateFilter" class="form-control d-inline-block w-auton search">
                            <option value="all">Todos</option>
                            <option value="con">Con RFID</option>
                            <option value="sin">Sin RFID</option>
                        </select>
                    </div>  
                </div>
            <!-- Indicadores de los usuarios con rfid-->
                <div class="mb-4 text-center">
                    <span class="badge badge-success">Con RFID: {{ rfidCount }}</span>
                    <span class="badge badge-primary">Sin RFID: {{ noRfidCount }}</span>
                </div>
                <table class="table table-striped" id="tabla-resultados">
                    <thead class='thead-dark '>
                        <tr>
                            <th>
                                ID
                            </th>
                            <th>
                                Usuario
                            </th>
                            <th>
                                RFID
                            </th>
                            <th>

                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Iteramos sobre los usuarios y mostramos sus detalles -->
                        <tr v-for="employee in filteredEmployees" :key="employee.id">
                            <td>{{employee.id}}</td>
                            <td>{{employee.name }} {{employee.appat}}</td>
                            <td>
                                <!-- Mostramos el estado del RFID del usuario -->
                                <h4 v-if="employee.rfid_serial"><span class="badge badge-success">&nbsp;Asignada ({{employee.rfid_serial}})</span></h4>
                                <h4 v-else-if="employee.waiting"><span class="badge badge-warning">&nbsp;Esperando...</span></h4>
                                <h4 v-else><span class="badge badge-primary">&nbsp;Sin asignar</span></h4>
                            </td>
                            <td>
                                <!-- Botones de acciones -->
                                <button @click="removerTarjeta(employee.rfid_serial)" v-if="employee.rfid_serial" class="btn-detalle" style="background-color: #FF3131;">Remover</button>
                                <button @click="cancelarEsperaParaEmparejar" v-else-if="employee.waiting" class="btn-detalle" style="background-color: #B8BC2C;">Cancelar</button>
                                <button @click="asignarTarjetaRFID(employee)" v-else class="btn-detalle" style="background-color: #005f6b;">Asignar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script src="./assets/js/vue.min.js"></script>
    <script src="./assets/js/vue-toasted.min.js"></script>
    <script>
        Vue.use(Toasted);
        let shouldCheck = true;
        const CHECK_PAIRING_EMPLOYEE_INTERVAL = 1000;
        new Vue({
            el: "#app",
            data: () => ({
                employees: [],
                searchQuery: "",
                filter: 'all'
            }),
            async mounted() {
                await this.setLectorParaLeer(); // Configuramos el lector RFID para leer
                await this.actualizarListaUsuarios(); // Actualizamos la lista de usuarios
            },
            methods: {
                async removerTarjeta(rfidSerial) {
                    await fetch("./config/RemoverTarjetaRFID.php?rfid_serial=" + rfidSerial); // Eliminamos el RFID del usuario
                    this.$toasted.show("RFID removed", {
                        position: "top-left",
                        duration: 1000,
                    });
                    await this.actualizarListaUsuarios(); // Actualizamos la lista de usuarios
                },
                async cancelarEsperaParaEmparejar() {
                    shouldCheck = false;
                    await this.setLectorParaLeer(); // Cancelamos el emparejamiento
                },
                async setLectorParaLeer() {
                    await fetch("./config/ConfParaLeer.php"); // Configuramos el lector para leer un RFID
                },
                async asignarTarjetaRFID(employee) {
                    shouldCheck = true;
                    const employeeId = employee.id;
                    employee.waiting = true; // Marcamos al usuario como esperando
                    await fetch("./config/ConfParaEmparejar.php?employee_id=" + employeeId); // Configuramos el lector para emparejar
                    this.verificarAsignacion(employee); // Comprobamos si el usuario acaba de emparejar un RFID
                },
                async verificarAsignacion(employee) {
                    const r = await fetch("./config/ObtenerRFIDporID.php?employee_id=" + employee.id); // Obtenemos el RFID del usuario
                    const serial = await r.json();
                    if (!shouldCheck) {
                        employee.waiting = false;
                        await this.actualizarListaUsuarios();
                        return;
                    }
                    if (serial) {
                        this.$toasted.show("RFID assigned!", {
                            position: "top-left",
                            duration: 1000,
                        });
                        await this.setLectorParaLeer(); // Configuramos el lector para leer
                        await this.actualizarListaUsuarios(); // Actualizamos la lista de usuarios
                    } else {
                        setTimeout(() => {
                            this.verificarAsignacion(employee);
                        }, CHECK_PAIRING_EMPLOYEE_INTERVAL);
                    }
                },
                async actualizarListaUsuarios() {
                    // Obtenemos todos los usuarios
                    let response = await fetch("./config/ObtenerUsuariosAJAX.php");
                    let employees = await response.json();
                    // Creamos un diccionario de usuarios
                    let employeeDictionary = {};
                    employees = employees.map((employee, index) => {
                        employeeDictionary[employee.IdUsuario] = index;
                        return {
                            id: employee.IdUsuario,
                            name: employee.Nombre,
                            appat: employee.ApPat,
                            rfid_serial: null, // Inicializamos el RFID como nulo
                            waiting: false, // No está esperando
                        }
                    });
                    // Obtenemos los datos RFID de los usuarios
                    response = await fetch("./config/ObtenerUsuariosConRFID.php");
                    let rfidData = await response.json();
                    // Actualizamos los datos RFID de los usuarios
                    rfidData.forEach(rfidDetail => {
                        let employeeId = rfidDetail.IdUsuario;
                        if (employeeId in employeeDictionary) {
                            let index = employeeDictionary[employeeId];
                            employees[index].rfid_serial = rfidDetail.RFID;
                        }
                    });
                    // Actualizamos la lista de usuarios
                    this.employees = employees;
                },
                updateFilter() {
                    this.$forceUpdate(); // Forzamos la actualización de la vista
                }
            },
            computed: {
                filteredEmployees() {
                    const query = this.searchQuery.trim().toLowerCase();
                    let filtered = this.employees;

                    // Aplicar búsqueda
                    if (query) {
                        filtered = filtered.filter(employee => {
                            const fullName = `${employee.name} ${employee.appat}`.toLowerCase();
                            return fullName.includes(query);
                        });
                    }

                    // Aplicar filtro RFID
                    if (this.filter === 'con') {
                        filtered = filtered.filter(employee => employee.rfid_serial);
                    } else if (this.filter === 'sin') {
                        filtered = filtered.filter(employee => !employee.rfid_serial);
                    }

                    return filtered;

                    // Aplicar filtro RFID
                    if (this.filter === 'all') {
                    return this.employees;
                    } else if (this.filter === 'con') {
                        return this.employees.filter(employee => employee.rfid_serial);
                    } else if (this.filter === 'sin') {
                        return this.employees.filter(employee => !employee.rfid_serial);
                    }

                    return filtered;
                },
                rfidCount() {
                    return this.employees.filter(employee => employee.rfid_serial).length;
                },
                noRfidCount() {
                    return this.employees.filter(employee => !employee.rfid_serial).length;
                }
            },
            
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>