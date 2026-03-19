<?php
// Use an absolute path or check for the file's existence
$configPath = __DIR__ . '/../../config/config.php';
if (file_exists($configPath)) {
    include $configPath;
} else {
    die("Configuration file not found.");
}
if (isset($_POST['subject_id'])) {
    $subject_id = $_POST['subject_id'];
    
    // Query to fetch activities associated with the selected subject
    $query = "SELECT id, activity_name FROM activities WHERE subject_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    echo json_encode(['activities' => $activities]);
}
?>
