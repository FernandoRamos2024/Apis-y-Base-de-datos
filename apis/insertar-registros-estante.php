<?php
require_once 'conexion.php';
date_default_timezone_set('America/Mexico_City');

$datos = isset($_GET['datos']) ? $_GET['datos'] : '';
$estante = isset($_GET['estante']) ? $_GET['estante'] : '';

$separador = "-";
$separada = explode($separador, $datos);

$codigo_proyecto = $separada[0]."-".$separada[1];
$codigo_partida = $datos;

function verificarRegistroSalida($codigo_proyecto, $codigo_partida, $estante) {
    global $conn;
    $sql = "SELECT * FROM reportes_estante
            WHERE codigo_proyecto = '$codigo_proyecto'
            	AND codigo_partida = '$codigo_partida'
				AND accion = 'salida'
				AND id_estante = '$estante'";
    $ejecutarConsulta = $conn->query($sql);

    if ($ejecutarConsulta->num_rows > 0) {
    	return true;
    } else {
    	return false;
    }
}

function verificarRegistroDuplicado($codigo_proyecto, $codigo_partida, $estante) {
    global $conn;
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    $hora_hace_1_minutos = date('H:i:s', strtotime('-1 minutes'));

    $sql = "SELECT COUNT(*) as cantidad_registros FROM reportes_estante
            WHERE codigo_proyecto = '$codigo_proyecto'
                AND codigo_partida = '$codigo_partida'
                AND id_estante = '$estante'
                AND fecha = '$fecha'
                AND hora BETWEEN '$hora_hace_1_minutos' AND '$hora'";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $cantidad_registros = $row['cantidad_registros'];

        if ($cantidad_registros > 0) {
            return true;
        }
    }

    return false;
}

function insertarRegistro($codigo_proyecto, $codigo_partida, $accion, $estatus, $estante) {
    global $conn;
   
    $stmt = $conn->prepare("CALL insertarRegistroEstante(?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $codigo_proyecto, $codigo_partida, $accion, $estatus, $estante);
    
    if (!$stmt->execute()) {
        echo "Error al insertar registro: " . $stmt->error;
        return false;
    }
    return true;
}

function obtenerAccionUltimoRegistro($codigo_proyecto, $codigo_partida, $estante) {
    global $conn;
    
    $sql = "SELECT accion FROM reportes_estante
            WHERE codigo_proyecto = '$codigo_proyecto'
                AND codigo_partida = '$codigo_partida'
                AND id_estante = '$estante'
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

$accion_ultimo_registro = obtenerAccionUltimoRegistro($codigo_proyecto, $codigo_partida, $estante);

if ($accion_ultimo_registro == null || $accion_ultimo_registro == 'salida') {
	if (!verificarRegistroDuplicado($codigo_proyecto, $codigo_partida, $estante)) {
	    if(insertarRegistro($codigo_proyecto, $codigo_partida, 'entrada', 'conforme', $estante)) {
	    	header("HTTP/1.1 201 OK");
		    echo "entrada insertada";

	    } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "no se inserto el registro";

        }
	} else {
        header("HTTP/1.1 410 Conflict");
        echo "Registro duplicado";

    }

} else {
	if (!verificarRegistroDuplicado($codigo_proyecto, $codigo_partida, $estante)) {
	    if(insertarRegistro($codigo_proyecto, $codigo_partida, 'salida', 'conforme', $estante)) {
	    	header("HTTP/1.1 202 OK");
		    echo "salida insertada";

	    } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "no se inserto el registro";

        }
	} else {
            header("HTTP/1.1 410 Conflict");
	        echo "Registro duplicado";

        }
}

?>