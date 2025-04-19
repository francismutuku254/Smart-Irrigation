<?php
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

// Get device ID and date filter from URL
$device_id = $_GET['device_id'];
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : date('Y-m-d'); // Default to today

// Fetch data for the selected device with the date filter
$sql = "SELECT message, moisture, server_time FROM soil_data WHERE device_id = '$device_id' AND DATE(server_time) = '$date_filter' ORDER BY server_time ASC";
$result = $conn->query($sql);

// Prepare data for the graph
$chart_data = [];
$moisture_data = [];
$time_labels = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $time_labels[] = $row['server_time'];
        $moisture_data[] = $row['moisture'];
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulcan IOT|  <?php echo $device_id; ?></title>
    <link rel="icon" href="vulcanlogo2.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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
        .button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            display: inline-block;
            margin-bottom: 20px;
            border-radius: 10px;
        }
        .button:hover {
            background-color: #45a049;
        }
        .filter {
            margin-bottom: 20px;
        }
        .filter input[type="date"] {
            padding: 5px;
            font-size: 16px;
        }
        /* Adjust canvas size */
        #moistureChart {
            max-width: 800px;  /* Adjust max width */
            height: 300px;     /* Set a fixed height */
            margin: 0 auto;    /* Center the chart */
        }
    </style>
</head>
<body>

    <!-- Back to Device Dashboard button -->
    <a href="devices.php" class="button">Back to Device Dashboard</a>

    <h1>Device Data: <?php echo $device_id; ?></h1>

    <!-- Filter by Date Form -->
    <div class="filter">
        <form action="" method="get">
            <input type="hidden" name="device_id" value="<?php echo $device_id; ?>">
            <label for="date_filter">Filter by Date:</label>
            <input type="date" id="date_filter" name="date_filter" value="<?php echo $date_filter; ?>" onchange="this.form.submit()">
        </form>
    </div>

    <!-- Line Chart for Moisture Data -->
    <canvas id="moistureChart"></canvas>

    <script>
        // Prepare the data for the chart
        var timeLabels = <?php echo json_encode($time_labels); ?>;
        var moistureData = <?php echo json_encode($moisture_data); ?>;

        // Line chart configuration
        var ctx = document.getElementById('moistureChart').getContext('2d');
        var moistureChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'Moisture Levels',
                    data: moistureData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'category',
                        labels: timeLabels
                    },
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    </script>

    <!-- Table with Device Data -->
    <table>
        <thead>
            <tr>
                <th>Message</th>
                <th>Moisture</th>
                <th>Server Time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Re-fetch the data for the table (same as the graph data)
            $conn = new mysqli($servername, $username, $password, $dbname);
            $sql = "SELECT message, moisture, server_time FROM soil_data WHERE device_id = '$device_id' AND DATE(server_time) = '$date_filter' ORDER BY server_time DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row['message'] . "</td>
                            <td>" . $row['moisture'] . "</td>
                            <td>" . $row['server_time'] . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No data available</td></tr>";
            }

            // Close the connection
            $conn->close();
            ?>
        </tbody>
    </table>

</body>
</html>
