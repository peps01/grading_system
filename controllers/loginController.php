<?php
session_start();
include '../config/config.php';

//LOGIN INSTRUCTOR AND STUDENTS
if (isset($_POST['instructorSubmitBtn']) || isset($_POST['studentSubmitBtn'])) {  
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn = conn();  

    // fetch the the neccessary data 
    $query = "SELECT users.id, users.username, users.password, users.full_name, roles.role_name, users.role_id, users.status
              FROM users 
              INNER JOIN roles ON users.role_id = roles.id 
              WHERE username = '$username'";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // check if the user is pending or active
        if ($row['status'] === 'pending') {
            echo "<script>alert('Your account is still pending approval. Please contact the administrator.');</script>";
            echo "<script>window.location.href = '../login/instructor.php';</script>";
            exit();
        } elseif ($row['status'] !== 'active') {
            echo "<script>alert('Your account is not active. Please contact the administrator.');</script>";
            echo "<script>window.location.href = '../login/instructor.php';</script>";
            exit();
        }

        // chekc the password
        if (password_verify($password, $row['password'])) {
            // session variables or store data's
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role_id'] = $row['role_id'];
            $_SESSION['role_name'] = $row['role_name'];
            $_SESSION['username'] = $row['username']; 
            $_SESSION['full_name'] = $row['full_name']; 

            if (isset($_POST['instructorSubmitBtn']) && $row['role_name'] === 'Instructor') {
                header('Location: ../public/dashboard/instructor.php');
                exit();
            } elseif (isset($_POST['studentSubmitBtn']) && $row['role_name'] === 'Student') {
                header('Location: ../public/dashboard/student.php');
                exit();
            } else {
                echo "<script>alert('Invalid User for selected Role.');</script>";
                echo "<script>window.location.href = '../login/instructor.php';</script>";
            }
        } else {
            echo "<script>alert('Incorrect username or password');</script>";
            echo "<script>window.location.href = '../login/instructor.php';</script>";
        }
    } else {
        echo "<script>alert('Account does not exist');</script>";
        echo "<script>window.location.href = '../login/instructor.php';</script>";
    }
    mysqli_close($conn);
}

