<?php
session_start();
include '../config/config.php';

$conn = conn();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id = $_POST['subject_id'];
    $record_type = $_POST['record_type'];

    switch ($record_type) {
        case 'quiz':
            $quiz_name = $_POST['quiz_name'];
            $quiz_date = $_POST['quiz_date'];
            $quiz_score = $_POST['quiz_score']; // Total quiz score

            // Insert Quiz into quizzes table
            $query = "INSERT INTO quizzes (subject_id, quiz_name, date, total_score) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issd", $subject_id, $quiz_name, $quiz_date, $quiz_score);
            $stmt->execute();
            // No need to insert into quiz_scores table as total_score is handled here

            break;

        case 'activity':
            $activity_name = $_POST['activity_name'];
            $activity_score = $_POST['activity_score']; // Total activity score

            // Insert Activity into activities table
            $query = "INSERT INTO activities (subject_id, activity_name, total_score) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isd", $subject_id, $activity_name, $activity_score);
            $stmt->execute();
            // No need to insert into activity_scores table as total_score is handled here

            break;

            case 'attendance':
                $schedule_date = $_POST['schedule_date'];
                $start_time = $_POST['time_start'];
                $end_time = $_POST['time_end'];
            
                // Insert data into class_schedule table
                $query = "INSERT INTO class_schedule (subject_id, schedule_date, time_start, time_end) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("isss", $subject_id, $schedule_date, $start_time, $end_time);
                $stmt->execute();
            
                break;
            
            

        case 'exam':
            $exam_name = $_POST['exam_name'];
            $exam_date = $_POST['exam_date'];
            $exam_score = $_POST['exam_score']; // Total exam score

            // Insert Exam into exams table
            $query = "INSERT INTO exams (subject_id, exam_name, date, total_score) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issd", $subject_id, $exam_name, $exam_date, $exam_score);
            $stmt->execute();
            // No need to insert into exam_scores table as total_score is handled here

            break;
    }

    // Redirect or display success message
    echo "<script>
            alert('Data successfully added.');
            window.location.href = '../public/dashboard/instructor.php?success=1#inputRecords';
        </script>";
        exit();
}
