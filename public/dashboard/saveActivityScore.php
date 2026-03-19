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
    $score = $_POST['score'];
    $activity_id = $_POST['activity_id'];

    // Fetch the activity details (including total score)
    $query = "SELECT id, total_score FROM activities WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $activity_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $activity = $result->fetch_assoc();
        $total_score = $activity['total_score'];

        // Check if the score exceeds the total score
        if ($score > $total_score) {
            echo "<script>
                alert('The entered score exceeds the total score for this activity. Please enter a valid score.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
        }

        // Check if the score is already added for this student and activity
        $query = "SELECT id FROM activity_scores WHERE activity_id = ? AND student_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $activity_id, $student_id);
        $stmt->execute();
        $existingScore = $stmt->get_result();

        if ($existingScore->num_rows > 0) {
            echo "<script>
                alert('A score for this student and activity has already been added. Duplicate entries are not allowed.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
        }

        // Insert the activity score into the activity_scores table
        $query = "INSERT INTO activity_scores (activity_id, student_id, score) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $activity_id, $student_id, $score);

        if ($stmt->execute()) {
            // Success message
            echo "<script>
                alert('Activity score saved successfully.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
        } else {
            // Error message
            echo "<script>
                alert('Error saving activity score.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
        }
    } else {
        // No activity found message
        echo "<script>
            alert('Invalid activity selected.');
            window.location.href = 'instructor.php';
        </script>";
        exit();
    }
}
