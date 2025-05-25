<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "booknook";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>