<?php
session_start();
require 'db_connection.php';

header("Content-Type: application/json");


if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit();
}


$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["project_id"], $data["label"]) || empty($data["label"])) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit();
}

$project_id = $data["project_id"];
$label = $data["label"];


$checkSql = "SELECT * FROM gantt_chart WHERE project_id = ? AND label = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("is", $project_id, $label);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Label already exists"]);
    exit();
}


$sql = "INSERT INTO gantt_chart (project_id, label, week_number, is_active) VALUES (?, ?, 1, 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $project_id, $label);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to add label"]);
}
