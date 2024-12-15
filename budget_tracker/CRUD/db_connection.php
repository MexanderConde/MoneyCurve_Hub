<?php
// db_connection.php

// Database connection variables
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "budget_tracker";

// Create a connection to the database
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
