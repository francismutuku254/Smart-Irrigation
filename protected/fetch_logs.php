<?php
if (isset($_GET['date'])) {
    $date = $_GET['date'];

    // Connect to the database
    $mysqli = new mysqli("localhost", "peter_richu", "", "mydatabase");

    if ($mysqli->connect_error) {
        die(json_encode(["status" => "error", "message" => "Database connection failed"]));
    }

    // Fetch pump logs for the specified date
    $stmt = $mysqli->prepare("
        SELECT action, timestamp 
        FROM pump_logs 
        WHERE DATE(timestamp) = ?
        ORDER BY timestamp ASC
    ");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $logs = [];
    $onTime = 0;
    $offTime = 0;
    $timestamps = [];
    $states = [];

    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
        $timestamps[] = date("H:i", strtotime($row['timestamp']));
        $states[] = $row['action'] === "ON" ? 1 : 0;

        // Calculate total ON and OFF time
        if ($row['action'] === "ON") {
            $onTime++;
        } else {
            $offTime++;
        }
    }

    $stmt->close();
    $mysqli->close();

    echo json_encode([
        "status" => "success",
        "logs" => $logs,
        "lineGraph" => [
            "timestamps" => $timestamps,
            "states" => $states
        ],
        "pieChart" => [
            "onTime" => $onTime,
            "offTime" => $offTime
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Date not provided"]);
}
?>
