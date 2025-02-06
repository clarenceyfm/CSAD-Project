<?php
$host = "localhost"; // Change if needed
$user = "root"; // Default for XAMPP
$password = ""; // Default for XAMPP (leave empty)
$database = "tasktopia"; // Change to your database name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
