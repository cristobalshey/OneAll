<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'connect.php';

$user_id = $_SESSION['user_id'];

// ✅ Get user profile and survey status
$stmt = $conn->prepare("SELECT profile_completed, survey_completed FROM users WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($profile_completed, $survey_completed);
$stmt->fetch();
$stmt->close();

// Redirect logic
if (!$profile_completed) {
    header("Location: form_prof.php"); // profile not done
    exit();
} elseif ($survey_completed) {
    header("Location: home_game.php"); // both done
    exit();
}
// Otherwise, show survey form
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Survey</title>
  <link rel="stylesheet" href="survey.css" />
</head>
<body>
  <div class="survey-container">
    <div class="survey-title">Waste Recycling Awareness</div>
    <div class="survey-desc">
      Ang layunin ng survey na ito ay upang malaman ang iyong
      kaalaman at kasanayan sa tamang paghihiwalay ng basura sa inyong lugar.
    </div>

    <form id="surveyForm" method="post" action="submit_survey.php">
      <div class="question">
        <label>1. Alam mo ba ang tamang paraan ng paghihiwalay ng basura?</label>
        <div class="answers">
          <label><input type="radio" name="q1" value="Oo" required /> Oo</label>
          <label><input type="radio" name="q1" value="Hindi" /> Hindi</label>
        </div>
      </div>
      <div class="question">
        <label>2. Ipinaghiwalay mo ba ang iyong basura sa nabubulok at di-nabubulok na lalagyan?</label>
        <div class="answers">
          <label><input type="radio" name="q2" value="Oo" required /> Oo</label>
          <label><input type="radio" name="q2" value="Hindi" /> Hindi</label>
        </div>
      </div>
      <div class="question">
        <label>3. May malinaw bang label ang mga basurahan sa iyong paligid para sa tamang paghihiwalay ng basura?</label>
        <div class="answers">
          <label><input type="radio" name="q3" value="Oo" required /> Oo</label>
          <label><input type="radio" name="q3" value="Hindi" /> Hindi</label>
        </div>
      </div>
      <div class="question">
        <label>4. Nakatanggap ka na ba ng gabay o instruksyon kung paano ang wastong paghihiwalay ng basura?</label>
        <div class="answers">
          <label><input type="radio" name="q4" value="Oo" required /> Oo</label>
          <label><input type="radio" name="q4" value="Hindi" /> Hindi</label>
        </div>
      </div>
      <div class="question">
        <label>5. Handa ka bang sumali sa isang programa na nagtataguyod ng tamang paghihiwalay ng basura?</label>
        <div class="answers">
          <label><input type="radio" name="q5" value="Oo" required /> Oo</label>
          <label><input type="radio" name="q5" value="Hindi" /> Hindi</label>
        </div>
      </div>

      <button type="submit" class="submit-btn">I-submit ang Survey</button>
    </form>
  </div>
</body>
</html>
