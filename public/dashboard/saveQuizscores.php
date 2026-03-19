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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $quiz_score = $_POST['quiz_score'];
    $subject_id = $_POST['subject_id'];

    // Fetch the quiz ID and total score associated with the subject
    $query = "SELECT id, total_score FROM quizzes WHERE subject_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $quiz = $result->fetch_assoc();
        $quiz_id = $quiz['id'];
        $total_score = $quiz['total_score'];

        // Check if the score exceeds the total score
        if ($quiz_score > $total_score) {
            echo "<script>
                alert('The entered score exceeds the total score for this quiz. Please enter a valid score.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
        }

        // Check if the score is already added for this student and quiz
        $query = "SELECT id FROM quiz_scores WHERE quiz_id = ? AND student_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $quiz_id, $student_id);
        $stmt->execute();
        $existingScore = $stmt->get_result();

        if ($existingScore->num_rows > 0) {
            echo "<script>
                alert('A score for this student and quiz has already been added. Duplicate entries are not allowed.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
        }

        // Insert the quiz score into the quiz_scores table
        $query = "INSERT INTO quiz_scores (quiz_id, student_id, score) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $quiz_id, $student_id, $quiz_score);

        if ($stmt->execute()) {
            // Success message
            echo "<script>
                alert('Quiz score saved successfully.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
        } else {
            // Error message
            echo "<script>
                alert('Error saving quiz score.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
        }
    } else {
        // No quiz found message
        echo "<script>
            alert('No quiz found for the selected subject.');
            window.location.href = 'instructor.php';
        </script>";
        exit();
    }
}
