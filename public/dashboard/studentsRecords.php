<?php
  session_start();

  // Check if the user is logged in and has the correct role (Instructor)
  if (!isset($_SESSION['user_id']) || $_SESSION['role_name'] !== 'Instructor') {
      header("Location: /grading-system/public/role.php");
      exit();
  }

  $role_name = $_SESSION['role_name'];
  $role_id = $_SESSION['role_id'];
  $full_name = $_SESSION['full_name'];
  $user_id = $_SESSION['user_id'];
  ?>
 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="main.js" ></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .hidden {
      display: none;
    }
    .active {
      display: block;
    }

    
  </style>
</head>
<script>
  function showSection(sectionId) {
      const sections = document.querySelectorAll('.content-section');
      sections.forEach(section => section.classList.add('hidden'));
      document.getElementById(sectionId).classList.remove('hidden');
    }
</script>
<body class="font-roboto bg-gray-100 text-black flex flex-col min-h-screen">
  <nav class="bg-blue-900 text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
      <h1 class="text-3xl font-bold">Grading System</h1>
      <div>
        <a class="px-4 py-2 rounded bg-blue-700 hover:bg-blue-600 transition duration-200" href="logout.php">Logout</a>
      </div>
    </div>
  </nav>
  <div class="flex flex-1">
    <aside class="w-64 bg-blue-800 text-white min-h-screen p-4">
    <?php
    // db connection
      include_once '../../config/config.php';

      $conn = conn();

      // Assuming $user_id is available, either from session or a previous query
      $user_id = $_SESSION['user_id']; 

      // Retrieve the image path from the database
      $query = "SELECT profile_image FROM users WHERE id = ?";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("i", $user_id); // Replace $user_id with the actual user ID
      $stmt->execute();
      $stmt->bind_result($image_path);
      $stmt->fetch();

      // Set a default image if the user hasn't uploaded one
      $image_path = $image_path ? $image_path : '..\dashboard\extras\img1.jpg'; // Default image
      ?>
    <div class="text-center mb-6">
        <img id="profileImage" alt="Portrait of the instructor" class="w-24 h-24 rounded-full mx-auto mb-4" src="<?php echo $image_path ?>" />
        <h2 class="text-xl font-bold"><?php echo $full_name ?></h2>
        <p class="text-sm"><?php echo $role_name ?></p>

        <form action="../../controllers/loginController.php" method="POST" enctype="multipart/form-data">
            <label for="imageUpload" class="text-blue-500 cursor-pointer">Upload Profile Image</label>
            <input type="file" name="image" id="imageUpload" class="hidden" onchange="previewImage(event)" />
            <button type="submit" name="submit" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Upload</button>
        </form>
    </div>
      <ul>
      <li class="mb-4"><a class="hover:underline" href="instructor.php" >Dashboard</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('viewStudentGrades')">Manage Quiz, Activities and Exam</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('inputRecords')">Manage Records</a></li>
      </ul>
    </aside>
    <main class="flex-1 p-8">
      
        <!-- apply grade weight and view grade weight -->
        <section id="viewStudentGrades" class="content-section">
            <h2 class="text-3xl font-bold mb-4">Student Grades by Section</h2>

            <!-- dropdown subjectss -->
            <form method="POST" action="">
                <label for="subject_id">Select Subject:</label>
                <select name="subject_id" id="subject_id" onchange="this.form.submit()" required>
                    <option value="">-- Choose Subject --</option>
                    <?php
                    $conn = conn();
                    $query = "
                        SELECT DISTINCT subjects.id, subjects.subject_name
                        FROM subjects
                        JOIN assigned_subjects ON assigned_subjects.subject_id = subjects.id
                        WHERE assigned_subjects.instructor_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $subjectResult = $stmt->get_result();

                    while ($subjectRow = $subjectResult->fetch_assoc()) {
                        $selected = isset($_POST['subject_id']) && $_POST['subject_id'] == $subjectRow['id'] ? 'selected' : '';
                        echo '<option value="' . $subjectRow['id'] . '" ' . $selected . '>' . htmlspecialchars($subjectRow['subject_name']) . '</option>';
                    }
                    ?>
                </select>
            </form>

            <?php if (isset($_POST['subject_id']) && $_POST['subject_id'] != ''): ?>
                <?php
                $subject_id = $_POST['subject_id'];
                $query_fetch_weights = "SELECT * FROM grade_weights WHERE subject_id = ?";
                $stmt = $conn->prepare($query_fetch_weights);
                $stmt->bind_param("i", $subject_id);
                $stmt->execute();
                $weights = $stmt->get_result()->fetch_assoc();

                $existing_weights = !empty($weights); // Check if weights already exist
                $quiz_weight = $weights['quiz_weight'] ?? 20;
                $activity_weight = $weights['activity_weight'] ?? 30;
                $attendance_weight = $weights['attendance_weight'] ?? 10;
                $exam_weight = $weights['exam_weight'] ?? 40;

                // Handle saving weights
                if (isset($_POST['save_weights'])) {
                    $quiz_weight = $_POST['quiz_weight'];
                    $activity_weight = $_POST['activity_weight'];
                    $attendance_weight = $_POST['attendance_weight'];
                    $exam_weight = $_POST['exam_weight'];

                    $total_weight = $quiz_weight + $activity_weight + $attendance_weight + $exam_weight;

                    if ($total_weight == 100) {
                        if ($existing_weights) {
                            // Update existing weights
                            $query_update_weights = "
                                UPDATE grade_weights 
                                SET quiz_weight = ?, activity_weight = ?, attendance_weight = ?, exam_weight = ? 
                                WHERE subject_id = ?";
                            $stmt = $conn->prepare($query_update_weights);
                            $stmt->bind_param("iiiii", $quiz_weight, $activity_weight, $attendance_weight, $exam_weight, $subject_id);
                        } else {
                            // Insert new weights
                            $query_insert_weights = "
                                INSERT INTO grade_weights (subject_id, quiz_weight, activity_weight, attendance_weight, exam_weight) 
                                VALUES (?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($query_insert_weights);
                            $stmt->bind_param("iiiii", $subject_id, $quiz_weight, $activity_weight, $attendance_weight, $exam_weight);
                        }
                        $stmt->execute();
                        echo '<p class="text-green-500">Weights saved successfully!</p>';
                    } else {
                        echo '<p class="text-red-500 font-bold mt-4">Error: The total weight must equal 100%. Current total: ' . htmlspecialchars($total_weight) . '%</p>';
                    }
                }
                ?>

                <!-- Weight Inputs -->
                <?php if (!$existing_weights): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($subject_id); ?>">
                        <label for="quiz_weight ">Quiz Weight:</label>
                        <input type="number" step="0.01" name="quiz_weight" value="<?php echo htmlspecialchars($quiz_weight); ?>" required>%<br>

                        <label for="activity_weight">Activity Weight:</label>
                        <input type="number" step="0.01" name="activity_weight" value="<?php echo htmlspecialchars($activity_weight); ?>" required>%<br>

                        <label for="attendance_weight">Attendance Weight:</label>
                        <input type="number" step="0.01" name="attendance_weight" value="<?php echo htmlspecialchars($attendance_weight); ?>" required>%<br>

                        <label for="exam_weight">Exam Weight:</label>
                        <input type="number" step="0.01" name="exam_weight" value="<?php echo htmlspecialchars($exam_weight); ?>" required>%<br>

                        <button type="submit" name="save_weights" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Apply Weights</button>
                    </form>
                <?php else: ?>
                    <h3 class="text-xl font-semibold mt-4 mb-2">
                        Existing Weights:
                        <br>
                        Quiz Weight: <?php echo htmlspecialchars($quiz_weight); ?>%<br>
                        Activity Weight: <?php echo htmlspecialchars($activity_weight); ?>%<br>
                        Attendance Weight: <?php echo htmlspecialchars($attendance_weight); ?>%<br>
                        Exam Weight: <?php echo htmlspecialchars($exam_weight); ?>%
                    </h3>

                    <!-- Display Overall Scores and Grades by Section -->
                    <?php
                    // Fetch total scores for quizzes, activities, and exams from their respective tables
                    $query_total_scores = "
                        SELECT 
                            (SELECT SUM(total_score) FROM quizzes WHERE subject_id = ?) AS total_quiz_score,
                            (SELECT SUM(total_score) FROM activities WHERE subject_id = ?) AS total_activity_score,
                            (SELECT COUNT(*) FROM class_schedule WHERE subject_id = ?) AS total_scheduled_classes,
                            (SELECT SUM(total_score) FROM exams WHERE subject_id = ?) AS total_exam_score";
                    $stmt = $conn->prepare($query_total_scores);
                    $stmt->bind_param("iiii", $subject_id, $subject_id, $subject_id, $subject_id);
                    $stmt->execute();
                    $totals = $stmt->get_result()->fetch_assoc();

                    $total_quiz_score = $totals['total_quiz_score'] ?: 1;
                    $total_activity_score = $totals['total_activity_score'] ?: 1;
                    $total_scheduled_classes = $totals['total_scheduled_classes'] ?: 1; // Avoid division by zero
                    $total_exam_score = $totals['total_exam_score'] ?: 1;

                    // Fetch students grouped by sections with their scores
                    $query = "
                        SELECT 
                            sections.section_name,
                            students.id AS student_id,
                            student_data.usn,
                            users.full_name,
                            users.email,
                            IFNULL((SELECT SUM(score) FROM quiz_scores WHERE quiz_scores.student_id = students.id AND quiz_id IN (SELECT id FROM quizzes WHERE subject_id = ?)), 0) AS student_quiz_score,
                            IFNULL((SELECT SUM(score) FROM activity_scores WHERE activity_scores.student_id = students.id AND activity_id IN (SELECT id FROM activities WHERE subject_id = ?)), 0) AS student_activity_score,
                            IFNULL((SELECT COUNT(*) FROM attendance WHERE attendance.student_id = students.id AND attendance.subject_id = ? AND status = 'Present'), 0) AS total_attended_classes,
                            IFNULL((SELECT SUM(score) FROM exam_scores WHERE exam_scores.student_id = students.id AND exam_id IN (SELECT id FROM exams WHERE subject_id = ?)), 0) AS student_exam_score
                        FROM students
                        JOIN users ON students.user_id = users.id
                        JOIN student_data ON student_data.student_id = students.id
                        JOIN sections ON students.section_id = sections.id
                        WHERE users.status = 'active'
                        AND students.section_id IN (
                            SELECT section_id FROM section_subject WHERE subject_id = ?
                        )
                        ORDER BY sections.section_name, students.id";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("iiiii", $subject_id, $subject_id, $subject_id, $subject_id, $subject_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $currentSection = null;

                    if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php 
                            // Handle new section grouping
                            if ($currentSection !== $row['section_name']): 
                                if ($currentSection !== null): 
                                    echo '</tbody></table>';
                                endif; 
                                $currentSection = $row['section_name'];
                                echo '<h3 class="text-2xl font-semibold mt-4">' . htmlspecialchars($currentSection) . '</h3>';
                                echo '<table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-4">';
                                echo '<thead>
                                        <tr>
                                            <th class="px-4 py-2 border">ID</th>
                                            <th class="px-4 py-2 border">USN</th>
                                            <th class="px-4 py-2 border">Full Name</th>
                                            <th class="px-4 py-2 border">Email</th>
                                            <th class="px-4 py-2 border">Quiz %</th>
                                            <th class="px-4 py-2 border">Activity %</th>
                                            <th class="px-4 py-2 border">Attendance %</th>
                                            <th class="px-4 py-2 border">Exam %</th>
                                            <th class="px-4 py-2 border">Final Grade</th>
                                        </tr>
                                    </thead>';
                                echo '<tbody>';
                            endif;

                            // Calculate percentages
                            $quiz_percentage = ($row['student_quiz_score'] / $total_quiz_score) * 100;
                            $activity_percentage = ($row['student_activity_score'] / $total_activity_score) * 100;
                            $attendance_percentage = ($row['total_attended_classes'] / $total_scheduled_classes) * 100;
                            $exam_percentage = ($row['student_exam_score'] / $total_exam_score) * 100;

                            // Calculate final grade
                            $final_grade = ($quiz_percentage * $quiz_weight / 100) +
                                        ($activity_percentage * $activity_weight / 100) +
                                        ($attendance_percentage * $attendance_weight / 100) +
                                        ($exam_percentage * $exam_weight / 100);
                            ?>
                            <tr>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['usn']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="px-4 py-2 border"><?php echo number_format($quiz_percentage, 2) . '%'; ?></td>
                                <td class="px-4 py-2 border"><?php echo number_format($activity_percentage, 2) . '%'; ?></td>
                                <td class="px-4 py-2 border"><?php echo number_format($attendance_percentage, 2) . '%'; ?></td>
                                <td class="px-4 py-2 border"><?php echo number_format($exam_percentage, 2) . '%'; ?></td>
                                <td class="px-4 py-2 border"><?php echo number_format($final_grade, 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody></table>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </section>



        <!-- Records Section -->
        <section id="inputRecords" class="content-section hidden">
            <h2 class="text-3xl font-bold mb-4">Input Records</h2>

            <!-- form for adding records -->
            <form id="recordForm" action="../../controllers/recordController.php"  method="post" onsubmit="return submitRecord(this);" >
                <!-- For selecting Subjects  -->
                <div class="mb-4">
                    <label for="subject_id" class="block text-lg font-semibold mb-2">Select Subject:</label>
                    <select id="subject_id" name="subject_id" class="w-full border border-gray-300 p-2 rounded mb-4" required>
                        <?php 
                        $conn = conn();
                        if (isset($conn) && isset($user_id)) {
                            $query = "SELECT s.id, s.subject_name FROM assigned_subjects AS asub 
                                    JOIN subjects AS s ON asub.subject_id = s.id 
                                    WHERE asub.instructor_id = ?";
                            $stmt = $conn->prepare($query);
                            if ($stmt) {
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['subject_name']) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No subjects available for this instructor</option>';
                                }
                                $stmt->close();
                            } else {
                                echo '<option value="">Error preparing statement</option>';
                            }
                        } else {
                            echo '<option value="">Database connection or User ID not established</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Select what to record -->
                <div class="mb-4">
                    <label for="record_type" class="block text-lg font-semibold mb-2">Select Record Type:</label>
                    <select id="record_type" name="record_type" class="w-full border border-gray-300 p-2 rounded mb-4" required>
                        <option value="">-- Select --</option>
                        <option value="quiz">Quiz</option>
                        <option value="activity">Activity</option>
                        <option value="class_schedule">Class Schedule</option>
                        <option value="exam">Exam</option>
                    </select>
                </div>

                <!-- shows if no record being selected  -->
                <div id="dynamicFields" class="mb-4">
                    <p class="text-gray-600">Please select a record type to see the form.</p>
                </div>

                <!-- Subit Record -->
                <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded hidden" id="submitButton">Submit Record</button>
            </form>

            <!-- Shows Existing Recrds -->
            <div id="existingRecords" class="mt-8">
                <?php
                if (isset($conn) && isset($user_id)) {
                    // fetch subjects assin=gned to instructor
                    $query = "SELECT s.id, s.subject_name FROM assigned_subjects AS asub
                            JOIN subjects AS s ON asub.subject_id = s.id
                            WHERE asub.instructor_id = ?";
                    $stmt = $conn->prepare($query);
                    if ($stmt) {
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $subjects_result = $stmt->get_result();

                        if ($subjects_result->num_rows > 0) {
                            $subject_ids = [];
                            while ($row = $subjects_result->fetch_assoc()) {
                                $subject_ids[] = $row['id'];
                            }

                            // Fetch records for each subject (Quiz, Activity, Class Schedule, Exam)
                            $subject_placeholders = implode(',', array_fill(0, count($subject_ids), '?'));
                            $queries = [
                                'quiz' => "SELECT q.quiz_name, q.date, q.total_score, s.subject_name FROM quizzes AS q
                                        JOIN subjects AS s ON q.subject_id = s.id
                                        WHERE q.subject_id IN ($subject_placeholders)",
                                'activity' => "SELECT a.activity_name, a.total_score, s.subject_name FROM activities AS a
                                            JOIN subjects AS s ON a.subject_id = s.id
                                            WHERE a.subject_id IN ($subject_placeholders)",
                                'class_schedule' => "SELECT cs.schedule_date, cs.time_start, cs.time_end, s.subject_name FROM class_schedule AS cs
                                                    JOIN subjects AS s ON cs.subject_id = s.id
                                                    WHERE cs.subject_id IN ($subject_placeholders)",
                                'exam' => "SELECT e.exam_name, e.date, e.total_score, s.subject_name FROM exams AS e
                                        JOIN subjects AS s ON e.subject_id = s.id
                                        WHERE e.subject_id IN ($subject_placeholders)"
                            ];

                            foreach ($queries as $type => $query) {
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param(str_repeat('i', count($subject_ids)), ...$subject_ids);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                echo "<div class='border-2 border-gray-400 p-4 mb-6'>";
                                echo "<h3 class='text-2xl font-bold mb-4'>" . ucfirst(str_replace('_', ' ', $type)) . " Records</h3>";
                                if ($result->num_rows > 0) {
                                    echo "<table class='table-auto w-full border-collapse'>";
                                    echo "<thead><tr><th class='border px-4 py-2'>Subject</th><th class='border px-4 py-2'>Details</th></tr></thead><tbody>";
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr><td class='border px-4 py-2'>" . htmlspecialchars($row['subject_name']) . "</td>";
                                        if ($type == 'quiz' || $type == 'exam') {
                                            echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['quiz_name'] ?? $row['exam_name']) . " - " . htmlspecialchars($row['date']) . " (Total Score: " . htmlspecialchars($row['total_score']) . ")</td></tr>";
                                        } elseif ($type == 'activity') {
                                            echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['activity_name']) . " (Total Score: " . htmlspecialchars($row['total_score']) . ")</td></tr>";
                                        } elseif ($type == 'class_schedule') {
                                            echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['schedule_date']) . " (" . htmlspecialchars($row['time_start']) . " - " . htmlspecialchars($row['time_end']) . ")</td></tr>";
                                        }
                                    }
                                    echo "</tbody></table>";
                                } else {
                                    echo "<p>No records found for this instructor.</p>";
                                }
                                echo "</div>";
                            }
                        } else {
                            echo "<p>No subjects assigned to this instructor.</p>";
                        }
                    } else {
                        echo "<p>Error preparing statement for fetching subjects.</p>";
                    }
                } else {
                    echo "<p>Database connection or user ID not established.</p>";
                }
                ?>
            </div>
        </section>
    </main>
  </div>
