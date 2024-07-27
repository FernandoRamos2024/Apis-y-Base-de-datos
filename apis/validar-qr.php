<?php

$cadena = isset($_GET['data']) ? $_GET['data'] : '';

$patron = "/^\d{3}-\d{4}-\d{2}\.\d{2}$/";

if (preg_match($patron, $cadena)) {
    header("HTTP/1.1 200 OK");
    echo json_encode(["status" => "success", "message" => "QR valido"]);
} else {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(["status" => "error", "message" => "QR no valido"]);
}
?>