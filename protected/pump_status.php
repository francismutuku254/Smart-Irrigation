<?php 
include 'session_check.php';

// Get logged-in user details
$username = isset($_SESSION["username"]) ? $_SESSION["username"] : "User";
$firstName = explode(" ", $username)[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulcan IoT | Pump Status Dashboard</title>
    <link rel="stylesheet" href="main/home.css">
    <link rel="icon" href="vulcanlogo2.png" type="image/x-icon">
    <style>
        .container {
            width: 90%;
            max-width: 750px;
            margin-top: 20px;
            text-align: center;
        
        }

        .status-box, .table-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .status-btn {
            padding: 15px 30px;
            border: none;
            color: white;
            font-size: 20px;
            font-weight: bold;
            cursor: default;
            border-radius: 8px;
            width: 100%;
            margin-top: 10px;
        }

        .on { background-color: #1fe04c; }
        .off { background-color: red; }

        .info {
            font-size: 16px;
            margin-top: 10px;
            color: #555;
        }

        .date-filter {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        input[type="date"], button#date {
            padding: 8px 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }
        .content{
             /* Centering the container */
            display: flex;
            justify-content: center; /* Centers horizontally */
            align-items: center; /* Centers vertically */
            min-height: calc(100vh - 80px); /* Ensures it takes up space below the header */
        }
    </style>
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Fixed header -->
    <div class="header">
        <div class="header-left">
            <h1>Vulcan IoT Limited</h1>
        </div>
        <div class="header-right">
            <img src="vulcan-iot-logo.png" alt="Logo" class="logo"> <!-- Replace with your logo -->
            <div class="dropdown">
                <button class="dropdown-btn">Hi, <?php echo htmlspecialchars($firstName ); ?>  &#9662;</button>
                <div class="dropdown-content">
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Side navigation -->
    <div class="menu-icon" onclick="toggleNav()">
        &#9776; <!-- Hamburger icon (3 bars) -->
    </div>
    <div id="sideNav" class="side-nav">
        <a href="#" class="close-btn" onclick="toggleNav()">&times;</a>

        <!-- Home Section -->
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
            <a href="lawn.php">
                <i class="fas fa-leaf"></i> Lawn Sections
            </a>
        </div>

        <!-- Pump Section -->
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
        <div class="container">
            <!-- Pump Status Section -->
            <div class="status-box">
                <h2>Current Pump Status</h2>
                <button id="pumpStatus" class="status-btn">Loading...</button>
                <p class="info">Last Updated: <span id="lastUpdated">Loading...</span></p>
                <p class="info">Source: <span id="controlSource">Loading...</span></p>
            </div>

            <!-- Date Filter -->
            <div class="date-filter">
                <input type="date" id="dateFilter">
                <button onclick="fetchHistoricalData()" id="date">Filter</button>
            </div>

            <!-- Historical Data Table -->
            <div class="table-box">
                <h3>Historical Pump Status</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody id="historyTable">
                        <tr><td colspan="3">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; <a href="https://www.vulcan-iot.co.ke" target="_blank">Vulcan IoT Limited</a> 
            <script type="text/javascript">document.write(new Date().getFullYear());</script>. All rights reserved.</p>
        </div>
    </footer>

    <script src="main/main.js"></script>
    <script>
        function fetchLatestStatus() {
            $.ajax({
                url: 'fetch_pump_status.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    let btn = $('#pumpStatus');
                    let status = data.status;
                    let source = data.source;
                    let time = data.server_time;
    
                    btn.text(`Pump is ${status}`);
                    btn.removeClass("on off").addClass(status === "On" ? "on" : "off");
    
                    $('#lastUpdated').text(time);
                    $('#controlSource').text(source);
                }
            });
        }
    
        function fetchHistoricalData() {
            let date = $('#dateFilter').val();
            $.ajax({
                url: 'fetch_historical_status.php',
                type: 'GET',
                data: { date: date },
                dataType: 'json',
                success: function(data) {
                    let table = $('#historyTable');
                    table.empty();
    
                    if (data.length === 0) {
                        table.append('<tr><td colspan="3">No data available</td></tr>');
                    } else {
                        data.forEach(row => {
                            table.append(`<tr>
                                <td>${row.status}</td>
                                <td>${row.source}</td>
                                <td>${row.server_time}</td>
                            </tr>`);
                        });
                    }
                }
            });
        }
    
        function updatePumpState() {
            $.ajax({
                url: 'fetch_pump_status.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    let icon = $('#pumpIcon');
                    let stateText = $('#pumpState');
    
                    if (data.status === "On") {
                        icon.css('color', '#1fe04c'); // Change icon color to green when On
                        stateText.text("Pump is On");
                    } else {
                        icon.css('color', 'red'); // Change icon color to red when Off
                        stateText.text("Pump is Off");
                    }
                }
            });
        }
    
        // Auto-refresh latest status and sidebar state every 5 seconds
        setInterval(fetchLatestStatus, 5000);
        setInterval(updatePumpState, 5000);
    
        // Initial fetch
        $(document).ready(function() {
            fetchLatestStatus();
            fetchHistoricalData();
            updatePumpState();
        });

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
    

    
</body>
</html>
