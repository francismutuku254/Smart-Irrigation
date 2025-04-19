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

// Query to fetch data from the soil_data table
$sql = "SELECT id, device_id, message, moisture, server_time FROM soil_data ORDER BY id DESC";
$result = $conn->query($sql);

// Prepare data as JSON
$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);


// Close the connection
$conn->close();
?>

