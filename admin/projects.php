<?php
session_start();
include 'connect.php'; // DB connection

// ======= Auto-update mission statuses =======
$conn->query("UPDATE missions SET status='expired' WHERE end_date IS NOT NULL AND end_date < CURDATE()");
$conn->query("UPDATE missions SET status='active' WHERE status='upcoming' AND launch_date <= CURDATE()");

// ======= Auto-update quiz statuses =======
$conn->query("UPDATE quizzes SET status='expired' WHERE end_date IS NOT NULL AND end_date < CURDATE()");
$conn->query("UPDATE quizzes SET status='active' WHERE status='upcoming' AND launch_date <= CURDATE()");

// ======= Handle Delete =======
if (isset($_GET['delete_mission'])) {
    $id = $_GET['delete_mission'];
    $stmt = $conn->prepare("DELETE FROM missions WHERE mission_id=?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    header("Location: mission_quiz.php");
    exit;
}
if (isset($_GET['delete_quiz'])) {
    $id = $_GET['delete_quiz'];
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE quiz_id=?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    header("Location: mission_quiz.php");
    exit;
}

// ======= Fetch Missions =======
$activeMissions   = $conn->query("SELECT * FROM missions WHERE status='active' ORDER BY created_at DESC");
$upcomingMissions = $conn->query("SELECT * FROM missions WHERE status='upcoming' ORDER BY created_at DESC");
$expiredMissions  = $conn->query("SELECT * FROM missions WHERE status='expired' ORDER BY created_at DESC");

// ======= Fetch Quizzes =======
$activeQuizzes   = $conn->query("SELECT * FROM quizzes WHERE status='active' ORDER BY created_at DESC");
$upcomingQuizzes = $conn->query("SELECT * FROM quizzes WHERE status='upcoming' ORDER BY created_at DESC");
$expiredQuizzes  = $conn->query("SELECT * FROM quizzes WHERE status='expired' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Missions & Quizzes | EcoTrack Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="projects.css">
  <style>
    .qr-thumb {
      width: 50px;
      height: 50px;
      border: 1px solid #ccc;
      border-radius: 6px;
      transition: 0.3s;
    }
    .qr-thumb:hover {
      transform: scale(1.2);
      border-color: #28a745;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h1>EcoTrack</h1>
    <nav>
      <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="index.php"><i class="fas fa-users"></i> User Management</a>
      <a href="wastereports.php"><i class="fas fa-file-alt"></i> Waste Reports</a>
      <a href="projects.php" class="active"><i class="fas fa-leaf"></i> Projects</a>
      <a href="feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a>
      <a href="notification.php"><i class="fas fa-bell"></i> Notifications</a>
      <a href="donations.php"><i class="fas fa-hand-holding-heart"></i> Donations</a>
    </nav>
    <div class="sidebar-footer">&copy; 2025 EcoTrack Admin</div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <span class="title">Missions & Quizzes</span>
      <div class="header-actions">
        <div class="profile">
          <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="John Doe">
          <span>John Doe</span>
        </div>
      </div>
    </div>

    <!-- ===== Missions Section ===== -->
    <?php
    $missionSections = [
        'Active Missions' => $activeMissions,
        'Upcoming Missions' => $upcomingMissions,
        'Expired Missions' => $expiredMissions
    ];
    foreach ($missionSections as $title => $missions):
    ?>
    <div class="section-card">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <h3><?= $title ?></h3>
        <?php if ($title === 'Active Missions'): ?>
          <a href="add_mission.php" class="add-btn"><i class="fas fa-plus"></i> Add Mission</a>
        <?php endif; ?>
      </div>
      <table class="missions-table">
        <thead>
          <tr>
            <th>Mission</th>
            <th>Description</th>
            <th>Points</th>
            <th>Code</th>
            <?php if ($title !== 'Active Missions'): ?>
              <th>Launch Date</th>
              <th>End Date</th>
            <?php endif; ?>
            <th>Status</th>
            <?php if ($title !== 'Expired Missions'): ?>
              <th>Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if ($missions->num_rows > 0): ?>
            <?php while($row = $missions->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['mission_name']) ?></td>
              <td><?= htmlspecialchars($row['mission_description']) ?></td>
              <td><?= intval($row['points_allocated']) ?> pts</td>
              <td>
                <?php if (!empty($row['qr_id'])): ?>
                  <?= htmlspecialchars($row['qr_id']) ?><br>
                  <a href="qrcodes/<?= urlencode($row['qr_id']) ?>.png" target="_blank" download>
                    <img src="qrcodes/<?= urlencode($row['qr_id']) ?>.png" alt="QR" class="qr-thumb">
                  </a>
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </td>
              <?php if ($title !== 'Active Missions'): ?>
                <td><?= $row['launch_date'] ? date("M d, Y", strtotime($row['launch_date'])) : 'TBD' ?></td>
                <td><?= $row['end_date'] ? date("M d, Y", strtotime($row['end_date'])) : 'TBD' ?></td>
              <?php endif; ?>
              <td><span class="status-badge"><?= ucfirst($row['status']) ?></span></td>
              <?php if ($title !== 'Expired Missions'): ?>
              <td class="action-icons">
                <a href="edit_mission.php?id=<?= urlencode($row['mission_id']) ?>"><i class="fas fa-edit"></i></a>
                <a href="mission_quiz.php?delete_mission=<?= urlencode($row['mission_id']) ?>" onclick="return confirm('Delete this mission?')"><i class="fas fa-trash"></i></a>
              </td>
              <?php endif; ?>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7">No missions found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php endforeach; ?>

    <!-- ===== Quizzes Section ===== -->
    <?php
    $quizSections = [
        'Active Quizzes' => $activeQuizzes,
        'Upcoming Quizzes' => $upcomingQuizzes,
        'Expired Quizzes' => $expiredQuizzes
    ];
    foreach ($quizSections as $title => $quizzes):
    ?>
    <div class="section-card">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <h3><?= $title ?></h3>
        <?php if ($title === 'Active Quizzes'): ?>
          <a href="add_quiz.php" class="add-btn"><i class="fas fa-plus"></i> Add Quiz</a>
        <?php endif; ?>
      </div>
      <table class="missions-table">
        <thead>
          <tr>
            <th>Quiz</th>
            <th>Description</th>
            <th>Points</th>
            <th>Launch Date</th>
            <th>End Date</th>
            <th>Status</th>
            <?php if ($title !== 'Expired Quizzes'): ?>
              <th>Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if ($quizzes->num_rows > 0): ?>
            <?php while($quiz = $quizzes->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($quiz['quiz_title']) ?></td>
              <td><?= htmlspecialchars($quiz['quiz_description']) ?></td>
              <td>10 <small>(2 pts per correct answer)</small></td>
              <td><?= $quiz['launch_date'] ? date("M d, Y", strtotime($quiz['launch_date'])) : 'TBD' ?></td>
              <td><?= $quiz['end_date'] ? date("M d, Y", strtotime($quiz['end_date'])) : 'TBD' ?></td>
              <td><span class="status-badge"><?= ucfirst($quiz['status']) ?></span></td>
              <?php if ($title !== 'Expired Quizzes'): ?>
              <td class="action-icons">
                <a href="edit_quiz.php?quiz_id=<?= urlencode($quiz['quiz_id']) ?>"><i class="fas fa-edit"></i></a>
                <a href="mission_quiz.php?delete_quiz=<?= urlencode($quiz['quiz_id']) ?>" onclick="return confirm('Delete this quiz?')"><i class="fas fa-trash"></i></a>
              </td>
              <?php endif; ?>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7">No quizzes found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
