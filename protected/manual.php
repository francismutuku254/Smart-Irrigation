<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controlValue = $_POST['control']; // ON or OFF
    $broker = "64.181.230.240"; // Broker IP
    $topic = "test/topic";     // MQTT Topic

    // Create a JSON message to include both control and source
    $message = json_encode(["command" => $controlValue, "source" => "Manual"]);

    // Publish the JSON message
    $command = 'mosquitto_pub -h ' . $broker . ' -t ' . $topic . ' -m \'' . $message . '\'';

    $output = [];
    $return_var = 0;
    exec($command . ' 2>&1', $output, $return_var);

    if ($return_var === 0) {
        echo "Pump is turned " . $controlValue;

        // Log the event to the database
        $mysqli = new mysqli("localhost", "peter_richu", "Peter", "mydatabase");
        $stmt = $mysqli->prepare("INSERT INTO pump_logs (action,timestamp) VALUES (?, NOW())");
        $stmt->bind_param("s", $controlValue);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
    } else {
        echo "Error executing command: " . implode("\n", $output);
    }
}
?>
