<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION["email"];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project | tasktopia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="extra.css">
    <script>
        function createProject(event) {
            event.preventDefault();

            const projectName = document.getElementById("project-name").value;
            const startDate = document.getElementById("start-date").value;
            const endDate = document.getElementById("end-date").value;

            // Get all entered team members
            const members = [];
            document.querySelectorAll(".member-email").forEach(input => {
                if (input.value.trim() !== "") {
                    members.push(input.value.trim());
                }
            });

            fetch("add_project.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        name: projectName,
                        start_date: startDate,
                        end_date: endDate,
                        members: members
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Project created successfully!");
                        window.location.href = "dashboard.php"; // Redirect to dashboard
                    } else {
                        alert("Error: " + data.error);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                });
        }

        function addMemberField() {
            const membersContainer = document.getElementById("members-container");
            const input = document.createElement("input");
            input.type = "email";
            input.className = "form-control member-email mb-2";
            input.placeholder = "Enter team member's email";
            membersContainer.appendChild(input);
        }
    </script>
</head>

<body>
    <div class="container mt-4" style="background: rgba(255, 255, 255, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <h2 class="text-center">Create a New Project</h2>

        <form onsubmit="createProject(event)">
            <div class="mb-3">
                <label for="project-name" class="form-label">Project Name</label>
                <input type="text" class="form-control" id="project-name" required>
            </div>

            <div class="mb-3">
                <label for="start-date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start-date" required>
            </div>

            <div class="mb-3">
                <label for="end-date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end-date" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Team Members</label>
                <div id="members-container">
                    <input type="email" class="form-control member-email mb-2" placeholder="Enter team member's email">
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addMemberField()">+ Add Member</button>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary me-2">Create Project</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Cancel</button>
            </div>
        </form>
    </div>
</body>

</html>