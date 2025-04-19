<?php
$conn = new mysqli("localhost", "peter_richu", "Peter", "mydatabase");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT top_threshold, bottom_threshold FROM thresholds ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>
