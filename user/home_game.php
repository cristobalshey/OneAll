<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'connect.php';

$user_id = $_SESSION['user_id'];

// ✅ Fetch user info including completion and points
$stmt = $conn->prepare("SELECT profile_completed, survey_completed, first_name, waste_points 
                        FROM users WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// ✅ Redirect if profile or survey is not done
if ($user['profile_completed'] != 1) {
    header("Location: form_prof.php");
    exit();
}
if ($user['survey_completed'] != 1) {
    header("Location: survey.php");
    exit();
}

// ✅ Prepare game data
$playerName   = $user['first_name'] ?? "Player";
$gameCoin     = $user['waste_points'] ?? 0;
$premiumCoin  = floor($gameCoin / 10);

// ✅ Engagement level logic
if ($gameCoin < 50) {
    $engagementLevel = "Beginner";
} elseif ($gameCoin < 150) {
    $engagementLevel = "Developing";
} elseif ($gameCoin < 300) {
    $engagementLevel = "Active Learner";
} else {
    $engagementLevel = "Master";
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EcoTrack Pixel Game UI</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="game.css">
</head>
<body>
  <div class="mobile-container">
    
    <!-- ✅ Top Bar -->
    <div class="top-bar">
      <div class="player-info">
        <span class="player-name"><?= htmlspecialchars($playerName); ?></span>
        <span class="engagement-level" id="engagementTitle"><?= $engagementLevel; ?></span>
      </div>

      <div class="top-right">
        <div class="currency">
          <div class="coin game-coin">
            <img src="images/icon-gamecoin.png" alt="Game Coin">
            <span id="gameCoin"><?= $gameCoin; ?></span>
          </div>
          <div class="coin premium-coin">
            <img src="images/icon-premium.png" alt="Premium Coin">
            <span><?= $premiumCoin; ?></span>
          </div>
        </div>
        <div class="notif-btn">
          <button id="notifButton">
            <i class="fa-solid fa-bell"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- ✅ Game Area -->
   <div class="game-area" id="gameArea">
  <img src="images/character.png" alt="Character" class="character" id="character">
  <!-- trash-bin moved here (sibling of game-area, child of .mobile-container) -->
<img src="images/trashclose.png" alt="Trash Bin" class="trash-bin" id="trashBin">
</div>



    <!-- ✅ Popup -->
    <div class="popup" id="popup">
      <p id="popupMessage"></p>
      <button onclick="closePopup()">OK</button>
    </div>

    <!-- ✅ Bottom Navigation -->
    <div class="bottom-nav">
        <a href="home_game.php" class="nav-item active">
            <img src="images/icon-home.png" alt="Home">
            <span>Home</span>
        </a>
        <a href="shop.php" class="nav-item">
            <img src="images/icon-shop.png" alt="Shop">
            <span>Shop</span>
        </a>
        <a href="mission.php" class="nav-item">
            <img src="images/icon-mission.png" alt="Mission">
            <span>Mission</span>
        </a>
        <a href="leaderboard.php" class="nav-item">
            <img src="images/icon-leaderboard.png" alt="Leaderboard">
            <span>Leaderboard</span>
        </a>
        <a href="profile.php" class="nav-item">
            <img src="images/icon-profile.png" alt="Profile">
            <span>Profile</span>
        </a>
    </div>
  </div>

  <!-- ✅ JS -->
  <script src="game.js"></script>
</body>
</html>
