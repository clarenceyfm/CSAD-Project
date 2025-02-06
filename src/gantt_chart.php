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

// Check if user is part of the project
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

$project = $result->fetch_assoc();

// Fetch tasks with start and end dates
$task_sql = "SELECT name, start_date, end_date FROM tasks WHERE project_id = ?";
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
    <title>Gantt Chart | TaskTopia</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById("ganttChart").getContext("2d");

            const tasks = <?php echo json_encode($tasks); ?>;
            const labels = tasks.map(task => task.name);
            const startDates = tasks.map(task => new Date(task.start_date).getTime());
            const endDates = tasks.map(task => new Date(task.end_date).getTime());

            const minDate = new Date(Math.min(...startDates));
            const maxDate = new Date(Math.max(...endDates));

            const chartData = {
                labels: labels,
                datasets: [{
                    label: "Task Duration",
                    data: tasks.map((task, index) => ({
                        x: [startDates[index], endDates[index]],
                        y: task.name
                    })),
                    backgroundColor: "rgba(54, 162, 235, 0.5)",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 1
                }]
            };

            new Chart(ctx, {
                type: "bar",
                data: chartData,
                options: {
                    indexAxis: "y",
                    scales: {
                        x: {
                            type: "time",
                            time: {
                                unit: "day"
                            },
                            min: minDate.getTime(),
                            max: maxDate.getTime()
                        }
                    }
                }
            });
        });
    </script>
</head>
<body>
    <header>
        <h1>Gantt Chart - <?php echo htmlspecialchars($project['name']); ?></h1>
        <button onclick="window.location.href='project_details.php?id=<?php echo $project_id; ?>'">Back to Project</button>
    </header>

    <main>
        <canvas id="ganttChart"></canvas>
    </main>
</body>
</html>
