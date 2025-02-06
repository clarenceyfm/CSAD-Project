<?php
session_start();
require 'db_connection.php';

header("Content-Type: application/json");

// Ensure user is logged in
if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit();
}

$user_email = $_SESSION["email"];

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["name"]) || !isset($data["start_date"]) || !isset($data["end_date"])) {
    echo json_encode(["success" => false, "error" => "Missing project details"]);
    exit();
}

$name = $data["name"];
$start_date = $data["start_date"];
$end_date = $data["end_date"];

// Insert project into database
$insertQuery = "INSERT INTO projects (name, owner_email, start_date, end_date) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("ssss", $name, $user_email, $start_date, $end_date);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Database insert failed"]);
}
?>
