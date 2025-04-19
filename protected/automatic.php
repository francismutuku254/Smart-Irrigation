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
    <title>Vulcan IoT| AutomaticControl Dashboard</title>
    <link rel="stylesheet" href="main/main.css">
    <link rel="icon" type="image/shortcut-icon" href="vulcanlogo2.png">
    <style>
        body {
          font-family: Arial, sans-serif;
          margin: 20px;
          background-color: #f4f4f9;
          overflow-x: hidden;
        }
    
        h1 {
          text-align: center;
          margin-bottom: 20px;
        }
    
        .dashboard-container {
          display: flex;
          flex-wrap: wrap;
          justify-content: center;
          gap: 20px;
        }
    
        .container {
          border: 1px solid #ddd;
          border-radius: 8px;
          padding: 15px;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
          background-color: #fff;
          min-width: 320px;
          max-width: 400px;
        }
    
        .container h3 {
          text-align: center;
          margin-bottom: 15px;
          font-size: 18px;
        }
    
        .thresholds-container {
          display: flex;
          flex-direction: column;
          gap: 20px;
        }
    
        table {
          width: 100%;
          border-collapse: collapse;
          font-size: 14px;
        }
    
        table, th, td {
          border: 1px solid #ddd;
        }
    
        th, td {
          padding: 8px;
          text-align: center;
        }
    
        th {
          background-color: #f4f4f4;
        }
    
        input[type="number"] {
          width: 100%;
          padding: 5px;
          box-sizing: border-box;
          border: 1px solid #ccc;
          border-radius: 4px;
        }
    
        button {
          display: block;
          width: 100%;
          padding: 10px;
          border: none;
          border-radius: 4px;
          background-color: #28a745;
          color: white;
          font-size: 14px;
          cursor: pointer;
        }
    
        button:hover {
          background-color: #218838;
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
        <h1>Automatic Pump Control Dashboard</h1>

  <div class="dashboard-container">
    <!-- Thresholds Section -->
    <div class="container">
      <h3>Thresholds</h3>
      <div class="thresholds-container">
        <!-- Set Thresholds -->
        <div>
          <h4>Set Thresholds</h4>
          <table>
            <tbody>
              <tr>
                <td>Top Threshold:</td>
                <td><input type="number" id="topThreshold" name="topThreshold" required></td>
              </tr>
              <tr>
                <td>Bottom Threshold:</td>
                <td><input type="number" id="bottomThreshold" name="bottomThreshold" required></td>
              </tr>
              <tr>
                <td colspan="2">
                  <button type="button" id="setThresholdButton">Set Thresholds</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Latest Thresholds -->
        <div>
          <h4>Latest Thresholds</h4>
          <table id="latestThresholdsTable">
            <thead>
              <tr>
                <th>Top Threshold</th>
                <th>Bottom Threshold</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Soil Data Table -->
    <div class="container">
      <h3>Soil Data (Last 6 Hours)</h3>
      <table id="soilDataTable">
        <thead>
          <tr>
            <th>Device ID</th>
            <th>Avg Moisture</th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <td><strong>Overall Average</strong></td>
            <td id="overallAverage"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <script >
    document.addEventListener('DOMContentLoaded', function () {
    const setThresholdButton = document.getElementById('setThresholdButton');
    const latestThresholdsTable = document.getElementById('latestThresholdsTable').querySelector('tbody');
    const soilDataTable = document.getElementById('soilDataTable').querySelector('tbody');
    const overallAverageCell = document.getElementById('overallAverage');
  
    // Fetch and display data
    async function fetchData() {
      try {
        // Fetch latest thresholds
        const thresholdsResponse = await fetch('fetch_thresholds.php');
        const thresholdsData = await thresholdsResponse.json();
        
        // Update the latest thresholds table
        latestThresholdsTable.innerHTML = thresholdsData.map(row => `
          <tr>
            <td>${row.top_threshold}</td>
            <td>${row.bottom_threshold}</td>
          </tr>
        `).join('');
  
        // Fetch soil data
        const soilDataResponse = await fetch('fetch_soil_data.php');
        const soilData = await soilDataResponse.json();
        let overallSum = 0, overallCount = 0;
  
        // Update the soil data table with the latest values
        soilDataTable.innerHTML = soilData.data.map(row => {
          overallSum += parseFloat(row.avg_moisture); // Sum all the moisture values
          overallCount++;
          return `
            <tr>
              <td>${row.device_id}</td>
              <td>${parseFloat(row.avg_moisture).toFixed(2)}</td>
            </tr>
          `;
        }).join('');
  
        // Update the overall average moisture value
        overallAverageCell.textContent = overallCount ? (overallSum / overallCount).toFixed(2) : 'N/A';
      } catch (error) {
        console.error('Error fetching data:', error);
      }
    }
  
    // Handle set thresholds button click
    setThresholdButton.addEventListener('click', async function () {
      const topThreshold = document.getElementById('topThreshold').value;
      const bottomThreshold = document.getElementById('bottomThreshold').value;
  
      if (!topThreshold || !bottomThreshold) {
        alert('Please fill in both thresholds!');
        return;
      }
  
      try {
        const response = await fetch('set_thresholds.php', {
          method: 'POST',
          body: new URLSearchParams({ topThreshold, bottomThreshold }),
        });
        const result = await response.text();
        alert(result);
        fetchData(); // Refresh data after setting thresholds
      } catch (error) {
        console.error('Error setting thresholds:', error);
      }
    });
  
    // Fetch data initially when the page loads
    fetchData();
  
    // Set up a polling mechanism to fetch new data every 10 seconds
    setInterval(fetchData, 10000); // Refresh every 10 seconds
  });
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



