<?php
session_start();

$host = "localhost";
$user = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "irrigation_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

$type = $_GET['type'] ?? 'soil_moisture';
$dateRange = $_GET['range'] ?? '';

// Extract start and end dates
if ($dateRange) {
    $dates = explode(" - ", $dateRange);
    $start_date = date("Y-m-d", strtotime($dates[0]));
    $end_date = date("Y-m-d", strtotime($dates[1]));
} else {
    $start_date = date("Y-m-d");
    $end_date = date("Y-m-d");
}

$query = "";

if ($type == "soil_moisture") {
    $query = "SELECT id, 'Soil Moisture' AS type, moisture AS value, server_time AS date 
              FROM soil_data 
              WHERE DATE(server_time) BETWEEN '$start_date' AND '$end_date'";
} elseif ($type == "pump_activity") {
    $query = "SELECT id, 'Pump Status' AS type, message AS value, server_time AS date 
              FROM pump_logs 
              WHERE DATE(server_time) BETWEEN '$start_date' AND '$end_date'";
} elseif ($type == "login_history") {
    $query = "SELECT id, 'Login History' AS type, username AS value, login_time AS date 
              FROM login_history 
              WHERE DATE(login_time) BETWEEN '$start_date' AND '$end_date'";
}

$result = $conn->query($query);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>
