<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'connect.php';

$user_id = $_SESSION['user_id'];

// ✅ Fetch profile and survey status from users table
$stmt = $conn->prepare("SELECT profile_completed, survey_completed FROM users WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($profile_completed, $survey_completed);
$stmt->fetch();
$stmt->close();

// Redirect logic
if ($profile_completed && $survey_completed) {
    // Both done → go to game
    header("Location: home_game.php");
    exit();
} elseif ($profile_completed && !$survey_completed) {
    // Profile done but survey not → go to survey
    header("Location: survey.php");
    exit();
}
// Otherwise, show the profile form
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Profile</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="form.css">
</head>
<body>
  <form class="profile-form" action="submit_form.php" method="post" enctype="multipart/form-data">
    <h2><i class="fa-solid fa-user"></i> Profile</h2>
    <p class="subtitle">Complete your details to unlock features</p>

    <div class="form-group">
      <label><i class="fa-solid fa-id-card"></i> Fullname</label>
      <input type="text" name="fullname" required>
    </div>

    <div class="form-group">
      <label><i class="fa-solid fa-envelope"></i> Email</label>
      <input type="email" name="email" required>
    </div>

    <div class="form-group">
      <label><i class="fa-solid fa-phone"></i> Phone Number</label>
      <input type="text" name="phone_number" required>
    </div>

    <div class="form-group">
      <label><i class="fa-solid fa-map-marker-alt"></i> Address</label>
      <textarea name="address" rows="2" required></textarea>
    </div>

    <div class="form-group">
      <label><i class="fa-brands fa-cc-apple-pay"></i> GCash Number</label>
      <input type="text" name="gcash_number" required>
    </div>

    <div class="form-group">
      <label><i class="fa-solid fa-upload"></i> Upload Proof ID</label>
      <input type="file" name="user_proof_id" class="file-input" required>
    </div>

    <button type="submit" class="submit-btn"><i class="fa-solid fa-check"></i> Submit</button>
    <button type="button" class="back-btn" onclick="history.back()"><i class="fa-solid fa-arrow-left"></i> Back</button>
  </form>
</body>
</html>
