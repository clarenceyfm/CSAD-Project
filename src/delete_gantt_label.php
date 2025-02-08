<?php
session_start();
require 'db_connection.php';

header("Content-Type: application/json");

// Check if user is logged in
if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit();
}

// Get data from the request
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["project_id"], $data["label"])) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit();
}

$project_id = $data["project_id"];
$label = $data["label"];

// Delete the label from the Gantt chart
$sql = "DELETE FROM gantt_chart WHERE project_id = ? AND label = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $project_id, $label);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to delete label"]);
}
?>
