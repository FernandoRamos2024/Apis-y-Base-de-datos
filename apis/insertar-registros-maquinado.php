<?php
require_once 'conexion.php';
date_default_timezone_set('America/Mexico_City');

$datos = isset($_GET['datos']) ? $_GET['datos'] : '';
$area = isset($_GET['area']) ? $_GET['area'] : '';
$id_maquina = isset($_GET['maquina']) ? $_GET['maquina'] : '';
$operador = isset($_GET['operador']) ? $_GET['operador'] : '';
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

$separador = "-";
$separada = explode($separador, $datos);

$codigo_proyecto = $separada[0] . "-" . $separada[1];
$codigo_partida = $datos;

function determinarTurno() {
    $hora_actual = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    $hora = (int) $hora_actual->format('H');

    if ($hora >= 7 && $hora < 15) {
        return 'primero';
    } elseif ($hora >= 15 && $hora < 22) {
        return 'segundo';
    }
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

function obtenerAccionUltimoRegistro($codigo_proyecto, $codigo_partida, $id_maquina) {
    global $conn;
    
    $sql = "SELECT accion FROM reportes_maquinado
            WHERE codigo_proyecto = '$codigo_proyecto'
                AND codigo_partida = '$codigo_partida'
                AND id_maquina = '$id_maquina'
            ORDER BY fecha DESC, hora DESC
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['accion'];
    } else {
        return null;
    }
}

function verificarRegistrosIncompletos($id_maquina, $codigo_proyecto_ignore, $codigo_partida_ignore) {
    global $conn;

    $sql_entradas = "
        SELECT codigo_proyecto, codigo_partida, id_maquina, id_operador, id_area, COUNT(*) AS entradas
        FROM reportes_maquinado
        WHERE accion = 'entrada' 
        AND id_maquina = '$id_maquina'
        GROUP BY codigo_proyecto, codigo_partida, id_maquina, id_operador, id_area
    ";

    $sql_salidas = "
        SELECT codigo_proyecto, codigo_partida, id_maquina, id_operador, id_area, COUNT(*) AS salidas
        FROM reportes_maquinado
        WHERE accion IN ('turno terminado', 'pieza terminada')
        AND id_maquina = '$id_maquina'
        GROUP BY codigo_proyecto, codigo_partida, id_maquina, id_operador, id_area
    ";

    $entradas = $conn->query($sql_entradas);
    $salidas = $conn->query($sql_salidas);

    $incompletos = [];

    $entradas_array = [];
    while ($row = $entradas->fetch_assoc()) {
        $entradas_array[] = $row;
    }

    $salidas_array = [];
    while ($row = $salidas->fetch_assoc()) {
        $salidas_array[] = $row;
    }

    foreach ($entradas_array as $entrada) {
        // Ignorar los registros que coincidan con codigo_proyecto y codigo_partida obtenidos por el método GET
        if ($entrada['codigo_proyecto'] == $codigo_proyecto_ignore && $entrada['codigo_partida'] == $codigo_partida_ignore) {
            continue;
        }

        $encontrado = false;
        foreach ($salidas_array as $salida) {
            if ($entrada['codigo_proyecto'] == $salida['codigo_proyecto'] &&
                $entrada['codigo_partida'] == $salida['codigo_partida'] &&
                $entrada['id_maquina'] == $salida['id_maquina'] &&
                $entrada['id_area'] == $salida['id_area'] &&
                $entrada['id_operador'] == $salida['id_operador']) {
                $encontrado = true;
                if ($entrada['entradas'] > $salida['salidas']) {
                    $incompletos[] = array_merge($entrada, ['faltante' => 'pieza terminada']);
                }
                break;
            }
        }
        if (!$encontrado) {
            $incompletos[] = array_merge($entrada, ['faltante' => 'pieza terminada']);
        }
    }

    foreach ($salidas_array as $salida) {
        // Ignorar los registros que coincidan con codigo_proyecto y codigo_partida obtenidos por el método GET
        if ($salida['codigo_proyecto'] == $codigo_proyecto_ignore && $salida['codigo_partida'] == $codigo_partida_ignore) {
            continue;
        }

        $encontrado = false;
        foreach ($entradas_array as $entrada) {
            if ($salida['codigo_proyecto'] == $entrada['codigo_proyecto'] &&
                $salida['codigo_partida'] == $entrada['codigo_partida'] &&
                $salida['id_maquina'] == $entrada['id_maquina'] &&
                $salida['id_area'] == $entrada['id_area'] &&
                $salida['id_operador'] == $entrada['id_operador']) {
                $encontrado = true;
                break;
            }
        }
        if (!$encontrado) {
            $incompletos[] = array_merge($salida, ['faltante' => 'entrada']);
        }
    }

    return $incompletos;
}


function manejarRegistrosIncompletos($incompletos) {
    global $area, $operador, $turno;

    foreach ($incompletos as $incompleto) {

        $codigo_proyecto = $incompleto['codigo_proyecto'];
        $codigo_partida = $incompleto['codigo_partida'];
        $maquinaF = $incompleto['id_maquina'];
        $areaF = $incompleto['id_area'];
        $operadorF = $incompleto['id_operador'];
        $accion_faltante = $incompleto['faltante'];
        $estatus = 'revisar';
        
        if (insertarRegistro($codigo_proyecto, $codigo_partida, $turno, $accion_faltante, $estatus, $area, $maquinaF, $operadorF)) {
            header("HTTP/1.1 200 OK");
        }
    }
}

$turno = determinarTurno();
$incompletos = verificarRegistrosIncompletos($id_maquina, $codigo_proyecto, $codigo_partida);

if (count($incompletos) > 0) {
    manejarRegistrosIncompletos($incompletos);
    header("HTTP/1.1 200 OK");
}

if ($accion == 'entrada') {
    $accion_ultimo_registro = obtenerAccionUltimoRegistro($codigo_proyecto, $codigo_partida, $id_maquina);

    if ($accion_ultimo_registro == null || $accion_ultimo_registro == 'turno terminado' || $accion_ultimo_registro == 'pieza terminada') {
        header("HTTP/1.1 201 OK");
        echo "entrada insertada";
        insertarRegistro($codigo_proyecto, $codigo_partida, $turno, $accion, 'proceso', $area, $id_maquina, $operador);

    } else {
        header("HTTP/1.1 402 salida");
        echo "falta un registro de salida";
        echo "faltante == 2";
    }
} else {
    $accion_ultimo_registro = obtenerAccionUltimoRegistro($codigo_proyecto, $codigo_partida, $id_maquina);

    if ($accion_ultimo_registro == 'entrada') {
        header("HTTP/1.1 202 OK");
        echo "salida insertada";
        insertarRegistro($codigo_proyecto, $codigo_partida, $turno, $accion, 'proceso', $area, $id_maquina, $operador);

    } else {
        header("HTTP/1.1 401 entrada");
        echo "falta un registro de entrada";
        echo "faltante == 1";
    }
}
?>