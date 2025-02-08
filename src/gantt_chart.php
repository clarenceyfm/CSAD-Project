<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    header("Location: login.html");
    exit();
}

// Get project ID from URL
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    echo "Project ID is missing.";
    exit();
}

$project_id = $_GET["id"];

// Fetch project details
$sql = "SELECT * FROM projects WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

// If no project is found, handle gracefully
if ($result->num_rows === 0) {
    echo "<p>Project not found.</p>";
    exit();
}

$project = $result->fetch_assoc();
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gantt Chart | tasktopia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="extra.css">
    <style>
        .gantt-cell.active {
            background: linear-gradient(135deg, #525252, #2C3E50);
            color: white;
        }

        .gantt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .gantt-table th, .gantt-table td {
            border: 1px solid #ccc;
            text-align: center;
            padding: 5px;
            background-color: white; /* Make all cells white by default */
        }

        .gantt-table th {
            background-color: #f8f9fa; /* Keep headers slightly distinct */
        }

        .action-buttons {
            margin-top: 15px;
        }

        .container {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
    <script>
        function toggleCell(cell, projectId, label, weekNumber) {
            const isActive = cell.classList.contains("active");
            fetch("update_gantt_cell.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    project_id: projectId,
                    label: label,
                    week_number: weekNumber,
                    is_active: !isActive
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cell.classList.toggle("active");
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => console.error("Error updating cell:", error));
        }

        function addRow(projectId) {
            const label = prompt("Enter a label for the new row:");
            if (!label) return;

            fetch("add_gantt_label.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    project_id: projectId,
                    label: label
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => console.error("Error adding row:", error));
        }

        function addColumn(projectId) {
            fetch("add_gantt_week.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ project_id: projectId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => console.error("Error adding column:", error));
        }

        function deleteLastColumn(projectId) {
            fetch("delete_gantt_week.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ project_id: projectId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => console.error("Error deleting last column:", error));
        }
    </script>
</head>

<body>
    <div class="container mt-4">
        <h2 class="text-center">Gantt Chart for <?php echo htmlspecialchars($project['name']); ?></h2>
        <div class="mb-3 text-start">
            <button class="btn btn-secondary" onclick="window.location.href='project_details.php?id=<?php echo $project_id; ?>'">
                Back to Project
            </button>
        </div>

        <table id="ganttTable" class="gantt-table">
            <thead>
                <tr>
                    <th>Labels</th>
                    <?php
                    $weekSql = "SELECT MAX(week_number) as max_week FROM gantt_chart WHERE project_id = ?";
                    $weekStmt = $conn->prepare($weekSql);
                    $weekStmt->bind_param("i", $project_id);
                    $weekStmt->execute();
                    $weekResult = $weekStmt->get_result();
                    $weekRow = $weekResult->fetch_assoc();
                    $weekCount = $weekRow['max_week'] ?? 1;

                    for ($week = 1; $week <= $weekCount; $week++) {
                        echo "<th>Week $week</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $ganttSql = "SELECT label, week_number, is_active FROM gantt_chart WHERE project_id = ? ORDER BY label, week_number";
                $ganttStmt = $conn->prepare($ganttSql);
                $ganttStmt->bind_param("i", $project_id);
                $ganttStmt->execute();
                $ganttResult = $ganttStmt->get_result();

                $ganttData = [];
                while ($row = $ganttResult->fetch_assoc()) {
                    $ganttData[$row['label']][$row['week_number']] = $row['is_active'];
                }

                foreach ($ganttData as $label => $weeks) {
                    echo "<tr>";
                    echo "<td contentEditable='true'>" . htmlspecialchars($label) . "</td>";
                    for ($week = 1; $week <= $weekCount; $week++) {
                        $isActive = isset($weeks[$week]) && $weeks[$week] ? "active" : "";
                        echo "<td class='gantt-cell $isActive' onclick='toggleCell(this, $project_id, \"$label\", $week)'></td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="action-buttons d-flex justify-content-between">
            <button class="btn btn-primary" onclick="addRow(<?php echo $project_id; ?>)">+ Add Row</button>
            <button class="btn btn-primary" onclick="addColumn(<?php echo $project_id; ?>)">+ Add Column</button>
            <button class="btn btn-danger" onclick="deleteLastColumn(<?php echo $project_id; ?>)">- Delete Last Column</button>
        </div>
    </div>
</body>

</html>
