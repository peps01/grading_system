<?php
include '../config/config.php';


$conn = conn();  

// Delete assignment action
if (isset($_POST['removeAssignmentBtn'])) {
    $assignmentId = $_POST['assignment_id'];

    // Create a new connection each time
    $query = "DELETE FROM section_subject WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $assignmentId);

    if ($stmt->execute()) {
        echo "<script>alert('Assignment removed successfully!');</script>";
        echo "<script>window.location.href = '../public/dashboard/instructor.php';</script>";
    } else {
        echo "<script>alert('Failed to remove assignment. Please try again.');</script>";
        echo "<script>window.location.href = '../public/dashboard/instructor.php';</script>";
    }
    $stmt->close();
    $conn->close();  
}

// Remove assigned subject for instructor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = conn();  // Open a new connection here
    $instructorId = intval($_SESSION['user_id']); // Current instructor's ID

    // Remove an assigned subject
    if (isset($_POST['assigned_subject_id'])) {
        $subjectId = intval($_POST['assigned_subject_id']);
    
        // Start a transaction
        $conn->begin_transaction();
    
        try {
            // Step 1: Remove related entries from `section_subject`
            $query = "DELETE FROM section_subject WHERE subject_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $subjectId);
            $stmt->execute();
    
            // Step 2: Remove from `assigned_subjects`
            $query = "DELETE FROM assigned_subjects WHERE instructor_id = ? AND subject_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $instructorId, $subjectId);
            $stmt->execute();
    
            // Commit transaction
            $conn->commit();
    
            // Redirect with success message
            echo "<script>alert('Subject removed successfully.');</script>";
            header("Location: ../public/dashboard/instructor.php?success=Subject removed successfully.");
            exit();
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            echo "Error removing subject: " . $e->getMessage();
        }
    }
    $stmt->close();
    $conn->close();  // Close the connection after use
}

// Add or reassign subject logic
if (isset($_POST['subject_id'])) {
    $subjectId = intval($_POST['subject_id']);
    $conn = conn();  // Open a new connection here

    // Check if the subject is assigned to another instructor
    $query = "SELECT instructor_id FROM assigned_subjects WHERE subject_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $subjectId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentInstructorId = intval($row['instructor_id']);

        if ($currentInstructorId !== $instructorId) {
            // Reassign subject to the current instructor
            $query = "UPDATE assigned_subjects SET instructor_id = ? WHERE subject_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $instructorId, $subjectId);
            $stmt->execute();
            echo "<script>alert('Subject reassigned successfully.');</script>";
            header("Location: ../public/dashboard/instructor.php?success=Subject reassigned successfully.");
            exit();
        } else {
            echo "<script>alert('Subject already assigned to you..');</script>";
            header("Location: ../public/dashboard/instructor.php?error=Subject already assigned to you.");
            exit();
        }
    } else {
        // Otherwise, assign the subject
        $query = "INSERT INTO assigned_subjects (instructor_id, subject_id) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $instructorId, $subjectId);

        if ($stmt->execute()) {
            echo "<script>alert('Subject added successfully.');</script>";
            header("Location: ../public/dashboard/instructor.php?success=Subject added successfully.");
            exit();
        } else {
            echo "Error assigning subject: " . $conn->error;
        }
    }
    $stmt->close();
    $conn->close();  // Close the connection after use
}

error_log("Instructor ID: $instructorId, Subject ID: $subjectId");
