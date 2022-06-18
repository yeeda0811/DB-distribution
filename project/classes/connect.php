<?php
$server_name = "140.127.74.186";
$username = "410977025";
$password = "410977025";
$db_name = "410977025";


$conn = new mysqli($server_name, $username, $password, $db_name);
// Check connection
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}
$conn->query('SET NAMES UTF8');
$conn->query('SET time_zone = "+8:00"');