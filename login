<?php
session_start();

// Capture the username and password
$username = $_POST['username'];
$password = $_POST['password']; // Optional: Add password validation

// Store username in session
$_SESSION['username'] = $username;

// Set a cookie if "Remember Me" is checked
if (isset($_POST['remember'])) {
    setcookie("username", $username, time() + 3600, "/"); // Cookie expires in 1 hour
}

echo "Welcome, " . $username . "! Go to your <a href='dashboard.php'>dashboard</a>.";
?>