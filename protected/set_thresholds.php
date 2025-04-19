<?php
$conn = new mysqli("localhost", "peter_richu", "Peter", "mydatabase");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$topThreshold = $_POST['topThreshold'];
$bottomThreshold = $_POST['bottomThreshold'];

$sql = "INSERT INTO thresholds (top_threshold, bottom_threshold, created_at)
        VALUES ('$topThreshold', '$bottomThreshold', NOW())";

if ($conn->query($sql) === TRUE) {
    echo "Thresholds set successfully.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
