<?php
session_start();
include 'connect.php'; // your DB connection (assumed correct path)

/**
 * Recursively search for qrlib.php under base directory.
 * Returns full path to qrlib.php or false if not found.
 */
function find_qrlib($baseDir) {
    try {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($it as $file) {
            if ($file->isFile() && strtolower($file->getFilename()) === 'qrlib.php') {
                return $file->getPathname();
            }
        }
    } catch (UnexpectedValueException $ex) {
        // directory unreadable
        return false;
    }
    return false;
}

// get mission_id from query string and sanitize
$mission_id = isset($_GET['mission_id']) ? trim($_GET['mission_id']) : null;
if (!$mission_id) {
    http_response_code(400);
    echo "Mission ID required.";
    exit;
}

// fetch mission row
$stmt = $conn->prepare("SELECT * FROM missions WHERE mission_id = ?");
if (!$stmt) {
    echo "Database error: " . $conn->error;
    exit;
}
$stmt->bind_param("s", $mission_id);
$stmt->execute();
$mission = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mission) {
    http_response_code(404);
    echo "Mission not found.";
    exit;
}

// try known quick paths first, then recursive search as fallback
$tryPaths = [
    __DIR__ . '/phpqrcode/qrlib.php',
    __DIR__ . '/phpqrcode-2010100721_1.1.4/qrlib.php',
    __DIR__ . '/phpqrcode-2010100721_1.1.4/phpqrcode/qrlib.php',
    __DIR__ . '/phpqrcode/qrcode/qrlib.php',
];

$qrlibPath = false;
foreach ($tryPaths as $p) {
    if (file_exists($p)) {
        $qrlibPath = $p;
        break;
    }
}
if (!$qrlibPath) {
    // recursive search (may take a short moment)
    $qrlibPath = find_qrlib(__DIR__);
}

if (!$qrlibPath) {
    // helpful error telling where you can put the library
    http_response_code(500);
    echo "<h3>QR library not found</h3>";
    echo "<p>The file <code>qrlib.php</code> wasn't found. Put the <code>phpqrcode</code> folder inside your project (e.g. <code>C:\\xampp\\htdocs\\try\\phpqrcode\\qrlib.php</code>), or update paths.</p>";
    exit;
}

// include the qrlib
require_once $qrlibPath;

// choose the QR content. Prefer stored qr_id, fallback to mission_id
$qrText = !empty($mission['qr_id']) ? $mission['qr_id'] : $mission['mission_id'];

// generate PNG into memory and base64 encode it for embedding
ob_start();
QRcode::png($qrText, null, QR_ECLEVEL_L, 5, 2); // (text, outfile=null -> output directly, ec level, size, margin)
$imageData = ob_get_clean();

if ($imageData === false || $imageData === '') {
    http_response_code(500);
    echo "Failed to generate QR image.";
    exit;
}

$qrBase64 = base64_encode($imageData);
$downloadName = htmlspecialchars($qrText) . ".png";
$mission_name_display = htmlspecialchars($mission['mission_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Mission QR</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #76D36B, #B8F1A0);
    height:100vh;
    margin:0;
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .card {
    width: 420px;
    background: #fff;
    border-radius: 16px;
    padding: 28px;
    text-align:center;
    box-shadow: 0 12px 30px rgba(0,0,0,0.12);
  }
  h2 { color:#2D6A4F; margin:0 0 8px 0; }
  p.sub { color:#333; margin:8px 0 16px 0; }
  .qr {
    display:inline-block;
    border:1px solid #e6e6e6;
    padding:8px;
    border-radius:8px;
    background:#fff;
  }
  .actions { margin-top:18px; display:flex; gap:12px; justify-content:center; }
  .btn {
    padding:10px 18px;
    border-radius:8px;
    border:none;
    font-weight:600;
    cursor:pointer;
  }
  .btn-green { background:#52B788; color:#fff; }
  .btn-outline { background:transparent; color:#2D6A4F; border:2px solid #CFEFD6; }
  a.download { text-decoration:none; display:inline-block; }
</style>
</head>
<body>
  <div class="card">
    <h2>Mission QR</h2>
    <p class="sub"><?= $mission_name_display ?></p>

    <div class="qr">
      <!-- embedded QR -->
      <a href="data:image/png;base64,<?= $qrBase64 ?>" target="_blank" download="<?= $downloadName ?>">
        <img src="data:image/png;base64,<?= $qrBase64 ?>" alt="QR Code" style="width:220px;height:220px;display:block;">
      </a>
    </div>

    <div style="margin-top:12px;color:#666;font-size:0.95rem;">
      QR ID: <strong><?= htmlspecialchars($qrText) ?></strong>
    </div>

    <div class="actions">
      <a class="download" href="data:image/png;base64,<?= $qrBase64 ?>" download="<?= $downloadName ?>">
        <button class="btn btn-outline">Download</button>
      </a>
      <form action="mission.php" method="get" style="display:inline;">
        <button class="btn btn-green" type="submit">⬅ Back to Missions</button>
      </form>
    </div>
  </div>
</body>
</html>
