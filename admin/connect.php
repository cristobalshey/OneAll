<?php
$host = "if0_40056516_try"; 
$user = "if0_40056516"; 
$pass = "FBLafdC4CWG"; 
$db   = "if0_40056516_try"; 


$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
  