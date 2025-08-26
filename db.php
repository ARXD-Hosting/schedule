<?php
$servername = "HOSTNAME (IP)"; // (Assuming the port your database on is 3306)
$username = "DATABASE-USERNAME";
$password = "DATABASE-PASSWORD";
$dbname = "DATABASE-NAME";

// Admin credentials
$admin_user = "admin";
$admin_pass = "changeme";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>