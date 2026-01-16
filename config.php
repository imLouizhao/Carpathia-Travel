<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$servername = "sql306.infinityfree.com";
$username = "if0_40904287";
$password = "4SmqwVsqUWJ6io";
$dbname = "if0_40904287_carp_travel";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
?>