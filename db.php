<?php
$host = "sql302.infinityfree.com";
$user = "if0_41060566";
$pass = "Jaqujim123";
$db   = "if0_41060566_leave_management_system";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
