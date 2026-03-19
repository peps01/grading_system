<?php
include '../config/config.php';

$conn = conn(); // Connect to the database
$instructorId = $_GET['user_id']; // Get instructor ID from the request

// Fetch all courses for the instructor
$query = "SELECT id, course_name FROM courses WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $instructorId);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

echo json_encode($courses);

$stmt->close();
$conn->close();
