<?php
header('Content-Type: application/json');

// Database connection details
$servername = "localhost";
$username = "peter_richu";
$password = "Francis";
$dbname = "mydatabase"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// Fetch all devices
$sql = "SELECT DISTINCT device_id FROM soil_data";
$result = $conn->query($sql);

$devices = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $device_id = $row['device_id'];

        // Check last activity time
        $status_sql = "SELECT server_time FROM soil_data WHERE device_id = '$device_id' ORDER BY server_time DESC LIMIT 1";
        $status_result = $conn->query($status_sql);
        $status = 'offline';

        if ($status_result->num_rows > 0) {
            $last_activity = strtotime($status_result->fetch_assoc()['server_time']);
            if ((time() - $last_activity) <= 2 * 3600) {
                $status = 'online';
            }
        }

        // Get latest message and moisture data
        $data_sql = "SELECT message, moisture FROM soil_data WHERE device_id = '$device_id' ORDER BY server_time DESC LIMIT 1";
        $data_result = $conn->query($data_sql);

        if ($data_result->num_rows > 0) {
            $data_row = $data_result->fetch_assoc();
            $devices[] = [
                'device_id' => $device_id,
                'message' => $data_row['message'],
                'status' => $status,
                'moisture' => $data_row['moisture'],
            ];
        }
    }
}

echo json_encode($devices);
$conn->close();
?>
