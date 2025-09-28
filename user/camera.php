<?php
session_start();
$mission_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$quiz_id = isset($_GET['quiz']) ? intval($_GET['quiz']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Camera</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

  <style>
    body {
      margin: 0;
      background: #E9F7EF;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-family: 'Poppins', sans-serif;
      text-align: center;
    }
    h2 {
      color: #2D6A4F;
      margin-bottom: 12px;
    }
    video, canvas {
      width: 90%;
      max-width: 350px;
      border-radius: 12px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.25);
    }
    .controls {
      margin-top: 12px;
      display: flex;
      gap: 10px;
      justify-content: center;
    }
    button {
      padding: 10px 14px;
      border: none;
      border-radius: 8px;
      background: #2D6A4F;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    button:hover {
      background: #1B4332;
    }
    #preview {
      display: none;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<h2><i class="fa-solid fa-camera"></i> Take a Picture</h2>

<video id="cameraFeed" autoplay></video>
<canvas id="preview"></canvas>

<div class="controls">
  <button onclick="capturePhoto()"><i class="fa-solid fa-camera"></i> Take Photo</button>
  <button onclick="submitPhoto()"><i class="fa-solid fa-check"></i> Submit</button>
  <button onclick="window.location.href='mission.php'"><i class="fa-solid fa-arrow-left"></i> Back</button>
</div>

<script>
let cameraStream = null;
const video = document.getElementById("cameraFeed");
const canvas = document.getElementById("preview");
const ctx = canvas.getContext("2d");

async function initCamera() {
  try {
    cameraStream = await navigator.mediaDevices.getUserMedia({ video: true });
    video.srcObject = cameraStream;
  } catch (err) {
    alert("Camera access denied or not supported.");
  }
}

function capturePhoto() {
  canvas.style.display = "block";
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  ctx.drawImage(video, 0, 0);
}

function submitPhoto() {
  const imageData = canvas.toDataURL("image/png");
  fetch("save_photo.php", {
    method: "POST",
    body: JSON.stringify({
      image: imageData,
      mission_id: <?= $mission_id ?? 'null' ?>,
      quiz_id: <?= $quiz_id ?? 'null' ?>
    }),
    headers: { "Content-Type": "application/json" }
  })
  .then(res => res.text())
  .then(data => {
    alert(data);
    window.location.href = "mission.php";
  });
}

initCamera();
</script>

</body>
</html>
