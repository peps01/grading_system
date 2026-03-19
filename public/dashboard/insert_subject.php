<?php
// insert_subject.php
include_once '../../config/config.php';
$conn = conn();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = $_POST['course_id'];
    $subjectId = $_POST['subject_id']; // Subject to be added to the course

    // Check if the instructor is allowed to assign this subject (based on their section)
    $instructorId = 'instructor_id'; // Replace with dynamic instructor ID (e.g., from session)
    
    // Ensure that the subject can only be added to a course assigned to the instructor
    $checkQuery = "SELECT * FROM courses WHERE id = $courseId AND instructor_id = $instructorId";
    $checkResult = mysqli_query(conn(), $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        // Insert the subject into the course_subject table
        $insertQuery = "INSERT INTO course_subject (course_id, subject_id) VALUES ($courseId, $subjectId)";
        if (mysqli_query(conn(), $insertQuery)) {
            echo "Subject added successfully!";
        } else {
            echo "Error: Could not add subject.";
        }
    } else {
        echo "You do not have permission to add subjects to this course.";
    }
}
?>
