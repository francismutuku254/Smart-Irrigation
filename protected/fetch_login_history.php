<?php
$host = "localhost";
$user = "peter_richu"; // Change if needed
$password = "Peter"; // Change if needed
$dbname = "mydatabase";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT username, login_time FROM login_history ORDER BY login_time DESC LIMIT 20";
$result = $conn->query($sql);

$history = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
}

echo json_encode($history);

$conn->close();
?>
