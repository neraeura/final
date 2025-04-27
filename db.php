<?php
// db.php - Database connection for Mystic Pet Dating App

$servername = "localhost";         
$username = "ukhkyawbzhrnk";  
$password = "cs20nora";
$db = "db2pge9juhkypz";           

// Create and check connection
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->select_db($db);

?>

