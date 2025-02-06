<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION["email"];

// Fetch projects where the user is the owner or a member
$sql = "SELECT * FROM projects WHERE owner_email = ? 
        UNION 
        SELECT p.* FROM projects p 
        JOIN project_members pm ON p.id = pm.project_id WHERE pm.user_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $user_email, $user_email);
$stmt->execute();
$result = $stmt->get_result();

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | tasktopia</title>
    <link rel="stylesheet" href="index.css">
    <script>
        function toggleTaskWidget() {
            var taskContent = document.getElementById("taskContent");

            if (taskContent.style.display === "block") {
                taskContent.style.display = "none";
            } else {
                taskContent.style.display = "block";
                loadUserTasks();
            }
        }

        function loadUserTasks() {
            fetch("get_user_tasks.php")
                .then(response => response.json())
                .then(data => {
                    const taskList = document.getElementById("taskList");
                    taskList.innerHTML = "";

                    if (data.length === 0) {
                        taskList.innerHTML = "<p>No tasks assigned to you.</p>";
                        return;
                    }

                    data.forEach(task => {
                        let li = document.createElement("li");
                        li.textContent = `${task.name} (Due: ${task.end_date})`;
                        taskList.appendChild(li);
                    });
                })
                .catch(error => console.error("Error fetching tasks:", error));
        }
        
        function logout() {
            fetch("logout.php", {
                method: "POST",
                credentials: "same-origin"
            })
            .then(response => {
                if (response.ok) {
                    window.location.href = "login.html"; // Redirect to login page
                } else {
                    console.error("Logout failed");
                }
            })
            .catch(error => console.error("Logout request failed:", error));
        }
    </script>
</head>
<body style = "background: linear-gradient(135deg, #525252, #2C3E50);">
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($user_email); ?></h1>
        <button onclick="logout()">Logout</button>
    </header>

    <main>
        <h2>Your Projects</h2>

        <div class="displaybox">
            <?php if (empty($projects)): ?>
                <p>No projects found. Create one!</p>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                        <p>Start Date: <?php echo htmlspecialchars($project['start_date']); ?></p>
                        <p>End Date: <?php echo htmlspecialchars($project['end_date']); ?></p>
                        <button onclick="window.location.href='project_details.php?id=<?php echo $project['id']; ?>'">View Details</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div><br> 
        <button onclick="window.location.href='create_project.php'">+ Create New Project</button>
    </main>

    <!-- Task Widget -->
    <div class="task-widget">
        <button class="toggle-btn" onclick="toggleTaskWidget()">📋 Tasks Due </button>
        <div class="task-content" id="taskContent">
            <h4>Assigned Tasks</h4>
            <ul id="taskList"></ul>
        </div>
    </div>
</body>
</html>
