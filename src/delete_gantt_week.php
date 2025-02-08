<?php
session_start();
require 'db_connection.php';

header("Content-Type: application/json");


if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit();
}


$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["project_id"])) {
    echo json_encode(["success" => false, "error" => "Project ID is missing"]);
    exit();
}

$project_id = $data["project_id"];


$lastWeekSql = "SELECT MAX(week_number) as max_week FROM gantt_chart WHERE project_id = ?";
$lastWeekStmt = $conn->prepare($lastWeekSql);
$lastWeekStmt->bind_param("i", $project_id);
$lastWeekStmt->execute();
$lastWeekResult = $lastWeekStmt->get_result();
$lastWeekRow = $lastWeekResult->fetch_assoc();

if ($lastWeekRow["max_week"] === null) {
    echo json_encode(["success" => false, "error" => "No weeks to delete"]);
    exit();
}

$lastWeek = $lastWeekRow["max_week"];


$deleteSql = "DELETE FROM gantt_chart WHERE project_id = ? AND week_number = ?";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("ii", $project_id, $lastWeek);

if ($deleteStmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to delete last week"]);
}
