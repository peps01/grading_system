<?php
session_start();
// Use an absolute path or check for the file's existence
$configPath = __DIR__ . '/../../config/config.php';
if (file_exists($configPath)) {
    include $configPath;
} else {
    die("Configuration file not found.");
}
// Subject assignment and removal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $conn = conn();

    // Get instructor ID from session
    $instructorId = intval($_SESSION['user_id']);

    // Check if this is the request to assign a subject to a section
    if (isset($_POST['assignSubjectToSectionBtn'])) {
        $sectionId = intval($_POST['section_id']);
        $subjectId = intval($_POST['subject_id']);

        // Check if the subject is valid and assigned to this instructor
        $query = "
            SELECT id 
            FROM assigned_subjects 
            WHERE instructor_id = ? AND subject_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $instructorId, $subjectId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Subject is not assigned to this instructor
            header("Location: ../public/dashboard/instructor.php?error=You can only assign subjects assigned to you.");
            exit();
        }

        // Assign the subject to the section
        $query = "
            INSERT INTO section_subject (section_id, subject_id) 
            VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $sectionId, $subjectId);

        if ($stmt->execute()) {
            // Success
            header("Location: ../public/dashboard/instructor.php?success=Subject assigned to section successfully.");
            exit();
        } else {
            // Error
            echo "Error assigning subject: " . $conn->error;
        }
    }

    $stmt->close();
    $conn->close();
}
