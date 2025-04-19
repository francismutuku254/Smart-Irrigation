<?php
$host = "localhost";
$user = "peter_richu";
$password = "Peter";
$database = "mydatabase";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$date = $_GET['date'] ?? date("Y-m-d");

$sql = "SELECT status, source, server_time FROM pump_status WHERE DATE(server_time) = ? ORDER BY server_time DESC" ;
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

$conn->close();
?>
