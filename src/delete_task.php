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

// Get task ID from request
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    echo json_encode(["success" => false, "error" => "Task ID missing"]);
    exit();
}

$task_id = $_GET["id"];

// Ensure user is authorized to delete the task
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

// Delete task
$deleteQuery = "DELETE FROM tasks WHERE id = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $task_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Database delete failed"]);
}
?>
