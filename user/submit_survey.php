<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'connect.php';

$user_id   = $_SESSION['user_id'];
$email     = $_SESSION['email'] ?? '';
$q1 = $_POST['q1'] ?? '';
$q2 = $_POST['q2'] ?? '';
$q3 = $_POST['q3'] ?? '';
$q4 = $_POST['q4'] ?? '';
$q5 = $_POST['q5'] ?? '';

// ✅ Check if survey already submitted
$check = $conn->prepare("SELECT 1 FROM survey_responses WHERE user_id = ? LIMIT 1");
$check->bind_param("s", $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header("Location: home_game.php"); // already done
    exit();
}

// Insert survey responses
$stmt = $conn->prepare("INSERT INTO survey_responses (user_id, email, q1_answer, q2_answer, q3_answer, q4_answer, q5_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $user_id, $email, $q1, $q2, $q3, $q4, $q5);
$stmt->execute();
$stmt->close();

// Mark survey as completed
$update = $conn->prepare("UPDATE users SET survey_completed = 1 WHERE user_id = ?");
$update->bind_param("s", $user_id);
$update->execute();
$update->close();

// ✅ After survey, always redirect to home_game
header("Location: home_game.php");
exit();
?>
