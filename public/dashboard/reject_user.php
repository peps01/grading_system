<?php
// Include database connection
include_once '../../config/config.php';
$conn = conn();

// Get the raw POST data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Check if the necessary parameters are passed
if (isset($data['id']) && isset($data['status'])) {
    $userId = $data['id'];
    $status = $data['status'];

    // Update the user's status
    $query = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $userId);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Status updated successfully"]);
    } else {
        echo json_encode(["message" => "Error updating status"]);
    }

    $stmt->close();
} else {
    echo json_encode(["message" => "Invalid input"]);
}

// Close the database connection
$conn->close();
?>
