<?php
session_start();
include_once '../config/config.php';
// Function to add a subject to a section
function addSubjectToSection($section_id, $subject_id) {
    $conn = conn();

    // Check if the subject is already assigned to the section
    $checkQuery = "SELECT * FROM section_subject WHERE section_id = ? AND subject_id = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "ii", $section_id, $subject_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: red;'>This subject is already assigned to the section.</p>";
    } else {
        // Insert the subject-section relationship
        $insertQuery = "INSERT INTO section_subject (section_id, subject_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, "ii", $section_id, $subject_id);

        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>Subject successfully added to the section.</p>";
        } else {
            echo "<p style='color: red;'>Error adding subject to section: " . mysqli_error($conn) . "</p>";
        }
    }

    // Close the statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_id = $_POST['section_id'];
    $subject_id = $_POST['subject_id'];

    addSubjectToSection($section_id, $subject_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject to Section</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f9;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, button {
            padding: 10px;
            margin-bottom: 15px;
            width: 100%;
            max-width: 300px;
        }
        button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Assign Subject to Section</h1>

    <form method="POST" action="">
        <label for="section_id">Select Section:</label>
        <select name="section_id" id="section_id" required>
            <option value="1">WAD2AB</option>
            <option value="2">WAD2CD</option>
            <option value="3">BSIT2A</option>
            <option value="4">BSIT2B</option>
            <option value="5">BSIT2C</option>
            <option value="6">BSIT2D</option>
        </select>

        <label for="subject_id">Select Subject:</label>
        <select name="subject_id" id="subject_id" required>
            <option value="1">System Integration and Architecture 1</option>
            <option value="2">Introduction to Human Computer Interaction</option>
            <option value="3">IT Major Elective 1 Web Application Development</option>
            <option value="4">Integrative Programming and Technology</option>
        </select>

        <button type="submit">Assign Subject</button>
    </form>
</body>
</html>
