<?php
session_start();
include '../config/config.php';

$conn = conn();
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id = $_POST['subject_id'];
    $record_type = $_POST['record_type'];

    try {
        switch ($record_type) {
            case 'quiz':
                $quiz_name = $_POST['quiz_name'];
                $quiz_date = $_POST['quiz_date'];
                $quiz_score = $_POST['quiz_score']; // Total quiz score

                $query = "INSERT INTO quizzes (subject_id, quiz_name, date, total_score) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issd", $subject_id, $quiz_name, $quiz_date, $quiz_score);
                $stmt->execute();
                $response['success'] = true;
                $response['message'] = "Quiz added successfully!";
                break;

            case 'activity':
                $activity_name = $_POST['activity_name'];
                $activity_score = $_POST['activity_score'];

                $query = "INSERT INTO activities (subject_id, activity_name, total_score) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("isd", $subject_id, $activity_name, $activity_score);
                $stmt->execute();
                $response['success'] = true;
                $response['message'] = "Activity added successfully!";
                break;

                
            case 'class_schedule':
                $schedule_date = $_POST['schedule_date'];
                $time_start = $_POST['time_start'];
                $time_end = $_POST['time_end'];
                $subject_id = $_POST['subject_id'];
            
                try {
                    // Insert schedule into the class_schedule table
                    $query = "INSERT INTO class_schedule (schedule_date, time_start, time_end, subject_id) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssi", $schedule_date, $time_start, $time_end, $subject_id);
                    $stmt->execute();
            
                    $response['success'] = true;
                    $response['message'] = "Class schedule added successfully!";
                } catch (Exception $e) {
                    $response['success'] = false;
                    $response['message'] = "Error adding class schedule: " . $e->getMessage();
                }
                break;
                

            case 'exam':
                $exam_name = $_POST['exam_name'];
                $exam_date = $_POST['exam_date'];
                $exam_score = $_POST['exam_score'];

                $query = "INSERT INTO exams (subject_id, exam_name, date, total_score) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issd", $subject_id, $exam_name, $exam_date, $exam_score);
                $stmt->execute();
                $response['success'] = true;
                $response['message'] = "Exam added successfully!";
                break;
        }
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }
}

echo json_encode($response);
exit();
