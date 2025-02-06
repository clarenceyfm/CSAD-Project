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

if (!isset($data["project_id"]) || !isset($data["user_email"])) {
    echo json_encode(["success" => false, "error" => "Missing data"]);
    exit();
}

$project_id = $data["project_id"];
$member_email = $data["user_email"];

// Ensure the user is the project owner
$checkOwner = "SELECT * FROM projects WHERE id = ? AND owner_email = ?";
$stmt = $conn->prepare($checkOwner);
$stmt->bind_param("is", $project_id, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit();
}

// Remove member from project
$deleteQuery = "DELETE FROM project_members WHERE project_id = ? AND user_email = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("is", $project_id, $member_email);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Database delete failed"]);
}
?>
