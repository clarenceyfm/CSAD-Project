<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION["email"]) || empty($_SESSION["email"])) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION["email"];


if (!isset($_GET["id"]) || empty($_GET["id"])) {
    echo "Project ID is missing.";
    exit();
}

$project_id = $_GET["id"];


$sql = "SELECT * FROM projects WHERE id = ? AND owner_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $project_id, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Unauthorized access.";
    exit();
}

$project = $result->fetch_assoc();


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
    <title>Manage Team | tasktopia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="extra.css">
    <script>
        function addMember(event) {
            event.preventDefault();
            const memberEmail = document.getElementById("member-email").value;

            fetch("add_member.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        project_id: <?php echo $project_id; ?>,
                        user_email: memberEmail
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Member added successfully!");
                        window.location.reload();
                    } else {
                        alert("Error: " + data.error);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                });
        }

        function removeMember(email) {
            if (!confirm("Are you sure you want to remove this member?")) {
                return;
            }

            fetch("remove_member.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        project_id: <?php echo $project_id; ?>,
                        user_email: email
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Member removed successfully!");
                        window.location.reload();
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

<body style="min-height: 100vh; display: flex; justify-content: center; align-items: center;">
    <div class="container mt-4" style="background: rgba(255, 255, 255, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); max-width: 600px;">
        <h2 class="text-center">Manage Team - <?php echo htmlspecialchars($project['name']); ?></h2><br>

        <div class="d-flex justify-content-start mb-3">
            <a href="project_details.php?id=<?php echo $project_id; ?>" class="btn btn-secondary">Back to Project</a>
        </div>
        <h4>Current Team Members</h4>
        <div class="border p-3 mb-3" style="background: #ffffff; border-radius: 5px;">
            <?php if (empty($members)): ?>
                <p>No team members yet.</p>
            <?php else: ?>
                <?php foreach ($members as $member): ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="m-0" style="color: black;"><?php echo htmlspecialchars($member); ?></p>
                        <button class="btn btn-danger btn-sm" onclick="removeMember('<?php echo $member; ?>')">Remove</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h4>Add New Member</h4>
        <form onsubmit="addMember(event)">
            <div class="mb-3">
                <label for="member-email" class="form-label">Email</label>
                <input type="email" class="form-control" id="member-email" name="email" placeholder="Enter member's email" required>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
</body>

</html>