<?php
session_start();
require 'db_connection.php';


if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION["email"];


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
    <link rel="stylesheet" href="output.css">
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
                        window.location.href = "login.html";
                    } else {
                        console.error("Logout failed");
                    }
                })
                .catch(error => console.error("Logout request failed:", error));
        }
    </script>
</head>

<body>
    <header>
        <h1 class="text-[3.2em] leading-[1.1] text-center w-full my-[10px] relative -top-[20px]">Welcome, <?php echo htmlspecialchars($user_email); ?></h1>
        <div class="flex justify-center mt-4">
            <button class="cursor-pointer text-neutral-300 text-center py-2.5 px-5 rounded-xl bg-gray-800 transition delay-150 duration-300 ease-in-out hover:bg-gray-700 hover:scale-110" onclick="logout()">Logout</button>
        </div>
    </header>
    <br>
    <main>
        <h2 class="text-3xl font-bold text-center">Your Projects</h2>
        <br>
        <div class="grid grid-cols-2 justify-items-center items-center gap-[20px] max-h-[360px] overflow-y-auto bg-[#444] p-[30px] rounded-[10px] shadow-[0_0_10px_rgba(0,0,0,0.3)] scrollbar scrollbar-thumb-[#666] scrollbar-track-[#222] mx-auto max-w-[800px]">
            <?php if (empty($projects)): ?>
                <p>No projects found. Create one!</p>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="flex flex-col items-center text-center bg-[#555] p-[20px] rounded-[10px] shadow-[0_0_10px_rgba(0,0,0,0.3)] w-[250px]">
                        <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                        <p>Start Date: <?php echo htmlspecialchars($project['start_date']); ?></p>
                        <p>End Date: <?php echo htmlspecialchars($project['end_date']); ?></p><br>
                        <button class="cursor-pointer text-neutral-300 text-center py-2.5 px-5 rounded-xl bg-gray-800 transition delay-150 duration-300 ease-in-out hover:bg-gray-700 hover:scale-110" onclick="window.location.href='project_details.php?id=<?php echo $project['id']; ?>'">View Details</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <br>
        <div class="flex justify-center mt-4">
            <button class="cursor-pointer text-neutral-300 text-center py-2.5 px-5 rounded-xl bg-gray-800 transition delay-150 duration-300 ease-in-out hover:bg-gray-700 hover:scale-110"
                onclick="window.location.href='create_project.php'">+ Create New Project
            </button>
        </div>
    </main>


    <div class="fixed bottom-5 right-5 w-[200px] bg-[#333] text-white rounded-[10px] shadow-[0_0_10px_rgba(0,0,0,0.3)] overflow-hidden">
        <button class="w-full bg-[#444] text-white text-[18px] p-[10px] border-0 cursor-pointer text-center hover:bg-[#555]"
            onclick="toggleTaskWidget()">📋 Tasks Due
        </button>
        <div class="hidden p-[10px]" id="taskContent">
            <h4>Assigned Tasks</h4><br>
            <ul id="taskList"></ul>
        </div>
    </div>
</body>

</html>