<?php
session_start();
include 'connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Logged-in user's ID (from users.user_id)
$user_id = $_SESSION['user_id'];

// Get mission_id from URL
$mission_id = isset($_GET['id']) ? $_GET['id'] : ''; // missions.mission_id is VARCHAR(10)

// Fetch mission info
$stmt = $conn->prepare("SELECT * FROM missions WHERE mission_id = ?");
$stmt->bind_param("s", $mission_id);
$stmt->execute();
$mission = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mission) {
    die("Mission not found.");
}

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['camera_image'])) {
    $notes = $_POST['notes'] ?? '';
    $imgData = $_POST['camera_image'];

    if ($imgData) {
        // Convert base64 to binary
        $imgData = str_replace('data:image/png;base64,', '', $imgData);
        $imgData = str_replace(' ', '+', $imgData);
        $imgBinary = base64_decode($imgData);

        // ✅ Check if user already submitted this mission
        $check = $conn->prepare("SELECT id FROM mission_submissions WHERE user_id = ? AND mission_id = ?");
        $check->bind_param("ss", $user_id, $mission_id);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            // Already submitted → redirect with error
            header("Location: mission.php?error=already_submitted");
            exit;
        } else {
            // Insert new submission
            $stmt = $conn->prepare("
                INSERT INTO mission_submissions 
                (user_id, mission_id, notes, submission_image, submitted_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("ssss", $user_id, $mission_id, $notes, $imgBinary);

            if ($stmt->execute()) {
                header("Location: mission.php?success=1");
                exit;
            } else {
                $error = "Database Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submit Mission</title>
<style>
body {
    margin:0;
    padding:20px;
    font-family:'Poppins',sans-serif;
    background:#f0f0f0;
    display:flex;
    justify-content:center;
}
.camera-container {
    position: relative;
    width:350px;
    background:#fff;
    padding:30px;
    display:flex;
    flex-direction:column;
    align-items:left;
}
video, img {
    width:100%;
    border-radius:12px;
    object-fit:cover;
}
textarea {
    width:100%;
    margin-top:10px;
    padding:8px;
    border-radius:8px;
    border:1px solid #ccc;
    resize:none;
    font-size:0.95rem;
}
button {
    margin-top:10px;
    padding:10px;
    width:48%;
    font-weight:600;
    border:none;
    border-radius:10px;
    cursor:pointer;
}
#captureBtn { background:#2D6A4F; color:#fff; }
#retakeBtn { background:#A7DCA5; color:#000; display:none; }
#submitBtn { background:#FFD93D; color:#000; display:none; }
#backBtn {
    position:absolute;
    top:0;
    left:0;
    border:none;
    background:none;
    color:#2D6A4F;
    padding:0;
    font-weight:600;
    cursor:pointer;
}
button:hover { opacity:0.9; }
</style>
</head>
<body>

<div class="camera-container">
    <button type="button" id="backBtn" onclick="window.location.href='mission.php';">← Back</button>

    <video id="cameraVideo" autoplay playsinline></video>
    <img id="previewImg" style="display:none; margin-top:10px;">

    <form id="cameraForm" method="POST" style="width:100%; display:flex; flex-direction:column; align-items:center;">
        <input type="hidden" name="mission_id" value="<?= htmlspecialchars($mission_id) ?>">
        <input type="hidden" name="camera_image" id="cameraImage">
        <textarea name="notes" rows="3" placeholder="Notes (optional)"></textarea>
        <div style="width:100%; display:flex; justify-content:space-between;">
            <button type="button" id="captureBtn">Capture</button>
            <button type="button" id="retakeBtn">Retake</button>
            <button type="submit" id="submitBtn">Submit</button>
        </div>
    </form>
</div>

<script>
let video = document.getElementById('cameraVideo');
let previewImg = document.getElementById('previewImg');
let cameraImage = document.getElementById('cameraImage');
let captureBtn = document.getElementById('captureBtn');
let retakeBtn = document.getElementById('retakeBtn');
let submitBtn = document.getElementById('submitBtn');

// Open back camera on page load
navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
    .then(s => { video.srcObject = s; })
    .catch(err => alert('Camera access denied: ' + err));

// Capture photo
captureBtn.addEventListener('click', () => {
    let canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    let dataURL = canvas.toDataURL('image/png');
    cameraImage.value = dataURL;
    previewImg.src = dataURL;
    previewImg.style.display = 'block';
    video.style.display = 'none';
    captureBtn.style.display = 'none';
    retakeBtn.style.display = 'inline-block';
    submitBtn.style.display = 'inline-block';
});

// Retake photo
retakeBtn.addEventListener('click', () => {
    previewImg.style.display = 'none';
    video.style.display = 'block';
    captureBtn.style.display = 'inline-block';
    retakeBtn.style.display = 'none';
    submitBtn.style.display = 'none';
});
</script>

</body>
</html>
