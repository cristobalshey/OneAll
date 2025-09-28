<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

if (!isset($_POST['id']) || !isset($_POST['status'])) {
    echo json_encode(["success" => false, "message" => "Missing parameters."]);
    exit;
}

$id = intval($_POST['id']);
$status = strtolower(trim($_POST['status']));
$validStatuses = ["approved", "pending", "banned"];

if (!in_array($status, $validStatuses)) {
    echo json_encode(["success" => false, "message" => "Invalid status value."]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "new_status" => $status]);
} else {
    echo json_encode(["success" => false, "message" => "No rows updated. Check ID."]);
}

$stmt->close();
$conn->close();
