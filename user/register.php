<?php
session_start();
include 'connect.php';

if (isset($_POST['signUp'])) {
    $firstName = trim($_POST['fName']);
    $lastName  = trim($_POST['lName']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $role      = $_POST['role'] ?? 'user';

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $userId = "U" . date("YmdHis") . rand(100, 999);
    $waste_points = 0;
    $profile_completed = 0;
    $survey_completed = 0;

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) die("Prepare failed: " . $conn->error);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email already exists.'); window.location.href='login.php';</script>";
        exit();
    }
    $stmt->close();

    // Insert new user
    $insert = $conn->prepare("INSERT INTO users 
        (user_id, first_name, last_name, email, password, role, status, waste_points, profile_completed, survey_completed) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
    if (!$insert) die("Prepare failed: " . $conn->error);

    $insert->bind_param(
        "ssssssiii",
        $userId,
        $firstName,
        $lastName,
        $email,
        $hashedPassword,
        $role,
        $waste_points,
        $profile_completed,
        $survey_completed
    );

    if ($insert->execute()) {
        // ✅ Generate QR code using Google Charts API (no local library needed)
        $qrUrl = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($userId);

        // Optionally save the QR code locally (optional)
        $qrDir = "qrcodes/";
        if (!file_exists($qrDir)) mkdir($qrDir, 0777, true);
        $qrFile = $qrDir . $userId . ".png";
        file_put_contents($qrFile, file_get_contents($qrUrl));

        echo "<script>
            alert('Account created! Your QR code has been generated. Wait for admin approval.');
            window.location.href='login.php';
        </script>";
        exit();
    } else {
        die("Error: " . $insert->error);
    }
}

if (isset($_POST['signIn'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) die("Prepare failed: " . $conn->error);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            if (strtolower($row['status']) === "approved") {
                $_SESSION['email']   = $row['email'];
                $_SESSION['role']    = $row['role'];
                $_SESSION['user_id'] = $row['user_id'];

                // ✅ Check profile
                $checkProfile = $conn->prepare("SELECT user_id FROM profiles WHERE user_id = ?");
                $checkProfile->bind_param("s", $row['user_id']);
                $checkProfile->execute();
                $checkProfile->store_result();

                // ✅ Check survey
                $checkSurvey = $conn->prepare("SELECT user_id FROM survey_responses WHERE user_id = ?");
                $checkSurvey->bind_param("s", $row['user_id']);
                $checkSurvey->execute();
                $checkSurvey->store_result();

                // Redirect based on completion
                if ($checkProfile->num_rows > 0 && $checkSurvey->num_rows > 0) {
                    header("Location: home_game.php"); // both done
                } elseif ($checkProfile->num_rows > 0) {
                    header("Location: survey.php"); // has profile but no survey
                } else {
                    header("Location: form_prof.php"); // no profile yet
                }
                exit();
            } else {
                echo "<script>alert('Account pending approval.'); window.location.href='login.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid password.'); window.location.href='login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('No account found.'); window.location.href='login.php';</script>";
        exit();
    }
}
?>
