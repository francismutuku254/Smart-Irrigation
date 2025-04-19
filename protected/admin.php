<?php
session_start();

// Set session timeout to 5 minutes (300 seconds)
$timeout = 300;

// Check if last activity is set
if (isset($_SESSION['last_activity'])) {
    $duration = time() - $_SESSION['last_activity'];
    if ($duration > $timeout) {
        session_unset();  // Unset session variables
        session_destroy(); // Destroy the session
        header("Location: login.html?session=expired");
        exit();
    }
}



// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo "<script>
            alert('Access denied. Please log in first.');
            window.location.href = 'login.html';
          </script>";
    exit();
}
$host = "localhost";
$user = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "irrigation_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Fetch user role
$user_id = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role !== "admin") {
    echo "<script>
            alert('Access denied. You are not an admin.');
            window.location.href = 'home.php';
          </script>";
    exit();
}

// Approve user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["approve_user"])) {
    $approve_user_id = $_POST["approve_user"];
    $updateStmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
    $updateStmt->bind_param("i", $approve_user_id);
    $updateStmt->execute();
    $updateStmt->close();
}

// Delete user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_user"])) {
    $delete_user_id = $_POST["delete_user"];
    $deleteStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->bind_param("i", $delete_user_id);
    $deleteStmt->execute();
    $deleteStmt->close();
}

// Promote user to admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["make_admin"])) {
    $make_admin_id = $_POST["make_admin"];
    $adminStmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
    $adminStmt->bind_param("i", $make_admin_id);
    $adminStmt->execute();
    $adminStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulcan IoT| Admin Panel</title>
    <link rel="icon" href="vulcanlogo2.png" type="image/x-icon">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        button { padding: 5px 10px; border: none; cursor: pointer; color: white; }
        .approve { background-color: green; }
        .approve:hover { background-color: darkgreen; }
        .delete { background-color: red; }
        .delete:hover { background-color: darkred; }
        .admin { background-color: blue; }
        .admin:hover { background-color: darkblue; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function fetchPendingUsers() {
            $.ajax({
                url: 'fetch_users.php',
                type: 'GET',
                success: function(data) {
                    $('#pending-users-table').html(data);
                }
            });
        }

        $(document).ready(function() {
            fetchPendingUsers(); // Load initially
            setInterval(fetchPendingUsers, 5000); // Refresh every 5 seconds
        });

        let inactivityTimer;
            function resetTimer() {
                clearTimeout(inactivityTimer);
                inactivityTimer = setTimeout(logoutUser, 300000); // 5 minutes = 300,000ms
            }

            function logoutUser() {
                alert("Session expired due to inactivity. You will be logged out.");
                window.location.href = "logout.php";
            }

            // Detect user activity (mouse, keyboard, or touch)
            document.addEventListener("mousemove", resetTimer);
            document.addEventListener("keypress", resetTimer);
            document.addEventListener("touchstart", resetTimer);
            document.addEventListener("click", resetTimer);

            // Start the inactivity timer when the page loads
            resetTimer();
    </script>
</head>
<body>

<h2>Welcome, Admin!</h2>
<p>You can approve pending users, delete users, or promote users to admin below.</p>
<!-- Logout Button -->
<!-- <form action="logout.php" method="post" style="text-align: right;">
    <button type="submit" style="background-color: green; color: white; padding: 10px; border: none; cursor: pointer; border-radius:5px">
        Logout
    </button>
</form> -->

<h3>Pending Users</h3>
<div id="pending-users-table">
    <!-- Pending users will be loaded here by AJAX -->
</div>

<h3>Approved Users</h3>
<table>
    <tr>
        <th>Username</th>
        <th>Phone</th>
        <th>Residence</th>
        <th>Actions</th>
    </tr>
    <?php
    $result = $conn->query("SELECT id, username, phone, residence FROM users WHERE status = 'approved' AND role != 'admin'");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['username']}</td>
                <td>{$row['phone']}</td>
                <td>{$row['residence']}</td>
                <td>
                    <form method='post' style='display:inline-block;'>
                        <button class='admin' type='submit' name='make_admin' value='{$row['id']}'>Make Admin</button>
                    </form>
                    <form method='post' style='display:inline-block;' onsubmit='return confirm(\"Are you sure you want to delete this user?\")'>
                        <button class='delete' type='submit' name='delete_user' value='{$row['id']}'>Delete</button>
                    </form>
                </td>
              </tr>";
    }
    ?>
</table>

<h3>Admins</h3>
<table>
    <tr>
        <th>Username</th>
        <th>Phone</th>
        <th>Residence</th>
    </tr>
    <?php
    $result = $conn->query("SELECT username, phone, residence FROM users WHERE role = 'admin'");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['username']}</td>
                <td>{$row['phone']}</td>
                <td>{$row['residence']}</td>
              </tr>";
    }
    ?>
</table>

</body>
</html>

<?php
$conn->close();
?>
