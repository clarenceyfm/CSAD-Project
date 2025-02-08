<?php
session_start();
require 'db_connection.php';

header("Content-Type: application/json");


if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    echo json_encode([]);
    exit();
}

$user_email = $_SESSION["email"];


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
