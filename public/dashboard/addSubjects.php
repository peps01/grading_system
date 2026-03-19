<?php
session_start();
$configPath = __DIR__ . '/../../config/config.php';
include $configPath;

$conn = conn();

// Add new subject
if (isset($_POST['add_subject'])) {
    $subject_name = trim($_POST['subject_name']);

    // Check if the subject name already exists
    $query = "SELECT COUNT(*) AS count FROM subjects WHERE subject_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $subject_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        echo "<script>alert('Error: The subject name already exists. Please try a different name.');</script>";
    } else {
        // Insert the new subject
        $query = "INSERT INTO subjects (subject_name) VALUES (?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $subject_name);

        if ($stmt->execute()) {
            echo "<script>alert('Subject added successfully.');</script>";
        } else {
            echo "<script>alert('Error: Could not add the subject. Please try again later.');</script>";
        }
    }
}

// Fetch all subjects to display in the table
$query = "SELECT id, subject_name FROM subjects ORDER BY subject_name ASC";
$result = $conn->query($query);

// Update subject
if (isset($_POST['update_subject'])) {
    $subject_id = $_POST['subject_id'];
    $updated_subject_name = trim($_POST['updated_subject_name']);

    // Update the subject name
    $query = "UPDATE subjects SET subject_name = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $updated_subject_name, $subject_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Subject updated successfully.');
                window.location.href = 'admin.php'; // Redirect back
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Error: Could not update the subject. Please try again later.');
                window.location.href = 'admin.php'; // Redirect back
            </script>";
        exit();
    }
}

// Delete subject
if (isset($_POST['delete_subject'])) {
    $subject_id = $_POST['subject_id'];

    // Delete the subject
    $query = "DELETE FROM subjects WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $subject_id);

    if ($stmt->execute()) {
        echo "<script>alert('Subject deleted successfully.');window.location.href = 'admin.php';</script>";
    } else {
        echo "<script>alert('Error: Could not delete the subject. Please try again later.');window.location.href = 'admin.php';</script>";
    }
}
?>
