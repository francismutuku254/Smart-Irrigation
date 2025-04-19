<?php
$host = "localhost";
$user = "peter_richu";
$password = "Peter";
$dbname = "mydatabase";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo '<table>
        <tr>
            <th>Username</th>
            <th>Phone</th>
            <th>Residence</th>
            <th>Action</th>
        </tr>';

$result = $conn->query("SELECT id, username, phone, residence FROM users WHERE status = 'pending'");
while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['username']}</td>
            <td>{$row['phone']}</td>
            <td>{$row['residence']}</td>
            <td>
                <form method='post' action='admin.php'>
                    <button class='approve' type='submit' name='approve_user' value='{$row['id']}'>Approve</button>
                </form>
            </td>
          </tr>";
}
echo '</table>';

$conn->close();
?>
