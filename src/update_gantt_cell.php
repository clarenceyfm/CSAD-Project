<?php
session_start();
require 'db_connection.php';

header("Content-Type: application/json");

// Ensure user is logged in
if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["project_id"], $data["label"], $data["week_number"], $data["is_active"])) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit();
}

$project_id = $data["project_id"];
$label = $data["label"];
$week_number = $data["week_number"];
$is_active = $data["is_active"];

// Update the cell's active status
$sql = "UPDATE gantt_chart SET is_active = ? WHERE project_id = ? AND label = ? AND week_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisi", $is_active, $project_id, $label, $week_number);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to update cell"]);
}
?>
