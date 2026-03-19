<?php
// Use an absolute path or check for the file's existence
$configPath = __DIR__ . '/../../config/config.php';
if (file_exists($configPath)) {
    include $configPath;
} else {
    die("Configuration file not found.");
}

$conn = conn();

$section_id = $_POST['section_id'];
$type_id = $_POST['type_id'];
$type = $_POST['type'];
$scores = $_POST['scores'];

// Determine the correct table based on the type
$table = $type === 'quiz' ? 'quiz_scores' : ($type === 'activity' ? 'activity_scores' : 'exam_scores');

// Iterate through the scores and save them to the database
foreach ($scores as $student_id => $score) {
    // Skip students without a score
    if (trim($score) === '') {
        continue;
    }

    // Insert or update the score for each student
    $query = "INSERT INTO $table (student_id, {$type}_id, score) 
              VALUES (?, ?, ?)
              ON DUPLICATE KEY UPDATE score = VALUES(score)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $student_id, $type_id, $score);
    $stmt->execute();
}

// Redirect or display success message
header("Location: instructor.php");
exit;
?>
