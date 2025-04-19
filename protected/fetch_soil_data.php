<?php
// Database connection details
$servername = "localhost";
$username = "peter_richu";
$password = "Peter";
$dbname = "mydatabase"; // Replace with your database name

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the current time and calculate the time for 6 hours ago
    $currentTime = date('Y-m-d H:i:s');
    $timeLimit = date('Y-m-d H:i:s', strtotime('-6 hours'));

    // Prepare and execute the SQL query to fetch data from the last 6 hours
    $stmt = $pdo->prepare("SELECT device_id, AVG(moisture) AS avg_moisture
                           FROM soil_data
                           WHERE server_time >= :timeLimit
                           GROUP BY device_id");
    $stmt->bindParam(':timeLimit', $timeLimit);
    $stmt->execute();

    // Fetch the data as an associative array
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the overall average moisture for all devices in the last 6 hours
    $overallStmt = $pdo->prepare("SELECT AVG(moisture) AS overall_avg 
                                  FROM soil_data 
                                  WHERE server_time >= :timeLimit");
    $overallStmt->bindParam(':timeLimit', $timeLimit);
    $overallStmt->execute();
    $overallData = $overallStmt->fetch(PDO::FETCH_ASSOC);

    // Return the data as a JSON response
    echo json_encode([
        'data' => $data,
        'overall_avg' => $overallData ? $overallData['overall_avg'] : null
    ]);
    
} catch (PDOException $e) {
    // Handle any database errors
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>


