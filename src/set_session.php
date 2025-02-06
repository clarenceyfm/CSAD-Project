<?php
session_start();
require 'db_connection.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["email"]) || empty($data["email"])) {
    echo json_encode(["success" => false, "error" => "Missing email"]);
    exit();
}

$email = $data["email"];

// Verify if the email exists in MySQL
$checkQuery = "SELECT * FROM users WHERE email = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows == 0) {
    echo json_encode(["success" => false, "error" => "User not found in database"]);
    exit();
}

// Store in session
$_SESSION["email"] = $email;

echo json_encode(["success" => true]);
?>
