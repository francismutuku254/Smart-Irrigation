<?php
include 'session_check.php';

// Get logged-in user details
$username = isset($_SESSION["username"]) ? $_SESSION["username"] : "User";
$firstName = explode(" ", $username)[0];

// Database connection details
$servername = "localhost";
$username = "peter_richu";
$password = "Peter";
$dbname = "mydatabase"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Close the connection (since we will fetch data via AJAX)
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulcan IoT | Devices Dashboard</title>
    <link rel="stylesheet" href="main/main.css">
    <link rel="icon" href="vulcanlogo2.png" type="image/x-icon">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body{
            overflow-x: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .online {
            color: rgb(69, 182, 69);
            font-size: 20px;
        }
        .offline {
            color: red;
            font-size: 20px;
        }
        .button {
            padding: 5px 10px;
            background-color: blue;
            color: white;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #45a049;
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
                <button class="dropdown-btn">Hi, <?php echo htmlspecialchars($firstName ); ?> &#9662;</button>
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
        <div class="menu-section">
            <a href="home.php">
                <i class="fas fa-home"></i> Home
            </a>
            <p class="subtitle">SENSORS</p>
            <a href="table_stream.php">
                <i class="fas fa-table"></i> Table Stream
            </a>
            <a href="devices.php">
                <i class="fas fa-cogs"></i> Devices
            </a>
            <a href="lawn.php" >
                <i class="fas fa-leaf"></i> Lawn Sections
            </a>
        </div>

        <div class="menu-section">
            <p class="subtitle">PUMP</p>
            <a href="automatic.php">
                <i class="fas fa-tachometer-alt"></i> Threshold
            </a>
            <a href="manual_control.php">
                <i class="fas fa-hand-paper"></i> Manual Control
            </a>
            <a href="pump_status.php">
                <i id="pumpIcon" class="fas fa-power-off"></i> <span id="pumpState">Loading...</span>
            </a>
            
        </div>
    </div>

    <!-- Main content -->
    <div class="content" id="content">
        <h1>Device Dashboard</h1>
        <table>
            <thead>
                <tr>
                    <th>Device ID</th>
                    <th>Message</th>
                    <th>Moisture</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="devices-table">
                <!-- Data will be inserted here dynamically -->
            </tbody>
        </table>

        <script>
            function fetchDeviceData() {
                fetch('fetch_devices.php')
                    .then(response => response.json())
                    .then(devices => {
                        const tableBody = document.getElementById('devices-table');
                        tableBody.innerHTML = ''; // Clear the table

                        devices.forEach(device => {
                            const tr = document.createElement('tr');
                            const statusClass = device.status === 'online' ? 'online' : 'offline';
                            
                            tr.innerHTML = `
                                <td>${device.device_id}</td>
                                <td>${device.message}</td>
                                <td>${device.moisture}</td>
                                <td class="${statusClass}">${device.status}</td>
                                <td><a href="device-data.php?device_id=${device.device_id}">
                                    <button class="button">View Data</button>
                                </a></td>
                            `;
                            tableBody.appendChild(tr);
                        });
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }

            // Fetch data initially
            fetchDeviceData();

            // Refresh data every 5 seconds
            setInterval(fetchDeviceData, 5000);
            // Function to update pump state in sidebar
            function updatePumpState() {
                fetch('fetch_pump_status.php')
                    .then(response => response.json())
                    .then(data => {
                        const icon = document.getElementById('pumpIcon');
                        const stateText = document.getElementById('pumpState');
        
                        if (data.status === "On") {
                            icon.style.color = '#1fe04c'; // Green for On
                            stateText.textContent = "Pump is On";
                        } else {
                            icon.style.color = 'red'; // Red for Off
                            stateText.textContent = " Pump is Off";
                        }
                    })
                    .catch(error => console.error('Error fetching pump status:', error));
            }
        
            // Fetch pump status every 5 seconds
            setInterval(updatePumpState, 5000);
            updatePumpState(); // Initial fetch

            let inactivityTimer;
            function resetTimer() {
                clearTimeout(inactivityTimer);
                inactivityTimer = setTimeout(logoutUser, 600000); // 5 minutes = 300,000ms
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
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; <a href="https://www.vulcan-iot.co.ke" target="_blank">Vulcan IoT Limited</a> <script type="text/javascript">document.write(new Date().getFullYear());</script>. All rights reserved.</p>
        </div>
    </footer>

    <script src="main/main.js"></script>
</body>
</html>
