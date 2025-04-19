<?php
$host = "localhost";
$user = "peter_richu";
$password = "Peter";
$database = "mydatabase";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT status, source, server_time FROM pump_status ORDER BY server_time DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["status" => "Unknown", "source" => "Unknown", "server_time" => "Unknown"]);
}

$conn->close();
?>
