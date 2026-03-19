<?php
// Use an absolute path or check for the file's existence
$configPath = __DIR__ . '/../../config/config.php';
if (file_exists($configPath)) {
    include $configPath;
} else {
    die("Configuration file not found.");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_name'], $_POST['instructor_id'])) {
    $courseName = mysqli_real_escape_string(conn(), $_POST['course_name']);
    $instructorId = (int)$_POST['instructor_id'];

    // Insert the new course into the courses table
    $query = "INSERT INTO courses (course_name, instructor_id) VALUES ('$courseName', '$instructorId')";

    if (mysqli_query(conn(), $query)) {
        // Redirect back to the manage courses page after adding a course
        header('Location: ../public/dashboard/instructor.php');
        exit;
    } else {
        echo "Error: " . mysqli_error(conn());
    }
}

