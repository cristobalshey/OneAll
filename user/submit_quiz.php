<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$quiz_id = $_POST['quiz_id'] ?? null;
$answers = $_POST['answers'] ?? [];

if (!$quiz_id || empty($answers)) {
    die("Invalid submission.");
}

// Fetch quiz info
$quiz = $conn->query("SELECT * FROM quizzes WHERE quiz_id='$quiz_id'")->fetch_assoc();
if (!$quiz) die("Quiz not found.");

$points_per_quiz = (int)$quiz['points'];

// Fetch all questions for this quiz
$questions = $conn->query("SELECT * FROM quiz_questions WHERE quiz_id='$quiz_id'");
$total_questions = $questions->num_rows;

$score = 0;

// Evaluate answers
while ($q = $questions->fetch_assoc()) {
    $qid = $q['question_id'];
    $correct = strtoupper($q['correct_option']); // stored like A/B/C/D
    $user_answer = $answers[$qid] ?? null;

    if ($user_answer === $correct) {
        $score++;
    }
}

// Calculate points (proportional scoring)
$points_earned = round(($score / $total_questions) * $points_per_quiz);

// ✅ Check if user already submitted this quiz
$check = $conn->prepare("SELECT submission_id FROM quiz_submissions WHERE quiz_id=? AND user_id=?");
$check->bind_param("ss", $quiz_id, $user_id);
$check->execute();
$existing = $check->get_result()->fetch_assoc();
$check->close();

if ($existing) {
    // If already submitted, update
    $stmt = $conn->prepare("UPDATE quiz_submissions SET points_earned=?, submitted_at=NOW() WHERE quiz_id=? AND user_id=?");
    $stmt->bind_param("iss", $points_earned, $quiz_id, $user_id);
} else {
    // First submission
    $stmt = $conn->prepare("INSERT INTO quiz_submissions (quiz_id, user_id, points_earned) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $quiz_id, $user_id, $points_earned);
}
$stmt->execute();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Quiz Result</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #76D36B, #B8F1A0);
    height: 100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    margin:0;
}
.container {
    width: 375px;
    background:#fff;
    border-radius:20px;
    box-shadow:0 8px 25px rgba(0,0,0,0.2);
    padding:30px;
    text-align:center;
}
h2 { color:#2D6A4F; margin-bottom:10px; }
p { font-size:1.1rem; color:#333; margin:8px 0; }
.score {
    font-size:1.4rem;
    font-weight:700;
    color:#52B788;
    margin:15px 0;
}
button {
    margin-top:15px;
    padding:10px 20px;
    border:none;
    border-radius:10px;
    background:#52B788;
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}
button:hover { background:#2D6A4F; }
</style>
</head>
<body>
<div class="container">
    <h2>Quiz Completed 🎉</h2>
    <p>You answered <strong><?= $score ?></strong> out of <strong><?= $total_questions ?></strong> correctly.</p>
    <p class="score">Points Earned: <?= $points_earned ?></p>
    <form action="mission.php" method="get">
        <button type="submit">Go to Missions</button>
    </form>
</div>
</body>
</html>
