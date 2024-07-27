<?php
require_once 'conexion.php';
date_default_timezone_set('America/Mexico_City');

$datos = isset($_GET['datos']) ? $_GET['datos'] : '';
$area = isset($_GET['area']) ? $_GET['area'] : '';
$id_maquina = isset($_GET['maquina']) ? $_GET['maquina'] : '';
$operador = isset($_GET['operador']) ? $_GET['operador'] : '';
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$faltante = isset($_GET['faltante']) ? $_GET['faltante'] : '';

$separador = "-";
$separada = explode($separador, $datos);

$codigo_proyecto = $separada[0] . "-" . $separada[1];
$codigo_partida = $datos;

function determinarTurno() {
    $hora_actual = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    $hora = (int) $hora_actual->format('H');

    if ($hora >= 5 && $hora < 15) {
        return 'primero';
    } elseif ($hora >= 15 && $hora < 24) {
        return 'segundo';
    }
}

function insertarRegistroFaltante($codigo_proyecto, $codigo_partida, $turno, $accion, $estatus, $area, $maquina, $operador) {
    global $conn;

    $stmt = $conn->prepare("CALL insertarRegistroMaquinado(?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiii", $codigo_proyecto, $codigo_partida, $turno, $accion, $estatus, $area, $maquina, $operador);
    
    if (!$stmt->execute()) {
        echo "Error al insertar registro: " . $stmt->error;
        return false;
    }
    return true;
}

function insertarRegistro($codigo_proyecto, $codigo_partida, $turno, $accion, $estatus, $area, $maquina, $operador) {
    global $conn;

    $stmt = $conn->prepare("CALL insertarRegistroMaquinado(?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiii", $codigo_proyecto, $codigo_partida, $turno, $accion, $estatus, $area, $maquina, $operador);
    
    if (!$stmt->execute()) {
        echo "Error al insertar registro: " . $stmt->error;
        return false;
    }
    return true;
}

function insertarRegistrosEnOrden($codigo_proyecto, $codigo_partida, $turno, $accion_faltante, $accion, $estatus, $area, $maquina, $operador) {
    if (insertarRegistroFaltante($codigo_proyecto, $codigo_partida, $turno, $accion_faltante, 'revisar', $area, $maquina, $operador)) {
        sleep(1);
        
        if (insertarRegistro($codigo_proyecto, $codigo_partida, $turno, $accion, $estatus, $area, $maquina, $operador)) {
            header("HTTP/1.1 200 OK");
            echo "Registros insertados correctamente";
            exit;
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "No se pudo insertar el registro: $accion";
            exit;
        }
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "No se pudo insertar el registro faltante: $accion_faltante";
        exit;
    }
}

$turno = determinarTurno();

if ($faltante == 402) {
    insertarRegistrosEnOrden($codigo_proyecto, $codigo_partida, $turno, 'turno terminado', $accion, 'proceso', $area, $id_maquina, $operador);
    
} elseif ($faltante == 401) {
    insertarRegistrosEnOrden($codigo_proyecto, $codigo_partida, $turno, 'entrada', $accion, 'proceso', $area, $id_maquina, $operador);

} else {
    header("HTTP/1.1 400 Bad Request");
    echo "Valor de faltante no válido";
    exit;
}
?>