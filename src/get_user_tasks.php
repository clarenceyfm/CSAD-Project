<?php
session_start();
require 'db_connection.php';

header("Content-Type: application/json");

// Ensure user is logged in
if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    echo json_encode([]);
    exit();
}

$user_email = $_SESSION["email"];

// Fetch tasks assigned to the user that are due within the next 7 days (excluding past-due tasks)
$sql = "SELECT name, end_date 
        FROM tasks 
        WHERE assigned_email = ? 
        AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY end_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode($tasks);
?>
