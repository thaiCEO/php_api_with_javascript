<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "product_db";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

?>