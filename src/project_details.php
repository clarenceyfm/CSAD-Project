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
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    echo "Project ID is missing.";
    exit();
}

$project_id = $_GET["id"];

// Fetch project details (check if user is owner or member)
$sql = "SELECT * FROM projects WHERE id = ? 
        AND (owner_email = ? OR id IN 
        (SELECT project_id FROM project_members WHERE user_email = ?))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $project_id, $user_email, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Unauthorized access.";
    exit();
}

$project = $result->fetch_assoc();

// Fetch project tasks
$task_sql = "SELECT * FROM tasks WHERE project_id = ?";
$task_stmt = $conn->prepare($task_sql);
$task_stmt->bind_param("i", $project_id);
$task_stmt->execute();
$task_result = $task_stmt->get_result();

$tasks = [];
while ($row = $task_result->fetch_assoc()) {
    $tasks[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Details | tasktopia</title>
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($project['name']); ?></h1>
        <p>Owner: <?php echo htmlspecialchars($project['owner_email']); ?></p>
        <p>Start Date: <?php echo htmlspecialchars($project['start_date']); ?></p>
        <p>End Date: <?php echo htmlspecialchars($project['end_date']); ?></p>
        <button onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
        <button onclick="window.location.href='gantt_chart.php?id=<?php echo $project_id; ?>'">View Gantt Chart</button>
        <button onclick="window.location.href='project_members.php?id=<?php echo $project_id; ?>'">View Members</button>
    </header>

    <main>
        <h2>Tasks</h2>
        <button onclick="window.location.href='create_task.php?project_id=<?php echo $project_id; ?>'">+ Add Task</button>

        <div id="task-list">
            <?php if (empty($tasks)): ?>
                <p>No tasks found. Add one!</p>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div>
                        <h3><?php echo htmlspecialchars($task['name']); ?></h3>
                        <p>Assigned to: <?php echo htmlspecialchars($task['assigned_email'] ?? "Unassigned"); ?></p>
                        <p>Start Date: <?php echo htmlspecialchars($task['start_date']); ?></p>
                        <p>End Date: <?php echo htmlspecialchars($task['end_date']); ?></p>
                        <p>Progress: <?php echo htmlspecialchars($task['progress']); ?>%</p>
                        <button onclick="window.location.href='update_task.php?id=<?php echo $task['id']; ?>'">Edit Task</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
