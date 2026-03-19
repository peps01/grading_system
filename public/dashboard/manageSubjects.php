<?php
session_start();
$configPath = __DIR__ . '/../../config/config.php';
include $configPath;

$conn = conn();
$response = ['success' => false, 'message' => ''];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_subject') {
        $subject_name = trim($_POST['subject_name']);

        // Check if the subject name already exists
        $query = "SELECT COUNT(*) AS count FROM subjects WHERE subject_name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $subject_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            $response['message'] = 'The subject name already exists. Please try a different name.';
        } else {
            // Insert the new subject
            $query = "INSERT INTO subjects (subject_name) VALUES (?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $subject_name);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Subject added successfully.';
                $response['subject'] = [
                    'id' => $stmt->insert_id,
                    'subject_name' => $subject_name
                ];
            } else {
                $response['message'] = 'Could not add the subject. Please try again later.';
            }
        }
    } elseif ($action === 'update_subject') {
        $subject_id = $_POST['subject_id'];
        $updated_subject_name = trim($_POST['updated_subject_name']);

        // Update the subject name
        $query = "UPDATE subjects SET subject_name = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $updated_subject_name, $subject_id);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Subject updated successfully.';
            $response['subject'] = [
                'id' => $subject_id,
                'subject_name' => $updated_subject_name
            ];
        } else {
            $response['message'] = 'Could not update the subject. Please try again later.';
        }
    } elseif ($action === 'delete_subject') {
        $subject_id = $_POST['subject_id'];

        // Delete the subject
        $query = "DELETE FROM subjects WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $subject_id);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Subject deleted successfully.';
        } else {
            $response['message'] = 'Could not delete the subject. Please try again later.';
        }
    }

    echo json_encode($response);
    exit();
}
?>
