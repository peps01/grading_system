<?php
session_start();
include '../config/config.php';

// Add a new course
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = conn(); // Connect to the database
    $courseName = $_POST['course_name']; // Get course name from the request
    $instructorId = $_POST['instructor_id']; // Get instructor ID from the request

    // Insert course into the table
    $query = "INSERT INTO courses (course_name, instructor_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $courseName, $instructorId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Course added successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding course: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit(); // Exit after adding a course to prevent executing the fetch logic
}

// Fetch courses
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conn = conn(); // Connect to the database

    // Check if instructor_id is set in the GET request
    if (isset($_GET['instructor_id'])) {
        $instructorId = $_GET['instructor_id']; // Get instructor ID from the request

        // Fetch all courses for the instructor
        $query = "SELECT id, course_name FROM courses WHERE instructor_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $instructorId);
        $stmt->execute();
        $result = $stmt->get_result();

        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }

        echo json_encode($courses);
    } else {
        echo json_encode(['success' => false, 'message' => 'Instructor ID is not provided.']);
    }

    $stmt->close();
    $conn->close();
}