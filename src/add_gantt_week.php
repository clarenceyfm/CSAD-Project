<?php
session_start();
require 'db_connection.php';

header("Content-Type: application/json");

// Check if user is logged in
if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit();
}

// Get project ID from the request
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["project_id"])) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit();
}

$project_id = $data["project_id"];

// Find the maximum week number for this project
$maxWeekSql = "SELECT MAX(week_number) as max_week FROM gantt_chart WHERE project_id = ?";
$maxWeekStmt = $conn->prepare($maxWeekSql);
$maxWeekStmt->bind_param("i", $project_id);
$maxWeekStmt->execute();
$maxWeekResult = $maxWeekStmt->get_result();
$maxWeekRow = $maxWeekResult->fetch_assoc();

$newWeek = $maxWeekRow["max_week"] + 1;

// Add the new week for each existing label
$sql = "INSERT INTO gantt_chart (project_id, label, week_number, is_active)
        SELECT project_id, label, ?, 0 FROM gantt_chart WHERE project_id = ? GROUP BY label";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $newWeek, $project_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to add week"]);
}
?>
