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
$fieldsToUpdate = [];
$params = [];

// Process the update request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['username'])) {
        $fieldsToUpdate[] = "username = ?";
        $params[] = $_POST['username'];
    }
    if (!empty($_POST['email'])) {
        $fieldsToUpdate[] = "email = ?";
        $params[] = $_POST['email'];
    }
    if (!empty($_POST['password'])) {
        $fieldsToUpdate[] = "password = ?";
        $params[] = password_hash($_POST['password'], PASSWORD_BCRYPT);
    }

    if (!empty($fieldsToUpdate)) {
        $query = "UPDATE users SET " . implode(', ', $fieldsToUpdate) . " WHERE id = ?";
        $params[] = $instructorId;

        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('s', count($params) - 1) . 'i', ...$params);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Profile updated successfully.']);
        } else {
            echo json_encode(['error' => 'Error updating profile.']);
        }
    } else {
        echo json_encode(['error' => 'No fields to update.']);
    }
}
