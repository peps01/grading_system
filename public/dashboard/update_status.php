<?php
include '../../config/config.php';
$conn = conn();

// Read the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Get the user ID and status from the JSON data
$userId = $data['id'];
$status = $data['status'];
$response = ['success' => false, 'message' => 'Something went wrong'];

// Check the status and update accordingly
if ($status === 'active') {
    // Prepare the query to update the user status
    $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'User approved successfully'];
    }
    $stmt->close();
} elseif ($status === 'rejected') {
    // If rejected, delete the user from the database
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'User rejected and account deleted successfully'];
    } else {
        $response['message'] = 'Failed to delete account';
    }
    $stmt->close();
}

echo json_encode($response);
