<?php
session_start();
// Set session timeout to 10 minutes (600 seconds)
$timeout = 600;

// Check if last activity is set
if (isset($_SESSION['last_activity'])) {
    $duration = time() - $_SESSION['last_activity'];
    if ($duration > $timeout) {
        session_unset();  // Unset session variables
        session_destroy(); // Destroy the session
        header("Location: login.html?session=expired");
        exit();
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$host = "localhost";
$user = "peter_richu";
$password = "Peter";
$dbname = "mydatabase";

// Database Connection
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION["user_id"];

// Fetch user data
$stmt = $conn->prepare("SELECT username, phone, residence FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username, $phone, $residence);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Error fetching user data: " . $conn->error);
}

// Update Profile Information
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["update_profile"])) {
        $new_username = trim($_POST["username"]);
        $new_phone = trim($_POST["phone"]);
        $new_residence = trim($_POST["residence"]);

        $stmt = $conn->prepare("UPDATE users SET username=?, phone=?, residence=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("sssi", $new_username, $new_phone, $new_residence, $user_id);
            if ($stmt->execute()) {
                $_SESSION["username"] = $new_username;
                echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
            } else {
                echo "<script>alert('Error updating profile.');</script>";
            }
            $stmt->close();
        } else {
            die("Error in SQL: " . $conn->error);
        }
    }

    // Change Password
    if (isset($_POST["update_password"])) {
        $current_password = $_POST["current_password"];
        $new_password = password_hash($_POST["new_password"], PASSWORD_BCRYPT);

        $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($stored_password);
            $stmt->fetch();

            if (password_verify($current_password, $stored_password)) {
                $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                if ($stmt) {
                    $stmt->bind_param("si", $new_password, $user_id);
                    if ($stmt->execute()) {
                        echo "<script>alert('Password updated successfully!'); window.location.href='profile.php';</script>";
                    }
                }
            } else {
                echo "<script>alert('Incorrect current password.');</script>";
            }
            $stmt->close();
        } else {
            die("Error in SQL: " . $conn->error);
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulcan IoT | Profile Page</title>
    <link rel="icon" href="vulcanlogo2.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <style>
        body {
            transition: background-color 0.3s, color 0.3s;
        }

        .dark-mode {
            background-color: #121212;
            color: white;
        }

        .container {
            max-width: 500px;
            margin-top: 50px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dark-mode .container {
            background: #1e1e1e;
            box-shadow: 0px 4px 6px rgba(255, 255, 255, 0.1);
           
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .back-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .back-btn:hover {
            background: #0056b3;
        }
        .logo {
            height: 50px;
        }
        .dark-mode-toggle {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .dark-mode-toggle:hover {
            background: #5a6268;
        }

        /* .toggle-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        } */

        /* .input-group-text {
            cursor: pointer;
        } */

        .btn-success, .btn-danger {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Bar with Back Button, Logo, and Dark Mode Toggle -->
        <div class="header-bar">
            <button onclick="goBack()" class="back-btn">⬅ Back</button>
            <img src="vulcan-iot-logo.png" alt="Vulcan Logo" class="logo">
            <button id="dark-mode-toggle" class="dark-mode-toggle">🌙 Dark Mode</button>
        </div>

        <h2 class="text-center">My Profile</h2>

        <form method="post">
            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" class="form-control" required>

            <label>Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" class="form-control" required>

            <label>Residence:</label>
            <input type="text" name="residence" value="<?= htmlspecialchars($residence) ?>" class="form-control" required>

            <button type="submit" name="update_profile" class="btn btn-success mt-3">Update Profile</button>
        </form>

        <h3 class="mt-4">Change Password</h3>
        <form method="post">
            <label>Current Password:</label>
            <div class="input-group">
                <input type="password" name="current_password" id="current-password" class="form-control" required>
                <span class="input-group-text" onclick="togglePassword('current-password', 'toggle-icon-current')">
                    <i id="toggle-icon-current" class="fa fa-eye-slash"></i>
                </span>
            </div>

            <label class="mt-2">New Password:</label>
            <div class="input-group">
                <input type="password" name="new_password" id="new-password" class="form-control" required>
                <span class="input-group-text" onclick="togglePassword('new-password', 'toggle-icon-new')">
                    <i id="toggle-icon-new" class="fa fa-eye-slash"></i>
                </span>
            </div>

            <button type="submit" name="update_password" class="btn btn-danger mt-3">Update Password</button>
        </form>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            }
        }

        // Dark Mode Toggle
        const darkModeToggle = document.getElementById("dark-mode-toggle");
        function setDarkMode(isDark) {
            document.body.classList.toggle("dark-mode", isDark);
            localStorage.setItem("darkMode", isDark);
        }

        darkModeToggle.addEventListener("click", () => {
            const isDark = !document.body.classList.contains("dark-mode");
            setDarkMode(isDark);
        });

        if (localStorage.getItem("darkMode") === "true") {
            setDarkMode(true);
        }
        let inactivityTimer;
            function resetTimer() {
                clearTimeout(inactivityTimer);
                inactivityTimer = setTimeout(logoutUser, 600000); // 10 minutes = 600,000ms
            }

            function logoutUser() {
                alert("Session expired due to inactivity. You will be logged out.");
                window.location.href = "logout.php";
            }

            // Detect user activity (mouse, keyboard, or touch)
            document.addEventListener("mousemove", resetTimer);
            document.addEventListener("keypress", resetTimer);
            document.addEventListener("touchstart", resetTimer);
            document.addEventListener("click", resetTimer);

            // Start the inactivity timer when the page loads
            resetTimer();
    </script>
</body>
</html>
