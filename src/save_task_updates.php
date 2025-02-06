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

if (!isset($data["task_id"]) || !isset($data["name"]) || !isset($data["start_date"]) || !isset($data["end_date"]) || !isset($data["progress"])) {
    echo json_encode(["success" => false, "error" => "Missing task details"]);
    exit();
}

$task_id = $data["task_id"];
$name = $data["name"];
$assigned_email = !empty($data["assigned_email"]) ? $data["assigned_email"] : NULL;
$start_date = $data["start_date"];
$end_date = $data["end_date"];
$progress = intval($data["progress"]);

// Ensure user is authorized to update the task
$checkTask = "SELECT t.*, p.owner_email 
              FROM tasks t 
              JOIN projects p ON t.project_id = p.id 
              WHERE t.id = ? AND (t.assigned_email = ? OR p.owner_email = ?)";
$stmt = $conn->prepare($checkTask);
$stmt->bind_param("iss", $task_id, $user_email, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit();
}

// Update task in database
$updateQuery = "UPDATE tasks SET name = ?, assigned_email = ?, start_date = ?, end_date = ?, progress = ? WHERE id = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("ssssii", $name, $assigned_email, $start_date, $end_date, $progress, $task_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Database update failed"]);
}
?>
