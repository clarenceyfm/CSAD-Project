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

if (!isset($data["project_id"]) || !isset($data["name"]) || !isset($data["start_date"]) || !isset($data["end_date"])) {
    echo json_encode(["success" => false, "error" => "Missing task details"]);
    exit();
}

$project_id = $data["project_id"];
$name = $data["name"];
$assigned_email = !empty($data["assigned_email"]) ? $data["assigned_email"] : NULL;
$start_date = $data["start_date"];
$end_date = $data["end_date"];

// Check if the user has permission to add a task
$checkProject = "SELECT * FROM projects WHERE id = ? AND (owner_email = ? OR id IN 
                (SELECT project_id FROM project_members WHERE user_email = ?))";
$stmt = $conn->prepare($checkProject);
$stmt->bind_param("iss", $project_id, $user_email, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit();
}

// Insert task into database
$insertQuery = "INSERT INTO tasks (project_id, assigned_email, name, start_date, end_date) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("issss", $project_id, $assigned_email, $name, $start_date, $end_date);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Database insert failed"]);
}
?>
