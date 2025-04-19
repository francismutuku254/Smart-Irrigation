<?php
$host = "localhost";
$user = "peter_richu"; // Change if using a different user
$password = "Peter"; // Change if you have a password
$dbname = "mydatabase";

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $phone = trim($_POST["phone"]);
    $residence = trim($_POST["residence"]);

    // Check if username already exists
    $checkUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkUser->bind_param("s", $username);
    $checkUser->execute();
    $checkUser->store_result();

    if ($checkUser->num_rows > 0) {
        echo "<script>
                alert('Username already exists! Try a different one.');
                window.location.href = 'register.html';
              </script>";
        exit();
    }
    $checkUser->close();

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO users (username, password, phone, residence, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ssss", $username, $password, $phone, $residence);

    if ($stmt->execute()) {
        echo "<script>
                alert('Registration successful! Please wait for admin approval.');
                window.location.href = 'login.html'; // Redirect to login page
              </script>";
    } else {
        echo "<script>
                alert('Error: Registration failed. Try again.');
                window.location.href = 'register.html';
              </script>";
    }
    $stmt->close();
}

$conn->close();
?>
