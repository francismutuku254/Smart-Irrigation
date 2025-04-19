<?php
session_start();

// Set session timeout to 5 minutes (300 seconds)
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

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo "<script>
            alert('Access denied. Please log in first.');
            window.location.href = 'login.html';
          </script>";
    exit();
}