<script>
   // Function to submit form data via AJAX
    function submitRecord(form) {
        const formData = new FormData(form); // Gather form data
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message); // Show success message
            } else {
                alert(`Failed: ${data.message}`); // Show error message
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An unexpected error occurred.");
        });
        return false; // Prevent form from submitting normally
    }
    document.addEventListener("DOMContentLoaded", () => {
        const recordTypeElement = document.getElementById("record_type");
        const dynamicFieldsContainer = document.getElementById("dynamicFields");
        const submitButton = document.getElementById("submitButton");

        // Form templates
        const templates = {
            quiz: `
                <label for="quiz_name" class="block text-lg font-semibold mb-2">Quiz Name:</label>
                <input type="text" id="quiz_name" name="quiz_name" class="w-full border border-gray-300 p-2 rounded mb-4">
                <label for="quiz_date" class="block text-lg font-semibold mb-2">Quiz Date:</label>
                <input type="date" id="quiz_date" name="quiz_date" class="w-full border border-gray-300 p-2 rounded mb-4">
                <label for="quiz_score" class="block text-lg font-semibold mb-2">Quiz Total Score:</label>
                <input type="number" id="quiz_score" name="quiz_score" class="w-full border border-gray-300 p-2 rounded mb-4">
            `,
            activity: `
                <label for="activity_name" class="block text-lg font-semibold mb-2">Activity Name:</label>
                <input type="text" id="activity_name" name="activity_name" class="w-full border border-gray-300 p-2 rounded mb-4">
                <label for="activity_score" class="block text-lg font-semibold mb-2">Activity Total Score:</label>
                <input type="number" id="activity_score" name="activity_score" class="w-full border border-gray-300 p-2 rounded mb-4">
            `,
            class_schedule: `
                <label for="schedule_date" class="block text-lg font-semibold mb-2">Schedule Date:</label>
                <input type="date" id="schedule_date" name="schedule_date" class="w-full border border-gray-300 p-2 rounded mb-4">
                <label for="time_start" class="block text-lg font-semibold mb-2">Start Time:</label>
                <input type="time" id="time_start" name="time_start" class="w-full border border-gray-300 p-2 rounded mb-4">
                <label for="time_end" class="block text-lg font-semibold mb-2">End Time:</label>
                <input type="time" id="time_end" name="time_end" class="w-full border border-gray-300 p-2 rounded mb-4">
            `,
            exam: `
                <label for="exam_name" class="block text-lg font-semibold mb-2">Exam Name:</label>
                <input type="text" id="exam_name" name="exam_name" class="w-full border border-gray-300 p-2 rounded mb-4">
                <label for="exam_date" class="block text-lg font-semibold mb-2">Exam Date:</label>
                <input type="date" id="exam_date" name="exam_date" class="w-full border border-gray-300 p-2 rounded mb-4">
                <label for="exam_score" class="block text-lg font-semibold mb-2">Exam Total Score:</label>
                <input type="number" id="exam_score" name="exam_score" class="w-full border border-gray-300 p-2 rounded mb-4">
            `
        };

        // Event listener to toggle fields
        recordTypeElement.addEventListener("change", (e) => {
            const selectedType = e.target.value;
            dynamicFieldsContainer.innerHTML = templates[selectedType] || `<p class="text-gray-600">Please select a valid record type to see the form.</p>`;
            submitButton.classList.toggle("hidden", !templates[selectedType]); // Show/Hide submit button
        });
    });
</script>

</body>
</html>
