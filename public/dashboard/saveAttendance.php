<?php
$configPath = __DIR__ . '/../../config/config.php';
if (file_exists($configPath)) {
    include $configPath;
} else {
    die("Configuration file not found.");
}

$conn = conn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = $_POST['subject_id'];
    $attendance_date = $_POST['attendance_date'];
    $class_schedule_id = $_POST['class_schedule_id']; // Get class_schedule_id from POST
    $statuses = $_POST['statuses'] ?? [];
    $allSaved = true; // Track if all records were saved successfully

    // Check for any blank statuses
    $studentsWithBlankStatus = [];
    foreach ($statuses as $student_id => $status) {
        if (empty($status)) {
            $studentsWithBlankStatus[] = $student_id;
        }
    }

    // If there are students with blank statuses, show an alert and prevent saving
    if (!empty($studentsWithBlankStatus)) {
        $studentIds = implode(", ", $studentsWithBlankStatus);
        echo "<script>
                alert('Attendance not saved. Some students have blank statuses. Please complete all statuses.');
                window.history.back();
              </script>";
        exit;
    }

    // Proceed to save attendance if all statuses are filled
    foreach ($statuses as $student_id => $status) {
        // Check if the student already has a record for the given date and subject
        $query = "
            SELECT status FROM attendance 
            WHERE student_id = ? AND subject_id = ? AND date = ? AND class_schedule_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iisi", $student_id, $subject_id, $attendance_date, $class_schedule_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // If a record exists, alert the user and skip saving this attendance
            $existingRecord = $result->fetch_assoc();
            echo "<script>
                    alert('Student with ID $student_id already has a record for this date and subject. Current Status: " . htmlspecialchars($existingRecord['status']) . "');
                    window.location.href='instructor.php';
                  </script>";
            $allSaved = false; // Mark as not all saved
            exit;
        } else {
            // If no record exists, insert or update the attendance status
            $query = "
                INSERT INTO attendance (student_id, subject_id, class_schedule_id, date, status)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iiiss", $student_id, $subject_id, $class_schedule_id, $attendance_date, $status);
            if (!$stmt->execute()) {
                // Handle error
                echo "<script>
                        alert('Error saving attendance for student ID $student_id.');
                        window.location.href='instructor.php';
                      </script>";
                $allSaved = false; // Mark as not all saved
                exit;
            }
        }
    }

    // Only display the success message if there were no issues
    if ($allSaved) {
        echo "<script>
                alert('Attendance saved successfully!');
                window.location.href='instructor.php';
              </script>";
    }
    exit;
}
?>
