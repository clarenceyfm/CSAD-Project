<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION["email"];

// Get task ID from URL
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    echo "Task ID is missing.";
    exit();
}

$task_id = $_GET["id"];

// Fetch task details (ensure user is assigned or a project owner)
$sql = "SELECT t.*, p.owner_email, p.id AS project_id
        FROM tasks t 
        JOIN projects p ON t.project_id = p.id 
        WHERE t.id = ? AND (t.assigned_email = ? OR p.owner_email = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $task_id, $user_email, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Unauthorized access or task does not exist.";
    exit();
}

$task = $result->fetch_assoc();
$project_id = $task['project_id'];

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
    <title>Update Task | tasktopia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function updateTask(event) {
            event.preventDefault();

            const taskName = document.getElementById("task-name").value;
            const assignedEmail = document.getElementById("assigned-email").value;
            const startDate = document.getElementById("start-date").value;
            const endDate = document.getElementById("end-date").value;
            const progress = document.getElementById("progress").value;

            fetch("save_task_updates.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    task_id: <?php echo $task_id; ?>,
                    name: taskName,
                    assigned_email: assignedEmail,
                    start_date: startDate,
                    end_date: endDate,
                    progress: progress
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Task updated successfully!");
                    window.location.href = "project_details.php?id=<?php echo $task['project_id']; ?>";
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
        }

        function deleteTask() {
            if (!confirm("Are you sure you want to delete this task?")) {
                return;
            }

            fetch("delete_task.php?id=<?php echo $task_id; ?>", {
                method: "POST"
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Task deleted successfully!");
                    window.location.href = "project_details.php?id=<?php echo $task['project_id']; ?>";
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
        }

        function updateProgressValue(value) {
            document.getElementById("progress-value").textContent = value + "%";
        }
    </script>
</head>
<body style="background: linear-gradient(135deg, #525252, #2C3E50);">
    <div class="container mt-4" style="background: rgba(255, 255, 255, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <h2 class="text-center">Update Task</h2>

        <form onsubmit="updateTask(event)">
            <div class="mb-3">
                <label for="task-name" class="form-label">Task Name</label>
                <input type="text" class="form-control" id="task-name" value="<?php echo htmlspecialchars($task['name']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="assigned-email" class="form-label">Assign to Member</label>
                <select class="form-select" id="assigned-email">
                    <option value="">Unassigned</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?php echo htmlspecialchars($member); ?>" <?php echo ($task['assigned_email'] == $member) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($member); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="start-date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start-date" value="<?php echo htmlspecialchars($task['start_date']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="end-date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end-date" value="<?php echo htmlspecialchars($task['end_date']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="progress" class="form-label">Progress: <span id="progress-value"><?php echo htmlspecialchars($task['progress']); ?>%</span></label>
                <input type="range" class="form-range" id="progress" min="0" max="100" value="<?php echo htmlspecialchars($task['progress']); ?>" oninput="updateProgressValue(this.value)">
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-danger" onclick="deleteTask()">Delete Task</button>
                <div>
                    <button type="submit" class="btn btn-primary me-2">Update Task</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='project_details.php?id=<?php echo $task['project_id']; ?>'">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
