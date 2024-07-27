<?php
$servername = 'srv1456.hstgr.io';
$database = 'u777454471_trazabilidad';
$username = 'u777454471_reyper';
$password = 'bDreyP3r67445HtgR';

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
