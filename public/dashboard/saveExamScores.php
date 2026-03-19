<?php
session_start();
// Use an absolute path or check for the file's existence
$configPath = __DIR__ . '/../../config/config.php';
if (file_exists($configPath)) {
    include $configPath;
} else {
    die("Configuration file not found.");
}

$conn = conn();

// Check if the necessary POST data is provided
if (isset($_POST['student_id'], $_POST['exam_score'], $_POST['exam_id'])) {
    $student_id = intval($_POST['student_id']);
    $exam_score = floatval($_POST['exam_score']);
    $exam_id = intval($_POST['exam_id']);

    // Validation: Check if the provided exam score is valid
    if ($exam_score < 0) {
        echo "Error: Invalid score. Score cannot be negative.";
        exit;
    }

    // Fetch the total score for the selected exam
    $query = "SELECT total_score FROM exams WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_score = $row['total_score'];

        // Validate the exam score does not exceed the total score
        if ($exam_score > $total_score) {
            echo "Error: Score cannot exceed the total score of $total_score.";
            exit;
        }

        // Check if a score for this student and exam already exists
        $query = "SELECT * FROM exam_scores WHERE student_id = ? AND exam_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $student_id, $exam_id);
        $stmt->execute();
        $existingResult = $stmt->get_result();

        if ($existingResult->num_rows > 0) {
            echo "Error: A score for this student and exam already exists.";
            exit;
        }

        // Insert the new score into the database
        $query = "INSERT INTO exam_scores (student_id, exam_id, exam_score) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iid", $student_id, $exam_id, $exam_score);

        if ($stmt->execute()) {
            echo "Success: Exam score saved successfully.";
        } else {
            echo "Error: Failed to save the exam score.";
        }
    } else {
        echo "Error: Invalid exam ID. Exam not found.";
    }
} else {
    echo "Error: Invalid request. Missing required parameters.";
}

exit;
?>
