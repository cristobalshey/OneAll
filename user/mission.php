<?php
session_start();
include 'connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$loggedInUserId = $_SESSION['user_id'];

// Auto-update mission statuses
$conn->query("UPDATE missions SET status='expired' WHERE end_date IS NOT NULL AND end_date < CURDATE()");
$conn->query("UPDATE missions SET status='active' WHERE status='upcoming' AND launch_date <= CURDATE()");

// Fetch active missions with submission check
$missions = $conn->query("
    SELECT m.*, IF(ms.user_id IS NOT NULL, 1, 0) AS has_submitted
    FROM missions m
    LEFT JOIN mission_submissions ms 
      ON m.mission_id = ms.mission_id AND ms.user_id = '$loggedInUserId'
    WHERE m.status='active'
    GROUP BY m.mission_id
    ORDER BY m.created_at DESC
");

// Fetch quizzes + submission status
$quizzes = $conn->query("
    SELECT q.*, s.points_earned
    FROM quizzes q
    LEFT JOIN quiz_submissions s
      ON q.quiz_id = s.quiz_id AND s.user_id = '$loggedInUserId'
    ORDER BY q.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Missions & Quizzes</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{font-family:Poppins, sans-serif;margin:0;padding:0;box-sizing:border-box;}
body{background:linear-gradient(135deg,#B8F1A0,#76D36B,#54B948,#D9F99D);display:flex;justify-content:center;align-items:center;min-height:100vh;}
.phone-frame{width:375px;height:667px;background:#E9F7EF;border-radius:24px;box-shadow:0 8px 20px rgba(0,0,0,0.25);overflow:hidden;display:flex;}
.main{width:100%;height:100%;display:flex;flex-direction:column;padding:16px;overflow-y:auto;}
.header-tabs{display:flex;justify-content:space-around;margin-bottom:16px;background:#A7DCA5;border-radius:14px;padding:6px;}
.tab-btn{flex:1;padding:12px;border:none;background:none;font-size:1rem;font-weight:600;cursor:pointer;border-radius:10px;color:#2F4F2F;transition:0.3s;}
.tab-btn.active{background:#2D6A4F;color:#fff;}
.tab-content{display:none;flex-direction:column;gap:14px;}
.tab-content.active{display:flex;}
.task-card{display:flex;flex-direction:column;background:#fff;border-radius:14px;padding:14px 18px;box-shadow:0 4px 12px rgba(0,0,0,0.15);border-left:6px solid #52B788;transition:transform 0.2s;background:#fff;}
.task-card:hover{transform:translateY(-3px);background:#D8F3DC;}
.task-title{font-size:1rem;font-weight:600;color:#2F4F2F;display:flex;align-items:center;gap:8px;}
.task-desc{font-size:0.85rem;color:#1B4332;}
.task-extra{margin-top:10px;font-size:0.85rem;color:#333;border-top:1px solid #dcdcdc;padding-top:8px;}
.do-btn{margin-top:8px;background:#2D6A4F;color:white;border:none;padding:8px 12px;border-radius:8px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:6px;}
.do-btn i{font-size:0.9rem;}
.do-btn:disabled{background:#ccc;cursor:not-allowed;}
.footer{margin-top:auto;text-align:left;}
.back-btn{background:none;border:none;font-size:1rem;color:#2D6A4F;cursor:pointer;font-weight:600;padding:8px 0;display:flex;align-items:center;gap:6px;}
</style>
</head>
<body>
<div class="phone-frame">
  <div class="main">
    <!-- Header Tabs -->
    <div class="header-tabs">
      <button class="tab-btn active" data-tab="missions">Missions</button>
      <button class="tab-btn" data-tab="quizzes">Quizzes</button>
    </div>

  <!-- Missions Section -->
<div class="tab-content active" id="missions">
  <?php while($row = $missions->fetch_assoc()): ?>
  <div class="task-card">
    <h3 class="task-title"><i class="fas fa-recycle"></i> <?= htmlspecialchars($row['mission_name']) ?></h3>
    <p class="task-desc"><?= htmlspecialchars($row['mission_description']) ?></p>
    <div class="task-extra">
      <p><strong>Launch:</strong> <?= $row['launch_date'] ?: 'TBD' ?></p>
      <p><strong>End:</strong> <?= $row['end_date'] ?: 'No deadline' ?></p>

      <?php if ($row['has_submitted']): ?>
        <button class="do-btn" disabled><i class="fas fa-check"></i> Completed</button>
        <button class="do-btn" onclick="window.location.href='qr.php?mission_id=<?= $row['mission_id'] ?>'">
          <i class="fas fa-qrcode"></i> View QR
        </button>
      <?php else: ?>
        <button class="do-btn"
          onclick="window.location.href='submit_mission.php?id=<?= $row['mission_id'] ?>'">
          <i class="fas fa-play"></i> Do Mission
        </button>
      <?php endif; ?>

    </div>
  </div>
  <?php endwhile; ?>
</div>


    <!-- Quizzes Section -->
    <div class="tab-content" id="quizzes">
      <?php while($quiz = $quizzes->fetch_assoc()): ?>
      <div class="task-card">
        <h3 class="task-title"><i class="fas fa-question-circle"></i> <?= htmlspecialchars($quiz['quiz_title']) ?></h3>
        <p class="task-desc"><?= htmlspecialchars($quiz['quiz_description']) ?></p>
        <div class="task-extra">
          <?php if (!is_null($quiz['points_earned'])): ?>
            <p style="color:green;font-weight:bold;"><i class="fas fa-check-circle"></i> You earn <?= $quiz['points_earned'] ?> points</p>
          <?php else: ?>
            <button class="do-btn" onclick="window.location.href='quiz_page.php?quiz_id=<?= $quiz['quiz_id'] ?>'">
              <i class="fas fa-pencil-alt"></i> Take Quiz
            </button>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <!-- Back Button -->
    <div class="footer">
      <button class="back-btn" onclick="window.location.href='home_game.php'"><i class="fas fa-arrow-left"></i> Back</button>
    </div>
  </div>
</div>

<script>
const tabs=document.querySelectorAll(".tab-btn");
const contents=document.querySelectorAll(".tab-content");
tabs.forEach(tab=>{
  tab.addEventListener("click",()=>{
    tabs.forEach(t=>t.classList.remove("active"));
    contents.forEach(c=>c.classList.remove("active"));
    tab.classList.add("active");
    document.getElementById(tab.dataset.tab).classList.add("active");
  });
});
</script>
</body>
</html>
