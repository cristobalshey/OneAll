<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must log in first.'); window.location.href='login.php';</script>";
    exit();
}

include 'connect.php';

$user_id      = $_SESSION['user_id'];
$fullname     = $_POST['fullname'] ?? '';
$email        = $_POST['email'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$address      = $_POST['address'] ?? '';
$gcash_number = $_POST['gcash_number'] ?? '';

// File upload handling
if (isset($_FILES['user_proof_id']) && $_FILES['user_proof_id']['error'] === UPLOAD_ERR_OK) {
    $proof_id = file_get_contents($_FILES['user_proof_id']['tmp_name']);
} else {
    echo "<script>alert('Proof ID is required.'); history.back();</script>";
    exit;
}

// Check if profile already exists
$check = $conn->prepare("SELECT id FROM profiles WHERE user_id = ?");
$check->bind_param("s", $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Profile exists → UPDATE
    $stmt = $conn->prepare("UPDATE profiles SET fullname = ?, email = ?, phone_number = ?, address = ?, gcash_number = ?, user_proof_id = ? WHERE user_id = ?");
    $stmt->bind_param("sssssss", $fullname, $email, $phone_number, $address, $gcash_number, $proof_id, $user_id);
} else {
    // Profile does not exist → INSERT
    $stmt = $conn->prepare("INSERT INTO profiles (user_id, fullname, email, phone_number, address, gcash_number, user_proof_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $user_id, $fullname, $email, $phone_number, $address, $gcash_number, $proof_id);
}

if ($stmt->execute()) {
    // Mark profile as completed
    $update = $conn->prepare("UPDATE users SET profile_completed = 1 WHERE user_id = ?");
    $update->bind_param("s", $user_id);
    $update->execute();
    $update->close();

    // Check if survey is completed
    $checkSurvey = $conn->prepare("SELECT survey_completed FROM users WHERE user_id = ?");
    $checkSurvey->bind_param("s", $user_id);
    $checkSurvey->execute();
    $checkSurvey->bind_result($survey_completed);
    $checkSurvey->fetch();
    $checkSurvey->close();

    // Redirect: if survey not completed → survey.php, else → home_game.php
    if ($survey_completed) {
        header("Location: home_game.php");
    } else {
        header("Location: survey.php");
    }
    exit();
} else {
    die("Error: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>
