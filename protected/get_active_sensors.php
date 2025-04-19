<?php
$host = "localhost";
$user = "peter_richu";
$password = "Peter";
$database = "mydatabase";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : 0;

if ($section_id == 0) {
    echo "<p>Invalid section selected.</p>";
    exit;
}

// Fetch all unique active device IDs from soil_data
$query = "SELECT DISTINCT device_id FROM soil_data";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<div>"; // Container for device list
    while ($row = $result->fetch_assoc()) {
        $device_id = $row['device_id'];

        // Check if the device is already assigned to a section
        $checkQuery = "SELECT section_id FROM device_assignments WHERE device_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $device_id);
        $stmt->execute();
        $result_check = $stmt->get_result();
        $assigned_section = ($result_check->num_rows > 0) ? $result_check->fetch_assoc()['section_id'] : null;
        $stmt->close();

        // Determine button label, action, and style
        echo "<div style='margin-bottom: 10px;'>"; // Adding space between entries
        if ($assigned_section == $section_id) {
            echo "$device_id <button onclick=\"toggleSensor('$device_id', $section_id, 'unassign')\" 
                  style='background-color: red; color: white; border: none; padding: 5px 10px; cursor: pointer;'>Unassign</button>";
        } elseif ($assigned_section === null) {
            echo "$device_id <button onclick=\"toggleSensor('$device_id', $section_id, 'assign')\" 
                  style='background-color: green; color: white; border: none; padding: 5px 10px; cursor: pointer;'>Assign</button>";
        } else {
            echo "$device_id (Assigned to Section $assigned_section)";
        }
        echo "</div>"; // Closing div
    }
    echo "</div>"; // Closing main div
} else {
    echo "<p>No active sensors found.</p>";
}

$conn->close();
?>
