<?php
session_start();
include '../config/config.php';

// create instructor account
if (isset($_POST['createInstructorBtn'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $instructor_code = $_POST['instructor_code'];
    $role_id = $_POST['role_id'];

    $SECRET_CODE = "SECRET2024";

    if ($instructor_code === $SECRET_CODE) {
        $conn = conn();

        $check_query = "
            SELECT * FROM users 
            WHERE username = ? OR email = ?
        ";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Username or email already in use. Please choose another.');</script>";
            echo "<script>window.location.href = '../login/createAccount.php';</script>";
        } else {
            $insert_query = "
                INSERT INTO users (username, password, full_name, email, role_id, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')
            ";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssssi", $username, $password, $full_name, $email, $role_id);

            if ($insert_stmt->execute()) {
                echo "<script>alert('Account created successfully! Await admin approval.');</script>";
                echo "<script>window.location.href = '../login/instructor.php';</script>";
            } else {
                echo "<script>alert('Error creating account. Please try again later.');</script>";
                echo "<script>window.location.href = '../login/createAccount.php';</script>";
            }
        }

        $stmt->close();
        $insert_stmt->close();
        mysqli_close($conn);
    } else {
        echo "<script>alert('Invalid Instructor Code. Please contact admin.');</script>";
        echo "<script>window.location.href = '../login/createAccount.php';</script>";
    }
}
?>