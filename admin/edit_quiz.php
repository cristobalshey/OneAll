<?php
session_start();
include 'connect.php';

// Redirect if no quiz ID is provided
if (!isset($_GET['quiz_id'])) {
    header("Location: projects.php");
    exit;
}

$quiz_id = $_GET['quiz_id'];

// Fetch quiz info
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE quiz_id = ?");
$stmt->bind_param("s", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$quiz) {
    header("Location: projects.php");
    exit;
}

// Fetch questions
$questions = [];
$stmtQ = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_id ASC");
$stmtQ->bind_param("s", $quiz_id);
$stmtQ->execute();
$resQ = $stmtQ->get_result();
while ($row = $resQ->fetch_assoc()) {
    $questions[$row['question_id']] = $row;
}
$stmtQ->close();

// Handle form submission
if (isset($_POST['edit_quiz'])) {
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

    // Force total points = 10
    $total_score = 10;

    // Update quiz
    $stmt = $conn->prepare("
        UPDATE quizzes 
        SET quiz_title=?, quiz_description=?, points=?, launch_date=?, end_date=?, status=?
        WHERE quiz_id=?
    ");
    $stmt->bind_param("ssissss", $title, $desc, $total_score, $launch_date, $end_date, $status, $quiz_id);
    $stmt->execute();
    $stmt->close();

    // Update 5 questions
    for ($i=1; $i<=5; $i++) {
        $question   = $_POST["question_$i"];
        $option_a   = $_POST["option_a_$i"];
        $option_b   = $_POST["option_b_$i"];
        $option_c   = $_POST["option_c_$i"];
        $option_d   = $_POST["option_d_$i"];
        $correct    = $_POST["correct_$i"];
        $question_id = $i;

        $stmtQ = $conn->prepare("
            UPDATE quiz_questions
            SET question_text=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=?
            WHERE quiz_id=? AND question_id=?
        ");
        $stmtQ->bind_param("sssssssi", $question, $option_a, $option_b, $option_c, $option_d, $correct, $quiz_id, $question_id);
        $stmtQ->execute();
        $stmtQ->close();
    }

    header("Location: projects.php?quiz_updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Quiz | EcoTrack Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
body { margin:0; font-family:'Segoe UI',Arial,sans-serif; background:#f4f8f6; color:#222; }
.form-card{max-width:900px;margin:40px auto;padding:32px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(40,167,69,0.08);border:1px solid #e4f5e9;}
.form-card h3{margin-bottom:24px;font-size:1.4em;color:#222;font-weight:600;}
.form-card input, .form-card textarea{width:100%;padding:10px 12px;margin-bottom:14px;border:1px solid #ccc;border-radius:8px;font-size:1em;box-sizing:border-box;}
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
    <h3>Edit Quiz (<?= htmlspecialchars($quiz_id) ?>)</h3>
    <form method="POST">
        <!-- Quiz Details -->
        <input type="text" name="quiz_title" placeholder="Quiz Title" required 
               value="<?= htmlspecialchars($quiz['quiz_title']) ?>">
        <textarea name="quiz_description" placeholder="Quiz Description" rows="4" required><?= htmlspecialchars($quiz['quiz_description']) ?></textarea>
        <input type="date" name="launch_date" required value="<?= htmlspecialchars($quiz['launch_date']) ?>">
        <input type="date" name="end_date" value="<?= htmlspecialchars($quiz['end_date']) ?>">

        <!-- ✅ Removed manual status select (status auto-calculated) -->

        <!-- Fixed Points Info -->
        <p><strong>Total Points:</strong> 10 (2 points per question)</p>

        <!-- 5 Questions -->
        <?php for ($i=1; $i<=5; $i++): 
            $q = $questions[$i] ?? ['question_text'=>'','option_a'=>'','option_b'=>'','option_c'=>'','option_d'=>'','correct_option'=>''];
        ?>
        <fieldset>
            <legend>Question <?= $i ?></legend>
            <textarea name="question_<?= $i ?>" placeholder="Enter question text" rows="2" required><?= htmlspecialchars($q['question_text']) ?></textarea>
            <input type="text" name="option_a_<?= $i ?>" placeholder="Option A" required value="<?= htmlspecialchars($q['option_a']) ?>">
            <input type="text" name="option_b_<?= $i ?>" placeholder="Option B" required value="<?= htmlspecialchars($q['option_b']) ?>">
            <input type="text" name="option_c_<?= $i ?>" placeholder="Option C" required value="<?= htmlspecialchars($q['option_c']) ?>">
            <input type="text" name="option_d_<?= $i ?>" placeholder="Option D" required value="<?= htmlspecialchars($q['option_d']) ?>">
            <select name="correct_<?= $i ?>" required>
                <option value="">-- Correct Option --</option>
                <option value="A" <?= $q['correct_option']=='A' ? 'selected':'' ?>>Option A</option>
                <option value="B" <?= $q['correct_option']=='B' ? 'selected':'' ?>>Option B</option>
                <option value="C" <?= $q['correct_option']=='C' ? 'selected':'' ?>>Option C</option>
                <option value="D" <?= $q['correct_option']=='D' ? 'selected':'' ?>>Option D</option>
            </select>
        </fieldset>
        <?php endfor; ?>

        <button type="submit" name="edit_quiz"><i class="fas fa-edit"></i> Save Changes</button>
    </form>
</div>
</body>
</html>
