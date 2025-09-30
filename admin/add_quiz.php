<?php
session_start();
include 'connect.php'; // DB connection


$conn = new mysqli($host, $user, $pass, $db);
if (isset($_POST['add_quiz'])) {
    $title       = trim($_POST['quiz_title']);
    $desc        = trim($_POST['quiz_description']);
    $launch_date = $_POST['launch_date'];
    $end_date    = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;

    // 🔹 Auto-determine status
    $today = date("Y-m-d");
    if ($today < $launch_date) {
        $status = "upcoming";
    } elseif ($end_date && $today > $end_date) {
        $status = "expired";
    } else {
        $status = "active";
    }

    // 🔹 Generate unique quiz_id like QEC1234
    do {
        $randomNum = random_int(1000, 9999); 
        $quiz_id = "QEC" . $randomNum;

        $check = $conn->prepare("SELECT quiz_id FROM quizzes WHERE quiz_id = ?");
        $check->bind_param("s", $quiz_id);
        $check->execute();
        $res = $check->get_result();
    } while ($res->num_rows > 0);

    // 🔹 Force total points = 10
    $total_score = 10;

    // Insert quiz
    $stmt = $conn->prepare("
        INSERT INTO quizzes 
        (quiz_id, quiz_title, quiz_description, points, launch_date, end_date, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("sssisss", $quiz_id, $title, $desc, $total_score, $launch_date, $end_date, $status);
    $stmt->execute();
    $stmt->close();

    // Insert 5 questions
    for ($i = 1; $i <= 5; $i++) {
        $question   = $_POST["question_$i"];
        $option_a   = $_POST["option_a_$i"];
        $option_b   = $_POST["option_b_$i"];
        $option_c   = $_POST["option_c_$i"];
        $option_d   = $_POST["option_d_$i"];
        $correct    = $_POST["correct_$i"];
        $question_id = $i;

        if (!empty($question)) {
            $stmtQ = $conn->prepare("
                INSERT INTO quiz_questions 
                (quiz_id, question_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtQ->bind_param("sissssss", $quiz_id, $question_id, $question, $option_a, $option_b, $option_c, $option_d, $correct);
            $stmtQ->execute();
            $stmtQ->close();
        }
    }

    header("Location: projects.php?quiz_added=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Quiz | EcoTrack Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
body { margin:0; font-family:'Segoe UI',Arial,sans-serif; background:#f4f8f6; color:#222; }
.form-card{max-width:900px;margin:40px auto;padding:32px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(40,167,69,0.08);border:1px solid #e4f5e9;}
.form-card h3{margin-bottom:24px;font-size:1.4em;color:#222;font-weight:600;}
.form-card input, .form-card textarea, .form-card select{width:100%;padding:10px 12px;margin-bottom:14px;border:1px solid #ccc;border-radius:8px;font-size:1em;box-sizing:border-box;}
.form-card textarea{height:auto;}
.form-card button{background:#28a745;color:#fff;border:none;border-radius:8px;padding:10px 18px;font-size:1em;font-weight:600;cursor:pointer;transition:background 0.2s;}
.form-card button:hover{background:#218838;}
a.back-btn{display:inline-block;margin-bottom:12px;color:#28a745;text-decoration:none;font-weight:600;}
a.back-btn:hover{opacity:0.7;}
fieldset{border:1px solid #ddd;border-radius:8px;margin-bottom:20px;padding:16px;}
legend{font-weight:bold;padding:0 8px;color:#28a745;}
</style>
</head>
<body>
<div class="form-card">
  <a href="projects.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Projects</a>
  <h3>Add New Quiz</h3>
  <form method="POST">
    <!-- Quiz Details -->
    <input type="text" name="quiz_title" placeholder="Quiz Title" required>
    <textarea name="quiz_description" placeholder="Quiz Description" rows="4" required></textarea>
    <p>Total Points: <strong>10</strong> (2 points per question)</p>
    <input type="date" name="launch_date" required>
    <input type="date" name="end_date">

    <!-- ✅ Removed manual status select (auto-handled in PHP) -->

    <!-- 5 Questions -->
    <?php for ($i=1; $i<=5; $i++): ?>
      <fieldset>
        <legend>Question <?= $i ?></legend>
        <textarea name="question_<?= $i ?>" placeholder="Enter question text" rows="2" required></textarea>
        <input type="text" name="option_a_<?= $i ?>" placeholder="Option A" required>
        <input type="text" name="option_b_<?= $i ?>" placeholder="Option B" required>
        <input type="text" name="option_c_<?= $i ?>" placeholder="Option C" required>
        <input type="text" name="option_d_<?= $i ?>" placeholder="Option D" required>
        <select name="correct_<?= $i ?>" required>
          <option value="">-- Correct Option --</option>
          <option value="A">Option A</option>
          <option value="B">Option B</option>
          <option value="C">Option C</option>
          <option value="D">Option D</option>
        </select>
      </fieldset>
    <?php endfor; ?>

    <button type="submit" name="add_quiz"><i class="fas fa-plus"></i> Add Quiz</button>
  </form>
</div>
</body>
</html>
