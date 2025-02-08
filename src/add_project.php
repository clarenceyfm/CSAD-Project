<?php
session_start();
require 'db_connection.php';

header("Content-Type: application/json");


if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit();
}

$user_email = $_SESSION["email"];


$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["name"], $data["start_date"], $data["end_date"])) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit();
}

$project_name = $data["name"];
$start_date = $data["start_date"];
$end_date = $data["end_date"];
$members = $data["members"] ?? [];


$insertProjectSQL = "INSERT INTO projects (name, owner_email, start_date, end_date) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insertProjectSQL);
$stmt->bind_param("ssss", $project_name, $user_email, $start_date, $end_date);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "error" => "Failed to create project"]);
    exit();
}


$project_id = $conn->insert_id;


$insertOwnerSQL = "INSERT INTO project_members (project_id, user_email) VALUES (?, ?)";
$stmt = $conn->prepare($insertOwnerSQL);
$stmt->bind_param("is", $project_id, $user_email);
$stmt->execute();


foreach ($members as $member_email) {
    if (!empty($member_email) && $member_email !== $user_email) {
        $insertMemberSQL = "INSERT INTO project_members (project_id, user_email) VALUES (?, ?)";
        $stmt = $conn->prepare($insertMemberSQL);
        $stmt->bind_param("is", $project_id, $member_email);
        $stmt->execute();
    }
}

echo json_encode(["success" => true]);
