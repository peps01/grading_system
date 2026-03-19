<?php
$configPath = __DIR__ . '/../../config/config.php';
if (file_exists($configPath)) {
    include $configPath;
} else {
    die("Configuration file not found.");
}

$conn = conn();
if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);

    // Query to fetch subjects for the selected course
    $query = "
        SELECT s.subject_name 
        FROM course_subject cs
        INNER JOIN subjects s ON cs.subject_id = s.id
        WHERE cs.course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Output the subjects as a list
    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['subject_name']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No subjects assigned to this course yet.</p>";
    }
}
?>
