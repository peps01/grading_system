<?php
// Use an absolute path or check for the file's existence
$configPath = __DIR__ . '/../../config/config.php';
if (file_exists($configPath)) {
    include $configPath;
} else {
    die("Configuration file not found.");
}
if (isset($_POST['subject_id']) && isset($_POST['activity_id'])) {
    $subject_id = $_POST['subject_id'];
    $activity_id = $_POST['activity_id'];
    
    // Query to fetch students assigned to the selected subject
    $query = " 
        SELECT 
            students.id AS student_id, 
            student_data.usn, 
            users.full_name, 
            users.email 
        FROM students 
        JOIN users ON students.user_id = users.id 
        JOIN student_data ON student_data.student_id = students.id 
        LEFT JOIN section_subject ON section_subject.section_id = students.section_id 
        LEFT JOIN subjects ON section_subject.subject_id = subjects.id 
        WHERE users.status = 'active' 
        AND subjects.id = ? 
        ORDER BY students.id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    echo json_encode(['students' => $students]);
}
?>
