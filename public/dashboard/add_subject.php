<?php

$configPath = __DIR__ . '/../../config/config.php';
if (file_exists($configPath)) {
    include $configPath;
} else {
    die("Configuration file not found.");
}

$conn = conn();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject_to_course'])) {
    $course_id = $_POST['course_id'];
    $subject_id = $_POST['subject_id'];

    // Validate input
    if (!empty($course_id) && !empty($subject_id)) {
        // Check if the course and subject pair already exists
        $checkSql = "SELECT COUNT(*) as count FROM course_subject WHERE course_id = ? AND subject_id = ?";
        $checkStmt = $conn->prepare($checkSql);

        if ($checkStmt) {
            $checkStmt->bind_param("ii", $course_id, $subject_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $row = $checkResult->fetch_assoc();

            if ($row['count'] > 0) {
                echo "<script>alert('This subject is already assigned to the selected course.');</script>";
                echo "<script>window.location.href = '../dashboard/admin.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Database error while checking for duplicates: " . $conn->error . "');</script>";
            echo "<script>window.location.href = '../dashboard/admin.php';</script>";
            exit();
        }

        // Insert the course_id and subject_id into the course_subject table
        $sql = "INSERT INTO course_subject (course_id, subject_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ii", $course_id, $subject_id); // 'ii' for two integers
            if ($stmt->execute()) {
                echo "<script>alert('Subject successfully added to the course.');</script>";
                echo "<script>window.location.href = '../dashboard/admin.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
                echo "<script>window.location.href = '../dashboard/admin.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Database error: " . $conn->error . "');</script>";
            echo "<script>window.location.href = '../dashboard/admin.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Both course and subject must be selected.');</script>";
        echo "<script>window.location.href = '../dashboard/admin.php';</script>";
        exit();
    }
}
?>
