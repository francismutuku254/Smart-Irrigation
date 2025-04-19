<?php
session_start();
$host = "localhost";
$user = "peter_richu"; // Change if needed
$password = "Peter"; // Change if needed
$dbname = "mydatabase";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define maximum failed attempts and lockout time
$max_attempts = 5;
$lockout_time = 300; // 5 minutes (in seconds)

// Initialize failed attempts if not set
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}

// Check if user is locked out
if (isset($_SESSION['lockout_time']) && time() - $_SESSION['lockout_time'] < $lockout_time) {
    $remaining_time = ($lockout_time - (time() - $_SESSION['lockout_time'])) / 60;
    echo "<script>alert('Too many failed attempts. Try again in " . ceil($remaining_time) . " minutes.'); window.location.href = 'login.html';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, password, status, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password, $status, $role);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        if ($status !== "approved") {
            echo "<script>alert('Your account is pending approval.'); window.location.href = 'login.html';</script>";
        } elseif (password_verify($password, $hashed_password)) {
            // Successful login, reset attempts
            $_SESSION["failed_attempts"] = 0;
            unset($_SESSION['lockout_time']);

            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;

            // **Insert into login_history table**
            $history_stmt = $conn->prepare("INSERT INTO login_history (user_id, username) VALUES (?, ?)");
            $history_stmt->bind_param("is", $id, $username);
            $history_stmt->execute();
            $history_stmt->close();

            // Redirect based on role
            if ($role === "admin") {
                echo "<script>alert('Login successful! Redirecting to admin panel...'); window.location.href = 'Admin_user.php';</script>";
            } else {
                echo "<script>alert('Login successful! Redirecting to dashboard...'); window.location.href = 'home.php';</script>";
            }
        } else {
            $_SESSION['failed_attempts']++;

            if ($_SESSION['failed_attempts'] >= $max_attempts) {
                $_SESSION['lockout_time'] = time();
                echo "<script>alert('Too many failed attempts. Try again in 5 minutes.'); window.location.href = 'login.html';</script>";
            } else {
                $remaining_attempts = $max_attempts - $_SESSION['failed_attempts'];
                echo "<script>alert('Invalid password. You have $remaining_attempts attempts left.'); window.location.href = 'login.html';</script>";
            }
        }
    } else {
        $_SESSION['failed_attempts']++;
        
        if ($_SESSION['failed_attempts'] >= $max_attempts) {
            $_SESSION['lockout_time'] = time();
            echo "<script>alert('Too many failed attempts. Try again in 5 minutes.'); window.location.href = 'login.html';</script>";
        } else {
            $remaining_attempts = $max_attempts - $_SESSION['failed_attempts'];
            echo "<script>alert('User not found. You have $remaining_attempts attempts left.'); window.location.href = 'login.html';</script>";
        }
    }
    $stmt->close();
}

$conn->close();
?>
