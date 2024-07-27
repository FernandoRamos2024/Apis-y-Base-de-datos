<?php
require_once 'conexion.php';

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(array("error" => "La conexion falló: " . $conn->connect_error));
    exit;
}

$infoAreas = array('areas' => array());

$consultaArea = "SELECT * FROM areas";
$areas = $conn->query($consultaArea);

if ($areas->num_rows > 0) {
    while ($area = $areas->fetch_assoc()) {
        $idArea = $area['id'];
        
        $consultaMaquina = "SELECT * FROM maquinas WHERE id_area = '$idArea'";
        $maquinas = $conn->query($consultaMaquina);

        $infoMaquinas = array();

        if ($maquinas->num_rows > 0) {
            while ($maquina = $maquinas->fetch_assoc()) {
                $infoMaquinas[] = $maquina;
            }
        }

        $consultaOperador = "SELECT * FROM operadores WHERE id_area = '$idArea'";
        $operadores = $conn->query($consultaOperador);
        $infoOperadores = array();

        if ($operadores->num_rows > 0) {
            while ($operador = $operadores->fetch_assoc()) {
                $infoOperadores[] = $operador;
            }
        }

        $area['maquinas'] = $infoMaquinas;
        $area['operadores'] = $infoOperadores;
        $infoAreas['areas'][] = $area;
    }

    header("HTTP/1.1 200 OK");
} else {
    $infoAreas['areas'] = null;
    header("HTTP/1.1 403 Forbidden");
}

$conn->close();

header("Content-type: application/json; charset=UTF-8");
echo json_encode($infoAreas);
?>