<?php
// Include database connection
include '../config/config.php';
$conn = conn();

session_start();

// Check if the access code is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $parent_code = trim($_POST['code']); // Sanitize input

    // Prepare the SQL query to validate the code
    $stmt = $conn->prepare("
        SELECT 
            parent_codes.student_id
        FROM parent_codes
        WHERE parent_codes.code = ? LIMIT 1
    ");
    $stmt->bind_param("s", $parent_code);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Valid parent code
            $data = $result->fetch_assoc();
            $student_id = $data['student_id'];

            // Store session variable
            $_SESSION['student_id'] = $student_id;

            // Redirect to dashboard
            header("Location: ../public/dashboard/parent.php");
            exit;
        } else {
            header("Location: parent.php?error=invalid_code");
            exit;
        }
    } else {
        die("Query execution failed: " . $stmt->error);
    }
} else {
    header("Location: parent.php?error=no_code");
    exit;
}
?>
