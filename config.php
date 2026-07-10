<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "guidemypc";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// קביעת קידוד UTF-8
$conn->set_charset("utf8mb4");

?>