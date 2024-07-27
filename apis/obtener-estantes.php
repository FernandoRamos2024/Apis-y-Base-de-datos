<?php
require_once 'conexion.php';
$info = array();


$sql = "SELECT * FROM estantes";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($inf = $result->fetch_assoc()) {
        $info['estantes'][] = $inf;
    }
    header("HTTP/1.1 200 OK");
} else {
    $info['estantes'] = null;
    header("HTTP/1.1 403 Forbidden");
}

header("Content-type: application/json; charset=UTF-8");
echo json_encode($info);
?>