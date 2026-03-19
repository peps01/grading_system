<?php
include '../config/config.php';
$conn = conn();

header('Content-Type: application/json');
$response = ['success' => false, 'records' => [], 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['subject_id']) && isset($_GET['type'])) {
    $conn = conn();
    $subject_id = intval($_GET['subject_id']);
    $type = $_GET['type'];

    try {
        // Determine table and column mappings
        $tableMap = [
            'quiz' => ['table' => 'quizzes', 'columns' => ['id', 'quiz_name', 'date', 'total_score']],
            'activity' => ['table' => 'activities', 'columns' => ['id', 'activity_name', 'total_score']],
            'class_schedule' => ['table' => 'class_schedule', 'columns' => ['id', 'schedule_date', 'time_start', 'time_end']],
            'exam' => ['table' => 'exams', 'columns' => ['id', 'exam_name', 'date', 'total_score']]
        ];

        if (array_key_exists($type, $tableMap)) {
            $table = $tableMap[$type]['table'];
            $columns = implode(', ', $tableMap[$type]['columns']);

            $query = "SELECT $columns FROM $table WHERE subject_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $subject_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $records = [];
            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }

            $response['success'] = true;
            $response['records'] = $records;
            $response['message'] = ucfirst($type) . ' records fetched successfully.';
        } else {
            $response['message'] = 'Invalid record type.';
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

echo json_encode($response);
exit();
