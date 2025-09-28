<?php
session_start();
include 'connect.php'; // your DB connection

if (isset($_POST['add_mission'])) {
    $name        = trim($_POST['name']);
    $desc        = trim($_POST['desc']);
    $points      = (int)$_POST['points'];
    $status      = $_POST['status'];
    $launch_date = $_POST['launch_date']; // format YYYY-MM-DD
    $end_date    = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;

    // ✅ Generate random mission_id in format MEC1234
    do {
        $randomNum = random_int(1000, 9999);
        $newId = "MEC" . $randomNum;

        $check = $conn->prepare("SELECT mission_id FROM missions WHERE mission_id = ?");
        $check->bind_param("s", $newId);
        $check->execute();
        $res = $check->get_result();
    } while ($res->num_rows > 0);

    // ✅ Insert mission with custom mission_id
    $stmt = $conn->prepare("
        INSERT INTO missions 
        (mission_id, mission_name, mission_description, points_allocated, status, launch_date, end_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssisss", $newId, $name, $desc, $points, $status, $launch_date, $end_date);

    if ($stmt->execute()) {
        header("Location: projects.php?success=1");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Mission | EcoTrack Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    body {
      margin:0; 
      font-family:'Segoe UI',Arial,sans-serif; 
      background:#f4f8f6; 
      color:#222;
    }
    .form-card{
      max-width:600px;
      margin:80px auto;
      padding:32px;
      background:#fff;
      border-radius:12px;
      box-shadow:0 2px 8px rgba(40,167,69,0.04);
      border:1px solid #e4f5e9;
    }
    .form-card h3{
      margin-bottom:24px;
      font-size:1.3em;
      color:#222;
      font-weight:600;
    }
    .form-card input, 
    .form-card textarea, 
    .form-card select {
      width: 100%;
      padding: 10px 12px;
      margin-bottom: 18px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1em;
      box-sizing: border-box;
      height: 42px;
    }
    .form-card textarea {
      height: auto;
    }
    .form-card button{
      background:#28a745;
      color:#fff;
      border:none;
      border-radius:8px;
      padding:10px 18px;
      font-size:1em;
      font-weight:600;
      cursor:pointer;
      transition:background 0.2s;
    }
    .form-card button:hover{
      background:#218838;
    }
    a.back-btn{
      display:inline-block;
      margin-bottom:12px;
      color:#28a745;
      text-decoration:none;
      font-weight:600;
    }
    a.back-btn:hover{opacity:0.7;}
  </style>
</head>
<body>
  <div class="form-card">
    <a href="projects.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Projects</a>
    <h3>Create Mission</h3>
    <form method="POST">
      <input type="text" name="name" placeholder="Mission Name" 
             value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>

      <textarea name="desc" placeholder="Description" rows="4" required><?= isset($_POST['desc']) ? htmlspecialchars($_POST['desc']) : '' ?></textarea>

      <input type="number" name="points" placeholder="Points" min="0" 
             value="<?= isset($_POST['points']) ? htmlspecialchars($_POST['points']) : '' ?>" required>

      <input type="date" name="launch_date" 
             value="<?= isset($_POST['launch_date']) ? htmlspecialchars($_POST['launch_date']) : '' ?>" required>

      <input type="date" name="end_date" 
             value="<?= isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : '' ?>" placeholder="End Date">

      <select name="status" required>
        <option value="active" <?= (isset($_POST['status']) && $_POST['status']=='active') ? 'selected' : '' ?>>Active</option>
        <option value="upcoming" <?= (isset($_POST['status']) && $_POST['status']=='upcoming') ? 'selected' : '' ?>>Upcoming</option>
      </select>

      <button type="submit" name="add_mission"><i class="fas fa-plus"></i> Add Mission</button>
    </form>
  </div>
</body>
</html>
