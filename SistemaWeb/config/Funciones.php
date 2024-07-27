<?php

if (!defined("RFID_STATUS_FILE")) {
    define("RFID_STATUS_FILE", "rfid_status");
}
if (!defined("RFID_STATUS_READING")) {
    define("RFID_STATUS_READING", "r");
}
if (!defined("RFID_STATUS_PAIRING")) {
    define("RFID_STATUS_PAIRING", "p");
}
if (!defined("PAIRING_EMPLOYEE_ID_FILE")) {
    define("PAIRING_EMPLOYEE_ID_FILE", "pairing_employee_id_file");
}

function getDB()
{
    $host = 'Host de la bd';
    $dbname = 'Nombre de tu bd';
    $username = 'Nombre de usuario';
    $password = 'Password de la bd';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        // Establecer el modo de error de PDO a excepción
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Establecer el modo de búsqueda predeterminado en FETCH_ASSOC
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // No mostrar el mensaje de error al usuario final
        die("Error al conectar con la base de datos. ");
    }
    return $pdo;
}

function getUsuarios()
{
    $db = getDB();
    $statement = $db->query("SELECT IdUsuario, Nombre, ApPat FROM usuarios");
    return $statement->fetchAll();
}

function getUsuariosConRFID()
{
    $query = "SELECT IdUsuario, RFID FROM rfid";
    $db = getDB();
    $statement = $db->query($query);
    return $statement->fetchAll();
}

function getUsuarioconRFIDporID($employeeId)
{ 
    $query = "SELECT RFID FROM rfid WHERE IdUsuario = ?";
    $db = getDB();
    $statement = $db->prepare($query);
    $statement->execute([$employeeId]);
    return $statement->fetchObject();
}

function setEstadoDelLector($newStatus)
{
    if (!in_array($newStatus, [RFID_STATUS_PAIRING, RFID_STATUS_READING])) {
        return;
    }
    file_put_contents(RFID_STATUS_FILE, $newStatus);
}

function setEmparejamiento($employeeId)
{
    setEstadoDelLector(RFID_STATUS_PAIRING);
    setEmparejarIDdeEmpleado($employeeId);
}

function RemoverRFIDeUsuario($rfidSerial)
{
    $query = "DELETE FROM rfid WHERE RFID = ?";
    $db = getDB();
    $statement = $db->prepare($query);
    return $statement->execute([$rfidSerial]);
}

function setEmparejarIDdeEmpleado($employeeId) 
{
    file_put_contents(PAIRING_EMPLOYEE_ID_FILE, $employeeId);
}

function LecturaRFID($rfidSerial)
{
    if (getEstadoDelLector() === RFID_STATUS_PAIRING) {
        EmparejarEmpleadoConRFID($rfidSerial, getEmparejarIDdeEmpleado());
        setEstadoDelLector(RFID_STATUS_READING);
        echo "Operacion realizada";
    } else {
        $employee = getEmpleadoPorRFID($rfidSerial);
        if ($employee) {
            if (existeRegistroDeEntrada($employee->IdUsuario)) {
                GuardarSalidaDeUsuario($employee->IdUsuario);
                echo "Operacion realizada";
                echo "<br>Salida registrada";
            } else {
                GuardarAsistenciaDeUsuario($employee->IdUsuario);
                echo "Operacion realizada";
                echo "<br> Entrada registrada";
            }
        } else {
            echo "Usuario no encontrado";
        }
    }
}

function getEstadoDelLector()
{
    return file_get_contents(RFID_STATUS_FILE);
}

function EmparejarEmpleadoConRFID($rfidSerial, $employeeId)
{
    RemoverRFIDeUsuario($rfidSerial);
    $query = "INSERT INTO rfid(IdUsuario, RFID) VALUES (?, ?)";
    $db = getDB();
    $statement = $db->prepare($query);
    return $statement->execute([$employeeId, $rfidSerial]);
}

function getEmparejarIDdeEmpleado()
{
    return file_get_contents(PAIRING_EMPLOYEE_ID_FILE);
}

function getEmpleadoPorRFID($rfidSerial)
{
    $query = "SELECT e.IdUsuario, e.Nombre FROM usuarios e INNER JOIN rfid
    ON rfid.IdUsuario = e.IdUsuario
    WHERE rfid.RFID = ?";

    $db = getDB();
    $statement = $db->prepare($query);
    $statement->execute([$rfidSerial]);
    return $statement->fetchObject();
}

function obtenerhora(){
    date_default_timezone_set('America/Mexico_City');
    $horaActual = date('H:i:s');
    return $horaActual;
}

function GuardarAsistenciaDeUsuario($IdUsuario)
{
    date_default_timezone_set('America/Mexico_City');
    $date = date("Y-m-d");
    $horaEntrada = obtenerhora();
    $query = "INSERT INTO registros( IdRegistro, Fecha, HoraEntrada, HoraSalida, IdUsuario) VALUES (Null, ?, ?, '00:00:00', ?)";
    $db = getDB();
    $statement = $db->prepare($query);
    return $statement->execute([ $date, $horaEntrada, $IdUsuario ]);

}

function existeRegistroDeEntrada($IdUsuario)
{
    date_default_timezone_set('America/Mexico_City');
    $date = date("Y-m-d");
    $query = "SELECT COUNT(*) FROM registros WHERE IdUsuario = ? AND Fecha = ? ";
    $db = getDB();
    $statement = $db->prepare($query);
    $statement->execute([$IdUsuario, $date]);
    return $statement->fetchColumn() > 0;
}

function GuardarSalidaDeUsuario($IdUsuario)
{
    date_default_timezone_set('America/Mexico_City');
    $date = date("Y-m-d");
    $horaSalida = obtenerhora();
    $query = "UPDATE registros SET HoraSalida = ? WHERE IdUsuario = ? AND Fecha = ? ";
    $db = getDB();
    $statement = $db->prepare($query);
    return $statement->execute([$horaSalida, $IdUsuario, $date]);
}


