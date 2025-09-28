<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Salamat</title>
  <link rel="stylesheet" href="css/survey.css">
</head>
<body>
  <div class="survey-container">
    <h2>✅ Salamat sa pagsagot ng survey!</h2>
    <a href="logout.php" class="submit-btn">Logout</a>
  </div>
</body>
</html>
