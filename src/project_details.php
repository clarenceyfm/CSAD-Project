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

// Fetch project details (checks if user is owner or member)
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
    <link rel="stylesheet" href="./output.css">
    <link rel="stylesheet" href="index.css">
</head>

<body class="min-h-screen">

    <h1 class=" mt-20 text-6xl text-center text-white font-bold">tasktopia</h1>

    <div class="flex justify-center text-center items-center">
        <div class="container mt-16 px-6 p-4 text-white bg-[#444] h-auto rounded-lg shadow-md overflow-y-auto w-5xl" id="details">
            <h2 class="text-5xl font-semibold text-white mx-auto text-wrap mb-15 mt-2 bg-blue-300/55 bg-auto p-2 shadow-md items-center size-auto rounded-2xl">
                <?php echo htmlspecialchars($project['name']); ?>
            </h2>
            <div class=" mt-8 container px-6 p-4 text-white bg-neutral-500 mb-3 h-auto rounded-lg shadow-md overflow-y-auto w-auto text-center">
                <div class="mt-2 font-normal">Owner: <span id="name" class="text-white"><?php echo htmlspecialchars($project['owner_email']); ?></span> </div>
                <br>
                <div class="font-normal">Start Date: <span id="start" class="text-white"><?php echo htmlspecialchars($project['start_date']); ?></span> </div>
                <br>
                <div class="font-normal">Due Date: <span id="due" class="text-white"><?php echo htmlspecialchars($project['end_date']); ?></span> </div>
                <br>
                <div class="mt-7">

                    <button onclick="window.location.href='dashboard.php'" class="cursor-pointer text-neutral-300 text-s text-center py-2.5 px-5 rounded-xl bg-gray-800 transition delay-150 duration-300 ease-in-out hover:bg-gray-700 hover:scale-110">
                        Back to Dashboard
                    </button>

                    <button onclick="window.location.href='project_members.php?id=<?php echo $project_id; ?>'" class="cursor-pointer text-neutral-300 text-center py-2.5 px-5 ml-8 mb-2.5 rounded-xl bg-gray-800 transition delay-150 duration-300 ease-in-out hover:bg-gray-700 hover:scale-110">
                        View Members
                    </button>

                    <button onclick="window.location.href='gantt_chart.php?id=<?php echo $project_id; ?>'" class="cursor-pointer text-neutral-300 text-center py-2.5 px-5 ml-8 mb-2.5 rounded-xl bg-gray-800 transition delay-150 duration-300 ease-in-out hover:bg-gray-700 hover:scale-110">
                        View Gantt Chart
                    </button>

                </div>

            </div>

            <main>
                <div class="mt-8 container px-6 p-4 text-white bg-neutral-500 h-auto rounded-lg shadow-md overflow-y-auto w-auto text-center">
                    <h2 class="text-3xl font-bold"> Tasks: </h2>

                    <button onclick="window.location.href='create_task.php?project_id=<?php echo $project_id; ?>'" class="cursor-pointer text-neutral-300 text-center py-1.5 px-4 mt-7 rounded-xl bg-gray-800 transition delay-150 duration-300 ease-in-out hover:bg-gray-700 hover:scale-110">+ Add Task</button>
                    <br>

                    <div id="task-list" class=" ">
                        <div id="task-list">
                            <?php if (empty($tasks)): ?>
                                <p class="mt-7 mb-7 ">No tasks found. Add one!</p>
                            <?php else: ?>
                                <div class="grid grid-cols-3 gap-4 auto-cols-max auto-rows-max">
                                    <?php foreach ($tasks as $task): ?>
                                        <div class="bg-gray-400 p-4 rounded-lg shadow-md h-auto w-auto text-wrap hover:shadow-xl transition-shadow ">

                                            <p class="text-2xl text-white font-normal"><?php echo htmlspecialchars($task['name']); ?></p>
                                            <p class="text-white mt-2 text-wrap font-normal">Assigned to: <?php echo htmlspecialchars($task['assigned_email'] ?? "Unassigned"); ?></p>
                                            <p class="mt-2 text-white font-normal">Start Date: <?php echo htmlspecialchars($task['start_date']); ?></p>
                                            <p class="mt-2 text-white font-normal">End Date: <?php echo htmlspecialchars($task['end_date']); ?></p>
                                            <p class="mt-2 text-white font-normal">Progress: <?php echo htmlspecialchars($task['progress']); ?>%</p>

                                            <button onclick="window.location.href='update_task.php?id=<?php echo $task['id']; ?>'" class="cursor-pointer text-neutral-300 text-center py-1.5 px-4 mt-5 rounded-xl bg-gray-800 transition delay-150 duration-300 ease-in-out hover:bg-gray-700 hover:scale-110">Edit Task</button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

</body>

</html>