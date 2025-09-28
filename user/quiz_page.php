<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$quiz_id = $_GET['quiz_id'] ?? null;
if (!$quiz_id) die("Quiz not found.");

$quiz = $conn->query("SELECT * FROM quizzes WHERE quiz_id='$quiz_id'")->fetch_assoc();
if (!$quiz) die("Quiz not found.");

$questions = $conn->query("SELECT * FROM quiz_questions WHERE quiz_id='$quiz_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($quiz['quiz_title']) ?> | Quiz</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #76D36B, #B8F1A0);
    margin:0; padding:0;
    display:flex; justify-content:center; align-items:center;
    height:100vh;
}

.container {
    width: 375px;
    height: 667px;
    background: #ffffff;
    border-radius: 40px;
    box-shadow: 0 12px 35px rgba(0,0,0,0.25);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
}

/* Back button */
.back-btn {
    position: absolute;
    top: 20px;
    left: 20px;
    background: none;
    border: none;
    font-size: 1.5rem;
    font-weight: 700;
    color: #2D6A4F;
    cursor: pointer;
}
.back-btn:hover {
    color: #1B4332;
}

h2 { text-align:center; color:#2D6A4F; margin-top:40px; margin-bottom:4px; }
p.quiz-desc { text-align:center; color:#1B4332; margin-bottom:16px; flex-shrink:0; }

.coin-display {
    text-align:right; font-size:1rem; font-weight:600; color:#FFD93D; margin-bottom:12px; flex-shrink:0;
}
.coin-icon { width:22px; height:22px; vertical-align:middle; margin-right:4px; }

.progress-bar {
    width: 100%; background:#d1e7dd; border-radius:12px; overflow:hidden; height:14px; margin-bottom:16px; flex-shrink:0;
}
.progress { width:0%; height:100%; background:#2D6A4F; transition:0.3s; }

.quiz-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.question-card {
    display:none;
    background:#fff; border-radius:14px; padding:16px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}
.question-card.active {
    display:block;
    border-left:6px solid #52B788;
}

.options { margin-top:10px; }
.option { display:block; margin-bottom:8px; }
input[type="radio"] { margin-right:8px; }

.button-container {
    padding-top:10px;
    flex-shrink:0;
}
.next-btn, .submit-btn {
    width:100%; padding:12px 0; background:#52B788; color:white; border:none; border-radius:10px;
    font-weight:600; font-size:1rem; cursor:pointer; transition:0.3s;
}
.next-btn:hover, .submit-btn:hover { background:#2D6A4F; }
.submit-btn { display:none; }
</style>
</head>
<body>

<div class="container">
    <!-- Back button -->
    <button class="back-btn" onclick="window.location.href='mission.php'">&larr;</button>

    <h2><?= htmlspecialchars($quiz['quiz_title']) ?></h2>
    <p class="quiz-desc"><?= htmlspecialchars($quiz['quiz_description']) ?></p>
    <div class="coin-display"><img src="images/icon-gamecoin.png" class="coin-icon"> +<?= $quiz['points'] ?> Points</div>
    <div class="progress-bar"><div class="progress" id="progress"></div></div>

    <form action="submit_quiz.php" method="POST" id="quizForm" style="display:flex; flex-direction:column; height:100%;">
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

        <div class="quiz-content">
            <?php 
            $counter = 0;
            $question_list = [];
            while($q = $questions->fetch_assoc()):
                $question_list[] = $q;
            endwhile;
            foreach($question_list as $index => $q):
                $counter++;
            ?>
            <div class="question-card <?php if($index===0) echo 'active'; ?>" data-index="<?= $index ?>">
                <p><strong>Q<?= $counter ?>.</strong> <?= htmlspecialchars($q['question_text']) ?></p>
                <div class="options">
                    <?php foreach(['a','b','c','d'] as $opt): 
                        $opt_text = $q["option_$opt"];
                    ?>
                    <label class="option">
                        <input type="radio" name="answers[<?= $q['question_id'] ?>]" value="<?= strtoupper($opt) ?>" required>
                        <?= strtoupper($opt) ?>. <?= htmlspecialchars($opt_text) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="button-container">
            <button type="button" class="next-btn" id="nextBtn">Next</button>
            <button type="submit" class="submit-btn" id="submitBtn">Submit Quiz</button>
        </div>
    </form>
</div>

<script>
const questions = document.querySelectorAll('.question-card');
let current = 0;
const total = questions.length;
const progress = document.getElementById('progress');
const nextBtn = document.getElementById('nextBtn');
const submitBtn = document.getElementById('submitBtn');

function showQuestion(index) {
    questions.forEach((q, i) => q.classList.remove('active'));
    questions[index].classList.add('active');
    progress.style.width = `${((index)/total)*100}%`;

    if(index === total-1){
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'block';
    } else {
        nextBtn.style.display = 'block';
        submitBtn.style.display = 'none';
    }
}

nextBtn.addEventListener('click', () => {
    const radios = questions[current].querySelectorAll('input[type="radio"]');
    let answered = Array.from(radios).some(r => r.checked);
    if(!answered) {
        alert('Please select an answer before proceeding.');
        return;
    }
    if(current < total-1){
        current++;
        showQuestion(current);
    }
});

showQuestion(0);
</script>

</body>
</html>
