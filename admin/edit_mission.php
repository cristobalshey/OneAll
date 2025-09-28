<?php
session_start();
include 'connect.php';
require __DIR__ . '/phpqrcode/qrlib.php'; // ✅ QR library

// Redirect if no mission ID is provided
if (!isset($_GET['id'])) {
    header("Location: projects.php");
    exit;
}

$mission_id = $_GET['id'];

// Fetch mission
$stmt = $conn->prepare("SELECT * FROM missions WHERE mission_id = ?");
$stmt->bind_param("s", $mission_id);
$stmt->execute();
$result = $stmt->get_result();
$mission = $result->fetch_assoc();

if (!$mission) {
    header("Location: projects.php");
    exit;
}

// Handle update
if (isset($_POST['edit_mission'])) {
    $name        = trim($_POST['name']);
    $desc        = trim($_POST['desc']);
    $launch_date = $_POST['launch_date'];
    $end_date    = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;

    // ✅ Points are integer only
    $points = (int) $_POST['points'];

    // ✅ Keep waste type & weight
    $wasteType = $_POST['wasteType'];
    $weight    = (float) $_POST['weight'];

    // ✅ Auto-determine status
    $today = date("Y-m-d");
    if ($today < $launch_date) {
        $status = "upcoming";
    } elseif ($end_date && $today > $end_date) {
        $status = "expired";
    } else {
        $status = "active";
    }

    // ✅ Keep same qr_id (do not regenerate)
    $qr_id = $mission['qr_id'];

    // Update DB
    $update_stmt = $conn->prepare("
        UPDATE missions 
        SET mission_name=?, mission_description=?, points_allocated=?, status=?, launch_date=?, end_date=?, qr_id=?, waste_type=?, weight=? 
        WHERE mission_id=?
    ");
    $update_stmt->bind_param(
        "ssissssssd",
        $name, 
        $desc, 
        $points, 
        $status, 
        $launch_date, 
        $end_date, 
        $qr_id, 
        $wasteType, 
        $weight, 
        $mission_id
    );

    if ($update_stmt->execute()) {
        header("Location: projects.php?updated=1");
        exit;
    } else {
        echo "Error updating: " . $update_stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Mission | EcoTrack Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    body { margin:0; font-family:'Segoe UI',Arial,sans-serif; background:#f4f8f6; color:#222; }
    .form-card{
      max-width:600px; margin:80px auto; padding:32px; background:#fff;
      border-radius:12px; box-shadow:0 2px 8px rgba(40,167,69,0.04);
      border:1px solid #e4f5e9;
    }
    .form-card h3{ margin-bottom:24px; font-size:1.3em; color:#222; font-weight:600; }
    .form-card input, .form-card textarea, .form-card select {
      width: 100%; padding: 10px 12px; margin-bottom: 18px;
      border: 1px solid #ccc; border-radius: 8px; font-size: 1em;
      box-sizing: border-box; height: 42px;
    }
    .form-card textarea { height: auto; }
    .form-card button{
      background:#28a745; color:#fff; border:none; border-radius:8px;
      padding:10px 18px; font-size:1em; font-weight:600;
      cursor:pointer; transition:background 0.2s;
    }
    .form-card button:hover{ background:#218838; }
    a.back-btn{ display:inline-block; margin-bottom:12px; color:#28a745; text-decoration:none; font-weight:600; }
    a.back-btn:hover{opacity:0.7;}
    .qr-preview { margin-top: 15px; text-align: center; }
    .qr-preview img {
      width: 120px; cursor: pointer; border: 1px solid #ddd;
      border-radius: 6px; transition: transform 0.2s;
    }
    .qr-preview img:hover { transform: scale(1.1); }
  </style>
</head>
<body>
  <div class="form-card">
    <a href="projects.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Projects</a>
    <h3>Edit Mission (<?= htmlspecialchars($mission_id) ?>)</h3>
    <form method="POST">
      <input type="text" name="name" placeholder="Mission Name"
             value="<?= htmlspecialchars($mission['mission_name']) ?>" required>

      <textarea name="desc" placeholder="Description" rows="4" required><?= htmlspecialchars($mission['mission_description']) ?></textarea>

      <!-- Waste Material Type -->
      <select id="wasteType" name="wasteType" required>
        <option value="">-- Select Waste Material --</option>
        <?php
          $options = [
            "White (Used) Paper"        => 80,
            "Corrugated Cardboard"      => 20,
            "Newspaper"                 => 40,
            "Mixed Paper"               => 10,
            "PET Bottles (clean)"       => 160,
            "PET Bottles (unclean)"     => 120,
            "Aluminum Cans"             => 50,
            "HDPE Plastic"              => 10,
            "LDPE Plastic"              => 50,
            "Engineering Plastics"      => 100,
            "Copper Wire"               => 1500,
            "Steel"                     => 900,
            "Stainless Steel"           => 600,
            "Tin Cans"                  => 30,
            "Glass Cullets"             => 10,
            "Scrap Metal"               => 140,
            "Hard Plastic"              => 150,
            "Plastic Bottles (informal)"=> 100,
            "Cardboard (informal)"      => 40,
            "Plastic Sachet"            => 20
          ];
          foreach ($options as $label => $rate) {
              $selected = ($mission['waste_type'] == $label) ? "selected" : "";
              echo "<option value=\"$label\" $selected>$label - {$rate} pts/kg</option>";
          }
        ?>
      </select>

      <!-- Weight in KG -->
      <input type="number" id="weight" name="weight" placeholder="Weight (kg)" 
             min="0" step="0.1" value="<?= htmlspecialchars($mission['weight']) ?>">

      <!-- Auto-calculated Points -->
      <input type="number" id="points" name="points" placeholder="Points"
             value="<?= htmlspecialchars($mission['points_allocated']) ?>" readonly required>

      <input type="date" name="launch_date" value="<?= htmlspecialchars($mission['launch_date']) ?>" required>
      <input type="date" name="end_date" value="<?= htmlspecialchars($mission['end_date']) ?>" placeholder="End Date">

      <button type="submit" name="edit_mission"><i class="fas fa-edit"></i> Save Changes</button>
    </form>

    <!-- ✅ QR Preview -->
    <?php if (!empty($mission['qr_id'])) : ?>
      <div class="qr-preview">
        <a href="qrcodes/<?= htmlspecialchars($mission['qr_id']) ?>.png" target="_blank">
          <img src="qrcodes/<?= htmlspecialchars($mission['qr_id']) ?>.png" alt="Mission QR">
        </a>
        <p><small>QR ID: <?= htmlspecialchars($mission['qr_id']) ?></small></p>
      </div>
    <?php endif; ?>
  </div>

  <script>
    const wasteType = document.getElementById("wasteType");
    const weight = document.getElementById("weight");
    const points = document.getElementById("points");

    // match JS mapping to PHP rates
    const rates = {
      "White (Used) Paper": 80,
      "Corrugated Cardboard": 20,
      "Newspaper": 40,
      "Mixed Paper": 10,
      "PET Bottles (clean)": 160,
      "PET Bottles (unclean)": 120,
      "Aluminum Cans": 50,
      "HDPE Plastic": 10,
      "LDPE Plastic": 50,
      "Engineering Plastics": 100,
      "Copper Wire": 1500,
      "Steel": 900,
      "Stainless Steel": 600,
      "Tin Cans": 30,
      "Glass Cullets": 10,
      "Scrap Metal": 140,
      "Hard Plastic": 150,
      "Plastic Bottles (informal)": 100,
      "Cardboard (informal)": 40,
      "Plastic Sachet": 20
    };

    function calculatePoints() {
      const type = wasteType.value;
      const rate = rates[type] || 0;
      const kg = parseFloat(weight.value) || 0;
      const totalPoints = Math.floor(rate * kg);

      if (kg > 0 && rate > 0) {
        points.value = totalPoints;
      } else {
        points.value = "";
      }
    }

    wasteType.addEventListener("change", calculatePoints);
    weight.addEventListener("input", calculatePoints);
  </script>
</body>
</html>
