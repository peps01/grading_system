<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: parent.php?error=not_logged_in");
    exit;
}

$student_id = $_SESSION['student_id'];

// Include database connection
include '../../config/config.php';
$conn = conn();

// Fetch student details, including section_name from the `sections` table
$query = "
    SELECT 
        student_data.full_name, 
        student_data.usn, 
        sections.section_name, 
        GROUP_CONCAT(subjects.subject_name SEPARATOR ', ') AS subjects
    FROM student_data
    LEFT JOIN students ON student_data.student_id = students.id
    LEFT JOIN sections ON students.section_id = sections.id
    LEFT JOIN section_subject ON sections.id = section_subject.section_id
    LEFT JOIN subjects ON section_subject.subject_id = subjects.id
    WHERE student_data.student_id = ?
    GROUP BY student_data.student_id";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($full_name, $usn, $section_name, $subjects);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Parent Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/grading-system/public/assets/css/admin.css">
  <style>
    .hidden { display: none; }
    .active { display: block; }
  </style>
  <script>
    function showSection(sectionId) {
      const sections = document.querySelectorAll('.content-section');
      sections.forEach(section => section.classList.add('hidden'));
      document.getElementById(sectionId).classList.remove('hidden');
    }
  </script>
</head>
<body class="bg-gray-100 text-black">
  <!-- Navbar -->
  <nav class="bg-blue-900 text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
      <a class="text-3xl font-bold" href="#">Parent Dashboard</a>
      <a class="px-4 py-2 rounded bg-blue-700 hover:bg-blue-600" href="logout.php">Logout</a>
    </div>
  </nav>
  <div class="flex">
    <aside class="w-64 bg-blue-800 text-white min-h-screen p-4">
      <h2 class="text-xl font-bold mb-6">Parent Panel</h2>
      <ul>
        <li class="mb-4"><a href="#" onclick="showSection('studentsSection')">View Child's Details</a></li>
        <li class="mb-4"><a href="#" onclick="showSection('viewGrades')">View Child's Grades</a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
      <!-- View Child's Details Section -->
      <section id="studentsSection" class="content-section mb-8">
        <h2 class="text-3xl font-bold mb-4">Child's Details</h2>
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <h3 class="text-2xl font-semibold mb-2">Name: <?php echo htmlspecialchars($full_name); ?></h3>
          <p>USN: <?php echo htmlspecialchars($usn); ?></p>
          <p>Section: <?php echo htmlspecialchars($section_name); ?></p>
          <p>Subjects: <?php echo htmlspecialchars($subjects); ?></p>
        </div>
      </section>

      <!-- View Grades Section -->
      <section id="viewGrades" class="content-section hidden">
        <h2 class="text-3xl font-bold mb-4">My Grade Records</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php
          // Fetch subjects, instructor names, and grades for the student's section
          $query_subjects = "
              SELECT DISTINCT 
                  subjects.id AS subject_id,
                  subjects.subject_name,
                  users.full_name AS instructor_name
              FROM subjects
              JOIN section_subject ON subjects.id = section_subject.subject_id
              JOIN sections ON sections.id = section_subject.section_id
              JOIN students ON students.section_id = sections.id
              JOIN assigned_subjects ON assigned_subjects.subject_id = subjects.id
              JOIN users ON assigned_subjects.instructor_id = users.id
              WHERE students.id = ?";
          $stmt = $conn->prepare($query_subjects);
          $stmt->bind_param("i", $student_id);
          $stmt->execute();
          $subjects = $stmt->get_result();

          if ($subjects->num_rows > 0):
              while ($subject = $subjects->fetch_assoc()):
                  $subject_id = $subject['subject_id'];

                  // Fetch grade weights
                  $query_weights = "SELECT quiz_weight, activity_weight, attendance_weight, exam_weight FROM grade_weights WHERE subject_id = ?";
                  $stmt_weights = $conn->prepare($query_weights);
                  $stmt_weights->bind_param("i", $subject_id);
                  $stmt_weights->execute();
                  $weights_result = $stmt_weights->get_result();
                  $weights = $weights_result->fetch_assoc();

                  if (!$weights) {
                      echo "<div>No grade weights found for the subject: " . htmlspecialchars($subject['subject_name']) . ".</div>";
                      continue;
                  }

                  $total_weighted_score = 0;
                  $total_weight = 0;

                  // Query attendance total classes
                $query_total_classes = "
                SELECT schedule_date, time_start, time_end 
                FROM class_schedule 
                WHERE subject_id = ?";
            $stmt_total_classes = $conn->prepare($query_total_classes);
            $stmt_total_classes->bind_param("i", $subject_id);
            $stmt_total_classes->execute();
            $total_classes_result = $stmt_total_classes->get_result();
            $total_classes = $total_classes_result->fetch_all(MYSQLI_ASSOC);

            // Initialize attendance details
            $final_grade_details = [];
            $attendance_percentage = 0;
            $quiz_weighted_score = 0;
            $activity_weighted_score = 0;
            $attendance_weighted_score = 0;
            $exam_weighted_score = 0;

            // Query grades
            $query_grades = [
                'quizzes' => "
                    SELECT quizzes.quiz_name, quiz_scores.score, quizzes.total_score 
                    FROM quizzes
                    JOIN quiz_scores ON quizzes.id = quiz_scores.quiz_id
                    WHERE quizzes.subject_id = ? AND quiz_scores.student_id = ?",
                'activities' => "
                    SELECT activities.activity_name, activity_scores.score, activities.total_score
                    FROM activities
                    JOIN activity_scores ON activities.id = activity_scores.activity_id
                    WHERE activities.subject_id = ? AND activity_scores.student_id = ?",
                'attendance' => "
                    SELECT COUNT(*) AS present_count 
                    FROM attendance 
                    WHERE subject_id = ? AND student_id = ? AND status = 'Present'",
                'exams' => "
                    SELECT exams.exam_name, exam_scores.score, exams.total_score 
                    FROM exams 
                    JOIN exam_scores ON exams.id = exam_scores.exam_id 
                    WHERE exams.subject_id = ? AND exam_scores.student_id = ?"
            ];

            // Process grades for each category
            foreach ($query_grades as $key => $query) {
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $subject_id, $student_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $final_grade_details[$key] = $result->fetch_all(MYSQLI_ASSOC);

                if ($key === 'attendance') {
                    $present_count = $final_grade_details[$key][0]['present_count'] ?? 0;
                    $attendance_percentage = count($total_classes) > 0 ? ($present_count / count($total_classes)) * 100 : 0;
                    $attendance_weighted_score = $attendance_percentage * ($weights['attendance_weight'] / 100);
                    $total_weighted_score += $attendance_weighted_score;
                    $total_weight += $weights['attendance_weight'];
                }

                if ($key === 'quizzes' && !empty($final_grade_details['quizzes'])) {
                    $quiz_total_score = 0;
                    $quiz_achieved_score = 0;
                    foreach ($final_grade_details['quizzes'] as $quiz) {
                        $quiz_achieved_score += $quiz['score'];
                        $quiz_total_score += $quiz['total_score'];
                    }
                    $quiz_percentage = $quiz_total_score > 0 ? ($quiz_achieved_score / $quiz_total_score) * 100 : 0;
                    $quiz_weighted_score = $quiz_percentage * ($weights['quiz_weight'] / 100);
                    $total_weighted_score += $quiz_weighted_score;
                    $total_weight += $weights['quiz_weight'];
                }

                if ($key === 'activities' && !empty($final_grade_details['activities'])) {
                    $activity_total_score = 0;
                    $activity_achieved_score = 0;
                    foreach ($final_grade_details['activities'] as $activity) {
                        $activity_achieved_score += $activity['score'];
                        $activity_total_score += $activity['total_score'];
                    }
                    $activity_percentage = $activity_total_score > 0 ? ($activity_achieved_score / $activity_total_score) * 100 : 0;
                    $activity_weighted_score = $activity_percentage * ($weights['activity_weight'] / 100);
                    $total_weighted_score += $activity_weighted_score;
                    $total_weight += $weights['activity_weight'];
                }

                if ($key === 'exams' && !empty($final_grade_details['exams'])) {
                    $exam_total_score = 0;
                    $exam_achieved_score = 0;
                    foreach ($final_grade_details['exams'] as $exam) {
                        $exam_achieved_score += $exam['score'];
                        $exam_total_score += $exam['total_score'];
                    }
                    $exam_percentage = $exam_total_score > 0 ? ($exam_achieved_score / $exam_total_score) * 100 : 0;
                    $exam_weighted_score = $exam_percentage * ($weights['exam_weight'] / 100);
                    $total_weighted_score += $exam_weighted_score;
                    $total_weight += $weights['exam_weight'];
                }
            }

            // Calculate final grade
            $final_grade = $total_weight > 0 ? ($total_weighted_score / $total_weight) * 100 : 0;
    ?>
                 <div class="bg-gray-100 p-4 rounded-lg shadow-md">
                        <h3 class="text-2xl font-semibold cursor-pointer" onclick="toggleGradeDetails('<?php echo $subject_id; ?>')">
                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                        </h3>
                        <p class="text-gray-600 text-sm">Instructor: <?php echo htmlspecialchars($subject['instructor_name']); ?></p>
                        <p class="text-gray-800 font-bold mt-2">Final Grade: <?php echo number_format($final_grade, 2); ?>%</p>
                        <div id="grades-<?php echo $subject_id; ?>" class="hidden mt-4">

                        <h4 class="text-xl font-semibold">Detailed Breakdown</h4>

                            <!-- Quizzes -->
                            <h5 class="font-semibold mt-2">Quizzes</h5>
                            <p>Contribution to Final Grade: <?php echo number_format($weights['quiz_weight'], 2); ?>%</p>
                            <table class="min-w-full bg-white border rounded">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 border">Quiz Name</th>
                                        <th class="px-4 py-2 border">Score</th>
                                        <th class="px-4 py-2 border">Total Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($final_grade_details['quizzes'])): ?>
                                        <?php foreach ($final_grade_details['quizzes'] as $quiz): ?>
                                            <tr>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($quiz['quiz_name']); ?></td>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($quiz['score']); ?></td>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($quiz['total_score']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 border text-center">No quizzes found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Activities -->
                            <h5 class="font-semibold mt-2">Activities</h5>
                            <p>Contribution to Final Grade: <?php echo number_format($weights['activity_weight'], 2); ?>%</p>
                            <table class="min-w-full bg-white border rounded">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 border">Activity Name</th>
                                        <th class="px-4 py-2 border">Score</th>
                                        <th class="px-4 py-2 border">Total Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($final_grade_details['activities'])): ?>
                                        <?php foreach ($final_grade_details['activities'] as $activity): ?>
                                            <tr>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($activity['activity_name']); ?></td>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($activity['score']); ?></td>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($activity['total_score']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 border text-center">No activities found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Exams -->
                            <h5 class="font-semibold mt-2">Exams</h5>
                            <p>Contribution to Final Grade: <?php echo number_format($weights['exam_weight'], 2); ?>%</p>
                            <table class="min-w-full bg-white border rounded">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 border">Exam Name</th>
                                        <th class="px-4 py-2 border">Score</th>
                                        <th class="px-4 py-2 border">Total Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($final_grade_details['exams'])): ?>
                                        <?php foreach ($final_grade_details['exams'] as $exam): ?>
                                            <tr>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($exam['score']); ?></td>
                                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($exam['total_score']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 border text-center">No exams found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Attendance -->
                            <h5 class="font-semibold mt-2">Attendance</h5>
                            <p>Contribution to Final Grade: <?php echo number_format($weights['attendance_weight'], 2); ?>%</p>
                            <p>Present: <?php echo htmlspecialchars($present_count); ?></p>
                            <p>Total Classes: <?php echo htmlspecialchars(count($total_classes)); ?></p>
                            <p>Attendance Percentage: <?php echo number_format($attendance_percentage, 2); ?>%</p>
                            <ul class="list-disc pl-5">
                                <?php if (!empty($total_classes)): ?>
                                    <?php foreach ($total_classes as $class): ?>
                                        <li><?php echo htmlspecialchars($class['schedule_date']); ?> - <?php echo htmlspecialchars($class['time_start'] . ' to ' . $class['time_end']); ?></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li>No class schedules found for this subject.</li>
                                <?php endif; ?>
                            </ul>

                            <!-- Final Grade -->
                            <h5 class="font-semibold mt-4">Final Grade</h5>
                            <p>Total Contribution:</p>
                            <ul class="list-disc pl-5">
                                <li>Quizzes: <?php echo number_format($quiz_weighted_score, 2); ?>%</li>
                                <li>Activities: <?php echo number_format($activity_weighted_score, 2); ?>%</li>
                                <li>Exams: <?php echo number_format($exam_weighted_score, 2); ?>%</li>
                                <li>Attendance: <?php echo number_format($attendance_weighted_score, 2); ?>%</li>
                            </ul>
                            <p class="text-lg font-bold mt-2">Final Grade: <?php echo number_format($final_grade, 2); ?>%</p>
                        </div>
                    </div>
            <?php
                endwhile;
            else:
            ?>
                <p>No subjects found for this student.</p>
            <?php endif; ?>
        </div>
    </section>
    
      <!-- Send Feedback Section -->
      <section id="feedbackSection" class="content-section mb-8 hidden">
        <h2 class="text-3xl font-bold mb-4">Send Feedback</h2>
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <form action="sendFeedback.php" method="POST">
            <div class="mb-4">
              <label class="block text-left text-gray-700 font-bold mb-2" for="feedback">Your Feedback</label>
              <textarea class="w-full p-2 border border-gray-300 rounded" id="feedback" name="feedback" rows="4"></textarea>
            </div>
            <button class="bg-blue-700 text-white px-4 py-2 rounded shadow hover:bg-blue-600 transition duration-200" type="submit">Send Feedback</button>
          </form>
        </div>
      </section>
    </main>
  </div>
  <script>
    function toggleGradeDetails(subjectId) {
        const detailsDiv = document.getElementById('grades-' + subjectId);
        if (detailsDiv.classList.contains('hidden')) {
            detailsDiv.classList.remove('hidden');
        } else {
            detailsDiv.classList.add('hidden');
        }
    }
    </script>
</body>
</html>

