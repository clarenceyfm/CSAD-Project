<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION["email"];

// Get project ID from URL
if (!isset($_GET["project_id"]) || empty($_GET["project_id"])) {
    echo "Project ID is missing.";
    exit();
}

$project_id = $_GET["project_id"];

// Check if the user is part of the project (owner or member)
$sql = "SELECT * FROM projects WHERE id = ? AND (owner_email = ? OR id IN 
        (SELECT project_id FROM project_members WHERE user_email = ?))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $project_id, $user_email, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Unauthorized access.";
    exit();
}

// Fetch project members
$members_sql = "SELECT user_email FROM project_members WHERE project_id = ?";
$members_stmt = $conn->prepare($members_sql);
$members_stmt->bind_param("i", $project_id);
$members_stmt->execute();
$members_result = $members_stmt->get_result();

$members = [];
while ($row = $members_result->fetch_assoc()) {
    $members[] = $row['user_email'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task | tasktopia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function createTask(event) {
            event.preventDefault();

            const taskName = document.getElementById("task-name").value;
            const assignedEmail = document.getElementById("assigned-email").value;
            const startDate = document.getElementById("start-date").value;
            const endDate = document.getElementById("end-date").value;

            fetch("add_task.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    project_id: <?php echo $project_id; ?>,
                    name: taskName,
                    assigned_email: assignedEmail,
                    start_date: startDate,
                    end_date: endDate
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Task created successfully!");
                    window.location.href = "project_details.php?id=<?php echo $project_id; ?>";
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
        }
    </script>
</head>
<body style="background: linear-gradient(135deg, #525252, #2C3E50);">
    <div class="container mt-4" style="background: rgba(255, 255, 255, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <h2 class="text-center">Create Task</h2>

        <form onsubmit="createTask(event)">
            <div class="mb-3">
                <label for="task-name" class="form-label">Task Name</label>
                <input type="text" class="form-control" id="task-name" required>
            </div>

            <div class="mb-3">
                <label for="assigned-email" class="form-label">Assign to Member</label>
                <select class="form-select" id="assigned-email">
                    <option value="">Unassigned</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?php echo htmlspecialchars($member); ?>">
                            <?php echo htmlspecialchars($member); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="start-date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start-date" required>
            </div>

            <div class="mb-3">
                <label for="end-date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end-date" required>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary me-2">Create Task</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='project_details.php?id=<?php echo $project_id; ?>'">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>
