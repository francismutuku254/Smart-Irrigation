<?php
session_start();

// Set session timeout to 5 minutes (300 seconds)
$timeout = 300;

// Check if last activity is set
if (isset($_SESSION['last_activity'])) {
    $duration = time() - $_SESSION['last_activity'];
    if ($duration > $timeout) {
        session_unset();
        session_destroy();
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulcan IoT | Login History</title>
    <link rel="stylesheet" href="main/main.css">
    <link rel="icon" href="vulcanlogo2.png" type="image/png">
    <script src="main/main.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            /* font-family: Arial, sans-serif;
            text-align: center; */
            overflow-x: hidden;
        }
        table {
            width: 100%;
            margin: auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
            
        }
    </style>
</head>
<body>

    <!-- Fixed header -->
    <div class="header">
        <div class="header-left">
            <h1>Vulcan IoT Limited</h1>
        </div>
        <div class="header-right">
            <img src="vulcan-iot-logo.png" alt="Logo" class="logo">
            <div class="dropdown">
                <button class="dropdown-btn">Hi, Admin &#9662;</button>
                <div class="dropdown-content">
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Side navigation -->
    <div class="menu-icon" onclick="toggleNav()">
        &#9776;
    </div>
    <div id="sideNav" class="side-nav">
        <a href="#" class="close-btn" onclick="toggleNav()">&times;</a>
        <!-- <div class="menu-section">
            <a href="home.php"><i class="fas fa-home"></i> Home</a>
            <p class="subtitle">SENSORS</p>
            <a href="table_stream.php"><i class="fas fa-table"></i> Table Stream</a>
            <a href="devices.php"><i class="fas fa-cogs"></i> Devices</a>
            <a href="lawn.php"><i class="fas fa-leaf"></i> Lawn Sections</a>
        </div> -->
        <!-- <div class="menu-section">
            <p class="subtitle">PUMP</p>
            <a href="automatic.php"><i class="fas fa-tachometer-alt"></i> Threshold</a>
            <a href="manual_control.php"><i class="fas fa-hand-paper"></i> Manual Control</a>
            <a href="pump_status.php">
                <i id="pumpIcon" class="fas fa-power-off"></i> <span id="pumpState">Loading...</span>
            </a>
        </div> -->
        <div class="menu-section">
            <p class="subtitle">ADMIN</p>
            <a href="Admin_user.php"><i class="fas fa-user-shield"></i> Admin Panel</a>
            <a href="login_history_dashboard.php"><i class="fas fa-history"></i> Login History</a>
            <a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>


            
        </div>
    </div>

    <!-- Main content -->
    <div class="content" id="content">
        <h2>Recent Login History</h2>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Login Time</th>
                </tr>
            </thead>
            <tbody id="history-body"></tbody>
        </table>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; <a href="https://www.vulcan-iot.co.ke" target="_blank">Vulcan IoT Limited</a> <script>document.write(new Date().getFullYear());</script>. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function fetchLoginHistory() {
            $.ajax({
                url: "fetch_login_history.php",
                type: "GET",
                dataType: "json",
                success: function (data) {
                    var rows = "";
                    data.forEach(function (item) {
                        rows += "<tr><td>" + item.username + "</td><td>" + item.login_time + "</td></tr>";
                    });
                    $("#history-body").html(rows);
                },
                error: function () {
                    console.log("Error fetching login history.");
                }
            });
        }

        setInterval(fetchLoginHistory, 5000);
        fetchLoginHistory();

        let inactivityTimer;
        function resetTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(logoutUser, 300000);
        }

        function logoutUser() {
            alert("Session expired due to inactivity. You will be logged out.");
            window.location.href = "logout.php";
        }

        document.addEventListener("mousemove", resetTimer);
        document.addEventListener("keypress", resetTimer);
        document.addEventListener("touchstart", resetTimer);
        document.addEventListener("click", resetTimer);

        resetTimer();
    </script>

</body>
</html>

<?php $conn->close(); ?>