if (isset($_POST['instructorSubmitBtn'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // to connect to the databse 
    $conn = conn();
    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    try {
        // Prepare the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT users.id, users.username, users.password, users.full_name, roles.role_name, users.role_id, users.status
                                 FROM users 
                                 INNER JOIN roles ON users.role_id = roles.id 
                                 WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Preparation failed: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Check account status
            if ($row['status'] === 'pending') {
                echo "<script>alert('Your account is still pending approval. Please contact the administrator.');</script>";
                echo "<script>window.location.href = '../login/instructor.php';</script>";
                exit();
            } elseif ($row['status'] !== 'active') {
                echo "<script>alert('Your account is not active. Please contact the administrator.');</script>";
                echo "<script>window.location.href = '../login/instructor.php';</script>";
                exit();
            }

            // Validate the password
            if (password_verify($password, $row['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role_id'] = $row['role_id'];
                $_SESSION['role_name'] = $row['role_name'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['full_name'] = $row['full_name']; // Storing full_name

                // Redirect based on role
                if ($row['role_name'] === 'Instructor') {
                    header('Location: ../public/dashboard/instructor.php');
                    exit();
                } elseif ($row['role_name'] === 'Student') {
                    header('Location: ../public/dashboard/student.php');
                    exit();
                } else {
                    echo "<script>alert('Invalid User for selected Role.');</script>";
                    echo "<script>window.location.href = '../login/instructor.php';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Incorrect username or password');</script>";
                echo "<script>window.location.href = '../login/instructor.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Account does not exist');</script>";
            echo "<script>window.location.href = '../login/instructor.php';</script>";
            exit();
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        // Close the statement and connection
        if (isset($stmt) && $stmt) {
            $stmt->close();
        }
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

if (isset($_POST['createBtn'])) {
    header('Location: ../controllers/createAccount.php');
}



// Ensure session is started only once
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



$conn = conn();  // Open a new connection before queries
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
    $conn->close();  // Close the connection here
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




// Helper function to fetch section name by ID
function getSectionName($conn, $section_id) {
    $query = "SELECT section_name FROM sections WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $section_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['section_name'];
}

//update the admin for registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['userId'];
    $status = $input['status'];

    $conn = conn();
    $query = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}


$conn = conn(); // Establish database connection
 // Add a new subject to this instructor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructorId = 1; // Replace with the instructor's ID from the session

   
    if (isset($_POST['subject_id'])) {
        $subjectId = intval($_POST['subject_id']);

        // Check if subject is already assigned
        $query = "SELECT * FROM assigned_subjects WHERE instructor_id = ? AND subject_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $instructorId, $subjectId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            header("Location: ../public/dashboard/instructor.php?error=Subject is already assigned.");
            exit();
        }

        // Assign the subject
        $query = "INSERT INTO assigned_subjects (instructor_id, subject_id) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $instructorId, $subjectId);

        if ($stmt->execute()) {
            header("Location: ../public/dashboard/instructor.php?success=Subject added successfully.");
            exit();
        } else {
            echo "Error assigning subject: " . $conn->error;
        }
    }

    // Remove an assigned subject
    if (isset($_POST['assigned_subject_id'])) {
        $assignedSubjectId = intval($_POST['assigned_subject_id']);

        $query = "DELETE FROM assigned_subjects WHERE instructor_id = ? AND subject_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $instructorId, $assignedSubjectId);

        if ($stmt->execute()) {
            header("Location: ../public/dashboard/instructor.php?success=Subject removed successfully.");
            exit();
        } else {
            echo "Error removing subject: " . $conn->error;
        }
    }
}
$conn->close();

// Add a Section and Assign to Subjects
if (isset($_POST['action']) && $_POST['action'] == 'add_section') {
    $section_name = $_POST['section_name'];
    $subject_ids = $_POST['subject_ids'];

    $insertSectionQuery = "INSERT INTO sections (section_name) VALUES (?)";
    $stmt = $conn->prepare($insertSectionQuery);
    $stmt->bind_param("s", $section_name);

    if ($stmt->execute()) {
        $section_id = $stmt->insert_id;

        $assignQuery = "INSERT INTO section_subject (section_id, subject_id) VALUES (?, ?)";
        $assignStmt = $conn->prepare($assignQuery);

        foreach ($subject_ids as $subject_id) {
            $assignStmt->bind_param("ii", $section_id, $subject_id);
            $assignStmt->execute();
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

// Retrieve sections grouped by subjects assigned to an instructor
if (isset($_GET['action']) && $_GET['action'] == 'fetch_sections') {
    $query = "SELECT id, section_name FROM sections";
    $result = $conn->query($query);

    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }

    echo json_encode($sections);
    exit;
}

//update assigned sections 
if (isset($_POST['update_section_subjects'])) {
    $section_id = $_POST['section_id'];
    $subject_ids = $_POST['subject_ids']; // Array of new subject IDs

    // Clear existing assignments
    $deleteQuery = "DELETE FROM section_subject WHERE section_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $section_id);
    $stmt->execute();

    // Assign the section to new subjects
    $assignQuery = "INSERT INTO section_subject (section_id, subject_id) VALUES (?, ?)";
    $assignStmt = $conn->prepare($assignQuery);

    foreach ($subject_ids as $subject_id) {
        $assignStmt->bind_param("ii", $section_id, $subject_id);
        $assignStmt->execute();
    }
    $assignStmt->close();

    echo "Section assignments updated successfully!";
}

//delete section
if (isset($_POST['delete_section'])) {
    $section_id = $_POST['section_id'];

    $deleteQuery = "DELETE FROM sections WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $section_id);
    $stmt->execute();

    echo "Section deleted successfully!";
    $stmt->close();
}

// Connect to the database
$conn = conn();

// Query to fetch all active students with their subjects
$query = "
    SELECT 
        students.id AS student_id,
        student_data.usn,
        users.full_name,
        users.email,
        sections.section_name,
        GROUP_CONCAT(subjects.subject_name SEPARATOR ', ') AS enrolled_subjects
    FROM students
    JOIN users ON students.user_id = users.id
    JOIN sections ON students.section_id = sections.id
    LEFT JOIN section_subject ON sections.id = section_subject.section_id
    LEFT JOIN subjects ON section_subject.subject_id = subjects.id
    WHERE users.status = 'active' -- Only include active students
    GROUP BY students.id
    ORDER BY students.id
";
$result = mysqli_query($conn, $query);

// Check if query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Display the table
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='width: 100%; text-align: left;'>";
    echo "<tr>
            <th>ID</th>
            <th>USN</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Section</th>
            <th>Enrolled Subjects</th>
          </tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['student_id']}</td>
                <td>{$row['usn']}</td>
                <td>{$row['full_name']}</td>
                <td>{$row['email']}</td>
                <td>{$row['section_name']}</td>
                <td>{$row['enrolled_subjects']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No active students found. Please ensure students have an active status.";
}
// Close the connection
mysqli_close($conn);



// approval and rejection 
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['id'];
$status = $data['status'];
$email = $data['email'] ?? null;
$reason = $data['reason'] ?? null; // Fixed typo here

$response = ['success' => false, 'message' => 'Something went wrong'];

if ($status === 'active') {
    // Approval functionality remains unchanged
    $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'User approved successfully'];
    }
    $stmt->close();
} elseif ($status === 'rejected') {
    // Enhanced rejection functionality
    $subject = "Registration Rejected";
    $message = "Dear User,\n\nYour registration has been rejected. Reason: $reason.\n\nRegards,\nAdmin";
    $headers = "From: admin@example.com";

    if (mail($email, $subject, $message, $headers)) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'User rejected and account deleted successfully'];
        } else {
            $response['message'] = 'User rejected but failed to delete account';
        }
        $stmt->close();
    } else {
        $response['message'] = 'User rejected but failed to send email';
    }
}

echo json_encode($response);




