<?php
session_start();

$configPath = __DIR__ . '/../../config/config.php';
if (file_exists($configPath)) {
    include $configPath;
} else {
    die("Configuration file not found.");
}

$conn = conn();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role_name'] !== 'Instructor') {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

$instructorId = $_SESSION['user_id']; // Use the logged-in instructor's ID

$query = "SELECT username, email, full_name FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $instructorId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Instructor not found.']);
}
