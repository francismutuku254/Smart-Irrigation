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
    <title>Vulcan IoT | Lawn Section</title>
    <link rel="stylesheet" href="main/main.css">
    <!-- <link rel="stylesheet" href="main/lawn.css">  -->
    <link rel="icon" href="vulcanlogo2.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
    <style>
        body{
            overflow-x: hidden;
        }
        
        h1 {
            margin-bottom: 10px;
            text-align: center;
    
            }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 70%;
            max-width: 800px;
            text-align: center;
            margin: 50px auto;  /* Centers it horizontally with some spacing */
        }

        /* FLEXBOX CONTAINER to align sections properly */
        .row {
            display: flex;
            width: 100%;
            justify-content: space-between;
            align-items: stretch; /* Ensures vertical alignment */
            }

        /* Sections Styling */
        .section {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0px;
            cursor: pointer;
        }

        /* Adjusted Section 1 */
        .section1 {
            width: 70%; /* Smaller than section 4 */
            height: 100px;
            margin-bottom: 1px;
           
        }



        /* Section 3 aligns with Section 1 */
        .section3 {
            margin-right: 10px; /* Add some space */
            width: 11%;
            height: 300px;
            writing-mode: vertical-rl;

        }

        /* Section 2 is positioned on the right */
        .section2 {
            margin-left: 10px; /* Add some space */
            margin-top: -100px;
            width: 8%;
            height: 400px;
            writing-mode: vertical-rl;
        }

        /* Section 4 spans full width */
        .section4 {
            width: 100%;
            height: 120px;
            margin-top: 1px;
        }
        .popup {
            display: none;  /* Initially hidden */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            position: absolute;
    
            /* Centering */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
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
       <!-- Lawn Section Management -->
        <h1>Lawn Section Management</h1>
        <div class="container">
            <div class="row">
                <div class="section section1" onclick="openPopup(1)">Section 1</div>
            </div>
            <div class="row">
                <div class="section section3" onclick="openPopup(3)">Section 3</div>
                <div class="section section2" onclick="openPopup(2)">Section 2</div>
            </div>
            <div class="row">
                <div class="section section4" onclick="openPopup(4)">Section 4</div>
            </div>
        </div>

        <!-- Popup Modal -->
        <div id="sensorPopup" class="popup">
            <div class="popup-content">
                <h2>Assign Sensors to Section <span id="sectionTitle"></span></h2>
                <div id="sensorList">
                    <!-- Sensors will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; <a href="https://www.vulcan-iot.co.ke" target="_blank">Vulcan IoT Limited</a> <script>document.write(new Date().getFullYear());</script>. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="main/main.js"></script>
    <script src="main/lawn.js"></script>

    <script>
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
                        stateText.textContent = "Pump is Off";
                    }
                })
                .catch(error => console.error('Error fetching pump status:', error));
        }

        // Fetch pump status every 5 seconds
        setInterval(updatePumpState, 5000);
        updatePumpState(); // Initial fetch


    </script>

</body>
</html>