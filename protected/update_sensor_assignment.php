<?php
$host = "localhost";
$user = "peter_richu";
$password = "Peter";
$database = "mydatabase";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$device_id = isset($_POST['device_id']) ? $_POST['device_id'] : '';
$section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if (!$device_id || $section_id == 0 || !in_array($action, ['assign', 'unassign'])) {
    echo "Invalid request.";
    exit;
}

if ($action == 'assign') {
    // Check if the device is already assigned to a section
    $checkQuery = "SELECT section_id FROM device_assignments WHERE device_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $device_id);
    $stmt->execute();
    $stmt->bind_result($existing_section);
    $stmt->fetch();
    $stmt->close();

    if ($existing_section) {
        echo "Device is already assigned to Section $existing_section.";
        exit;
    }

    // Assign the device to the section
    $assignQuery = "INSERT INTO device_assignments (device_id, section_id) VALUES (?, ?)";
    $stmt = $conn->prepare($assignQuery);
    $stmt->bind_param("si", $device_id, $section_id);
    $stmt->execute();
    $stmt->close();

    echo "Device assigned successfully.";
}

if ($action == 'unassign') {
    // Remove the device assignment
    $unassignQuery = "DELETE FROM device_assignments WHERE device_id = ?";
    $stmt = $conn->prepare($unassignQuery);
    $stmt->bind_param("s", $device_id);
    $stmt->execute();
    $stmt->close();

    echo "Device unassigned successfully.";
}

$conn->close();
?>
