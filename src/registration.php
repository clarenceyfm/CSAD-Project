<?php
session_start();
require 'db_connection.php'; // Ensure database connection

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["email"]) || empty($data["email"])) {
    echo json_encode(["success" => false, "error" => "Missing email"]);
    exit();
}

$email = $data["email"];

// Debugging: Check if data is received
error_log("Received email: " . $email);

// Check if email already exists
$checkQuery = "SELECT * FROM users WHERE email = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(["success" => true, "message" => "User already exists"]);
    exit();
}

// Insert new user
$insertQuery = "INSERT INTO users (email) VALUES (?)";
$insertStmt = $conn->prepare($insertQuery);
$insertStmt->bind_param("s", $email);

if ($insertStmt->execute()) {
    $_SESSION["email"] = $email; // Store email in session
    echo json_encode(["success" => true, "message" => "User inserted successfully"]);
} else {
    echo json_encode(["success" => false, "error" => "Database insert failed: " . $conn->error]);
}

$insertStmt->close();
$checkStmt->close();
$conn->close();
?>
