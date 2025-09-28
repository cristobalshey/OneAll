<?php
session_start();
include 'connect.php'; // DB connection
require __DIR__ . '/phpqrcode/qrlib.php'; // ✅ path to QR library

$qrPathForHTML = ""; // prevent doubling

if (isset($_POST['add_mission'])) {
    $name        = trim($_POST['name']);
    $desc        = trim($_POST['desc']);
    $waste_type  = $_POST['wasteType'];
    $weight      = (float) $_POST['weight'];
    $launch_date = $_POST['launch_date']; 
    $end_date    = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;

    // ✅ Extract points as integer
    $points = (int) $_POST['points'];

    // ✅ Determine status automatically
    $today = date("Y-m-d");
    if ($today < $launch_date) {
        $status = "upcoming";
    } elseif ($end_date && $today > $end_date) {
        $status = "expired";
    } else {
        $status = "active";
    }

    // ✅ Generate unique mission_id MEC####
    do {
        $randomNum = random_int(1000, 9999);
        $newId = "MEC" . $randomNum;

        $check = $conn->prepare("SELECT mission_id FROM missions WHERE mission_id = ?");
        $check->bind_param("s", $newId);
        $check->execute();
        $res = $check->get_result();
    } while ($res->num_rows > 0);

    // ✅ Generate unique QR ID
    do {
        $qr_id = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        $check = $conn->prepare("SELECT qr_id FROM missions WHERE qr_id = ?");
        $check->bind_param("s", $qr_id);
        $check->execute();
        $res = $check->get_result();
    } while ($res->num_rows > 0);

    // ✅ Generate QR Code image file
    $qrDir = __DIR__ . "/qrcodes/";
    if (!file_exists($qrDir)) {
        mkdir($qrDir, 0777, true);
    }
    $qrFile = $qrDir . $qr_id . ".png";
    QRcode::png($qr_id, $qrFile, QR_ECLEVEL_L, 5);

    // ✅ Save relative path for HTML
    $qrPathForHTML = "qrcodes/" . $qr_id . ".png";

    // ✅ Insert into DB (now includes waste_type and weight)
    $stmt = $conn->prepare("
        INSERT INTO missions 
        (mission_id, qr_id, mission_name, mission_description, waste_type, weight, points_allocated, status, launch_date, end_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssddsss", 
        $newId, 
        $qr_id, 
        $name, 
        $desc, 
        $waste_type, 
        $weight, 
        $points, 
        $status, 
        $launch_date, 
        $end_date
    );

    if ($stmt->execute()) {
        // redirect to avoid duplicate form resubmission
        header("Location: projects.php?success=1&qr=" . urlencode($qrPathForHTML));
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

// ✅ If redirected back with qr preview
if (isset($_GET['qr'])) {
    $qrPathForHTML = $_GET['qr'];
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
      margin:0; font-family:'Segoe UI',Arial,sans-serif; background:#f4f8f6; color:#222;
    }
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
    a.back-btn{
      display:inline-block; margin-bottom:12px; color:#28a745;
      text-decoration:none; font-weight:600;
    }
    a.back-btn:hover{opacity:0.7;}
    .qr-preview {
      margin-top: 15px;
      text-align: center;
    }
    .qr-preview img {
      width: 120px;
      cursor: pointer;
      border: 1px solid #ddd;
      border-radius: 6px;
      transition: transform 0.2s;
    }
    .qr-preview img:hover {
      transform: scale(1.1);
    }
  </style>
</head>
<body>
  <div class="form-card">
    <a href="projects.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Projects</a>
    <h3>Create Mission</h3>
    <form method="POST">
      <input type="text" name="name" placeholder="Mission Name" required>
      <textarea name="desc" placeholder="Description" rows="4" required></textarea>

      <!-- Waste Material Type -->
      <select id="wasteType" name="wasteType" required>
        <option value="">-- Select Waste Material --</option>
        <option value="White (Used) Paper">White (Used) Paper - 80 pts/kg</option>
        <option value="Corrugated Cardboard">Corrugated Cardboard - 20 pts/kg</option>
        <option value="Newspaper">Newspaper - 40 pts/kg</option>
        <option value="Mixed Paper">Mixed Paper - 10 pts/kg</option>
        <option value="PET Bottles (clean)">PET Bottles (clean) - 160 pts/kg</option>
        <option value="PET Bottles (unclean)">PET Bottles (unclean) - 120 pts/kg</option>
        <option value="Aluminum Cans">Aluminum Cans - 50 pts/kg</option>
        <option value="HDPE Plastic">HDPE (Sibakin) Plastic - 10 pts/kg</option>
        <option value="LDPE Plastic">LDPE Plastic (lids) - 50 pts/kg</option>
        <option value="Engineering Plastics">Engineering Plastics - 100 pts/kg</option>
        <option value="Copper Wire">Copper Wire - 1500 pts/kg</option>
        <option value="Steel">Steel (Iron alloys) - 900 pts/kg</option>
        <option value="Stainless Steel">Stainless Steel - 600 pts/kg</option>
        <option value="Tin Cans">Tin Cans - 30 pts/kg</option>
        <option value="Glass Cullets">Glass Cullets - 10 pts/kg</option>
        <option value="Scrap Metal">Scrap Metal - 140 pts/kg</option>
        <option value="Hard Plastic">Hard Plastic - 150 pts/kg</option>
        <option value="Plastic Bottles (informal)">Plastic Bottles (informal) - 100 pts/kg</option>
        <option value="Cardboard (informal)">Cardboard (informal) - 40 pts/kg</option>
        <option value="Plastic Sachet">Plastic Sachet - 20 pts/kg</option>
      </select>

      <!-- Weight in KG -->
      <input type="number" id="weight" name="weight" placeholder="Weight (kg)" min="0" step="0.1" required>

      <!-- Auto-calculated Points -->
      <input type="number" id="points" name="points" placeholder="Points" readonly required>

      <input type="date" name="launch_date" required>
      <input type="date" name="end_date" placeholder="End Date">
      <button type="submit" name="add_mission"><i class="fas fa-plus"></i> Add Mission</button>
    </form>

    <!-- ✅ QR Preview -->
    <?php if (!empty($qrPathForHTML)) : ?>
      <div class="qr-preview">
        <a href="<?php echo htmlspecialchars($qrPathForHTML); ?>" target="_blank">
          <img src="<?php echo htmlspecialchars($qrPathForHTML); ?>" alt="Mission QR">
        </a>
      </div>
    <?php endif; ?>
  </div>

  <script>
    const wasteType = document.getElementById("wasteType");
    const weight = document.getElementById("weight");
    const points = document.getElementById("points");

    // mapping waste to points/kg
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
