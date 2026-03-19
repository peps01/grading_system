
<?php
session_start();
// Use an absolute path or check for the file's existence
include '../config/config.php';

//STUDENT CREATE ACCOUNT
if (isset($_POST['createStudentAccountBtn'])) {
    $conn = conn();
    $usn = $_POST['usn'];
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Capture the password before hashing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password
    $section_id = $_POST['section_id'];
    $email = $_POST['email']; // Capture the email

    // Check if username, email, or USN already exists
    $checkUsernameQuery = "SELECT * FROM users WHERE username = ?";
    $checkEmailQuery = "SELECT * FROM users WHERE email = ?";
    $checkUsnQuery = "SELECT * FROM student_data WHERE usn = ?";

    // Check username
    $stmt = $conn->prepare($checkUsernameQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $usernameResult = $stmt->get_result();
    if ($usernameResult->num_rows > 0) {
        echo "<script>
            alert('Username already exists. Please choose a different username.');
            window.location.href = '../login/studentCreate.php'; // Reload or redirect
        </script>";
        exit;
    }

    // Check email
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $emailResult = $stmt->get_result();
    if ($emailResult->num_rows > 0) {
        echo "<script>
            alert('Email already exists! Please choose another.');
            window.location.href = '../login/studentCreate.php'; // Reload or redirect
        </script>";
        exit;
    }

    // Check USN
    $stmt = $conn->prepare($checkUsnQuery);
    $stmt->bind_param("s", $usn);
    $stmt->execute();
    $usnResult = $stmt->get_result();
    if ($usnResult->num_rows > 0) {
        echo "<script>
            alert('USN already exists!');
            window.location.href = '../login/studentCreate.php'; // Reload or redirect
        </script>";
        echo "<script>alert('USN already exists! Please choose another.');</script>";
        exit;
    }

    // Insert user
    $insertUserQuery = "INSERT INTO users (username, password, full_name, email, role_id, status) 
                        VALUES (?, ?, ?, ?, 2, 'pending')";
    $stmt = $conn->prepare($insertUserQuery);
    $stmt->bind_param("ssss", $username, $hashedPassword, $full_name, $email); // Use hashed password

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Insert student
        $insertStudentQuery = "INSERT INTO students (user_id, section_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insertStudentQuery);
        $stmt->bind_param("ii", $user_id,  $section_id);

        if ($stmt->execute()) {
            $student_id = $stmt->insert_id;

            // Insert into student_data
            $insertStudentDataQuery = "INSERT INTO student_data (student_id, usn, full_name) 
                                       VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertStudentDataQuery);
            $stmt->bind_param("iss", $student_id, $usn, $full_name);

            if ($stmt->execute()) {
                echo "<script>alert('Student account created successfully! Pending approval by admin.'); window.location.href='../public/role.php';</script>";
            } else {
                echo "<script>alert('Failed to save student data. Please try again.'); window.location.href='../login/studentController.php';</script>";
            }
        } else {
            echo "<script>alert('Failed to insert student record. Please try again.'); window.location.href='../login/studentController.php';</script>";
        }
    } else {
        echo "<script>alert('Failed to create account. Please try again.'); window.location.href='../login/studentController.php';</script>";
    }

    $stmt->close();
    $conn->close();
}