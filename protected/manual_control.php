<?php 
include 'session_check.php';

// Get logged-in user details
$username = isset($_SESSION["username"]) ? $_SESSION["username"] : "User";
$firstName = explode(" ", $username)[0];
?>
!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulcan IoT| ManualControl Dashboard</title>
    <link rel="stylesheet" href="main/main.css">
    <link rel="icon" href="vulcanlogo2.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            overflow-x: hidden;
        }
        h2, h3 {
            text-align: center;
            color: #333;
        }
        .section {
            margin: 20px auto;
            padding: 20px;
            width: 90%;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .chart-section {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin: 20px auto;
            /* height: 600px; */
        }
        .chart-container {
            width: 45%;
            background-color: #fff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* height: 200px; */
        }
        canvas {
            max-width: 100%;
            height: auto;
        }
        form {
            text-align: center;
            margin: 20px 0;
        }
        .buttonM {
            width: 150px;
            height: 60px;
            font-size: 20px;
            border-radius: 10px;
            color: white;
            cursor: pointer;
        }
        #on {
            background-color: rgb(48, 226, 48);
        }
        #off {
            background-color: red;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            text-align: left;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        table th {
            background-color: #f4f4f4;
        }
        .filter-container {
            text-align: center;
            margin: 20px 20px;
        }
        .filter-container input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>

    

    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        &#9776; <!-- Hamburger icon (3 bars) -->
    </div>
    <div id="sideNav" class="side-nav">
        <a href="#" class="close-btn" onclick="toggleNav()">&times;</a>

        <!-- Home Section -->
        <div class="menu-section">
            <a href="home.php" >
                <i class="fas fa-home"></i> Home
            </a>
            <p class="subtitle">SENSORS</p>
            <a href="table_stream.php" >
                <i class="fas fa-table"></i> Table Stream
            </a>
            <a href="devices.php" >
                <i class="fas fa-cogs"></i> Devices
            </a>
            <a href="lawn.php" >
                <i class="fas fa-leaf"></i> Lawn Sections
            </a>
        </div>

        <!-- Pump Section -->
        <div class="menu-section">
            <p class="subtitle">PUMP</p>
            <a href="automatic.php" >
                <i class="fas fa-tachometer-alt"></i> Threshold
            </a>
            <a href="manual_control.php" >
                <i class="fas fa-hand-paper"></i> Manual Control
            </a>
            <a href="pump_status.php">
                <i id="pumpIcon" class="fas fa-power-off"></i> <span id="pumpState">Loading...</span>
            </a>
        </div>
        
    </div>

    <!-- Main content -->
    <div class="content" id="content">
        <h2>Smart Irrigation Pump Dashboard</h2>

    <!-- Manual Control Section -->
    <div class="section">
        <h3>Manual Control for the Pump</h3>
        <form id="manualControlForm" action="manual.php" method="post">
            <button type="button" name="control" value="ON" class="buttonM" id="on">Turn ON</button>
            <button type="button" name="control" value="OFF" class="buttonM" id="off">Turn OFF</button>
        </form>
        <div class="filter-container">
            <p>Stats filter</p>
            <input type="date" id="filterDate" onchange="fetchDashboardData()" />
        </div>
    </div>

    <!-- Line Graph and Pie Chart Sections -->
    <div class="section chart-section">
        <!-- Line Graph -->
        <div class="chart-container">
            <h3>Pump On/Off History</h3>
            <canvas id="lineGraph"></canvas>
        </div>

        <!-- Pie Chart -->
        <div class="chart-container">
            <h3>Total On/Off Time</h3>
            <canvas id="pieChart"></canvas>
        </div>
    </div>

    <!-- Daily Statistics Section -->
    <div class="section">
        <h3>Daily Pump Analytics</h3>
        <table id="analyticsTable">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be dynamically inserted -->
            </tbody>
        </table>
    </div>

    <script >
        const lineGraphCtx = document.getElementById("lineGraph").getContext("2d");
const pieChartCtx = document.getElementById("pieChart").getContext("2d");

let lineGraph = null;
let pieChart = null;

// Create a status message area
const statusMessage = document.createElement("div");
statusMessage.style.textAlign = "center";
statusMessage.style.marginTop = "10px";
statusMessage.style.color = "#333";
document.getElementById("manualControlForm").appendChild(statusMessage);

// Handle Manual Control Button Clicks
document.getElementById("manualControlForm").addEventListener("click", (e) => {
    if (e.target.tagName === "BUTTON") {
        const controlValue = e.target.innerText.includes("ON") ? "ON" : "OFF";
        
        // Send the command to the PHP script
        fetch("manual.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `control=${controlValue}`,
        })
            .then((response) => response.text())
            .then((message) => {
                // Show the status message and refresh the dashboard
                statusMessage.innerText = `Pump is turned ${controlValue}`;
                fetchDashboardData(); // Refresh the dashboard
                setTimeout(() => (statusMessage.innerText = ""), 3000); // Clear status message after 3 seconds
            })
            .catch((error) => {
                console.error("Error:", error);
                statusMessage.innerText = "An error occurred. Please try again.";
            });
    }
});

// Fetch Data for the Dashboard
function fetchDashboardData() {
    const filterDate = document.getElementById("filterDate").value;

    fetch(`fetch_logs.php?date=${filterDate}`)
        .then((response) => response.json())
        .then((data) => {
            updateLineGraph(data.lineGraph);
            updatePieChart(data.pieChart);
            updateTable(data.logs);
        })
        .catch((error) => console.error("Error:", error));
}

// Update Line Graph
function updateLineGraph(data) {
    if (lineGraph) lineGraph.destroy();
    lineGraph = new Chart(lineGraphCtx, {
        type: "line",
        data: {
            labels: data.timestamps,
            datasets: [
                {
                    label: "Pump State",
                    data: data.states,
                    borderColor: "#36a2eb",
                    fill: false,
                },
            ],
        },
    });
}

// Update Pie Chart
function updatePieChart(data) {
    if (pieChart) pieChart.destroy();
    pieChart = new Chart(pieChartCtx, {
        type: "pie",
        data: {
            labels: ["On Time", "Off Time"],
            datasets: [
                {
                    data: [data.onTime, data.offTime],
                    backgroundColor: ["#36a2eb", "#ff6384"],
                },
            ],
        },
    });
}

// Update Table
function updateTable(logs) {
    const tableBody = document.getElementById("analyticsTable").querySelector("tbody");
    tableBody.innerHTML = "";
    logs.forEach((log) => {
        const row = document.createElement("tr");
        row.innerHTML = `<td>${log.timestamp}</td><td>${log.action}</td>`;
        tableBody.appendChild(row);
    });
}

// Initial data fetch
document.addEventListener("DOMContentLoaded", fetchDashboardData);
document.getElementById("filterDate").addEventListener("change", fetchDashboardData);
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
            <p>&copy; <a href="https://www.vulcan-iot.co.ke" target="_blank">Vulcan IoT Limited</a> 
            <script type="text/javascript">document.write(new Date().getFullYear());</script>. All rights reserved.</p>
        </div>
    </footer>

    <script src="main/main.js"></script>
</body>
</html>