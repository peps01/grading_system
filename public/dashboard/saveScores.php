<?php
session_start();

// Include configuration
$configPath = __DIR__ . '/../../config/config.php';
if (file_exists($configPath)) {
    include $configPath;
} else {
    die("Configuration file not found.");
}

$conn = conn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract common parameters
    $type = $_POST['type'] ?? null; // quiz, activity, or exam
    $type_id = $_POST['type_id'] ?? null; // ID of quiz, activity, or exam
    $scores = $_POST['scores'] ?? []; // Array of student scores

    if (!$type || !$type_id || !is_array($scores)) {
        echo "<script>
            alert('Invalid request. Missing parameters.');
            window.location.href = 'instructor.php';
        </script>";
        exit();
    }

    // Determine the correct table and total score field
    $table = '';
    $totalScoreField = 'total_score';
    $nameField = '';
    switch ($type) {
        case 'quiz':
            $table = 'quiz_scores';
            $totalScoreTable = 'quizzes';
            $nameField = 'quiz_id';
            break;
        case 'activity':
            $table = 'activity_scores';
            $totalScoreTable = 'activities';
            $nameField = 'activity_id';
            break;
        case 'exam':
            $table = 'exam_scores';
            $totalScoreTable = 'exams';
            $nameField = 'exam_id';
            break;
        default:
            echo "<script>
                alert('Invalid type specified.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
    }

    // Fetch the total score for the specified type
    $query = "SELECT $totalScoreField FROM $totalScoreTable WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $type_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>
            alert('Invalid $type ID. Record not found.');
            window.location.href = 'instructor.php';
        </script>";
        exit();
    }

    $row = $result->fetch_assoc();
    $total_score = $row[$totalScoreField];

    // Iterate through the scores and validate/save each
    foreach ($scores as $student_id => $score) {
        // Skip blank scores
        if (trim($score) === '') {
            continue;
        }

        $score = floatval($score);

        // Validation: Check if the score exceeds the total score
        if ($score > $total_score) {
            echo "<script>
                alert('The entered score for student ID $student_id exceeds the total allowed score of $total_score. Please enter a valid score.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
        }

        // Check for duplicate scores
        $query = "SELECT id FROM $table WHERE $nameField = ? AND student_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $type_id, $student_id);
        $stmt->execute();
        $existingScore = $stmt->get_result();

        if ($existingScore->num_rows > 0) {
            echo "<script>
                alert('A score for student ID $student_id has already been added. Duplicate entries are not allowed.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
        }

        // Insert the score into the database
        $query = "INSERT INTO $table ($nameField, student_id, score) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $type_id, $student_id, $score);

        if (!$stmt->execute()) {
            echo "<script>
                alert('Error saving score for student ID $student_id.');
                window.location.href = 'instructor.php';
            </script>";
            exit();
        }
    }

    // Success message
    echo "<script>
        alert('Scores saved successfully for $type.');
        window.location.href = 'instructor.php';
    </script>";
    exit();
}
