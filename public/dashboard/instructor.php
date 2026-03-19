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
  <!-- <script src="main.js" ></script> -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .hidden {
      display: none;
    }
    .active {
      display: block;
    }

    
  </style>
  <script>
    // in manage student section
    function showScoreForm(studentId) {
        const form = document.getElementById('score-form-' + studentId);
        form.classList.toggle('hidden');
    }

        //STUDENT GRADES
        function toggleScores(studentId) {
        const scoresDiv = document.getElementById('scores-' + studentId);
        scoresDiv.classList.toggle('hidden');
    }

    function calculateGrade(studentId) {
        const attendance = parseFloat(document.getElementById('attendance-' + studentId).value) || 0;
        const quiz = parseFloat(document.getElementById('quiz-' + studentId).value) || 0;
        const activities = parseFloat(document.getElementById('activities-' + studentId).value) || 0;
        const exam = parseFloat(document.getElementById('exam-' + studentId).value) || 0;

        const total = attendance * 0.1 + quiz * 0.2 + activities * 0.3 + exam * 0.4;
        const gradePercentage = total.toFixed(2) + '%';

        document.getElementById('grade-' + studentId).innerText = 'Grade: ' + gradePercentage;
    }
    function showSection(sectionId) {
      const sections = document.querySelectorAll('.content-section');
      sections.forEach(section => section.classList.add('hidden'));
      document.getElementById(sectionId).classList.remove('hidden');
    }
    
    function confirmDelete() {
      return confirm("Are you sure you want to delete this?");
    }
  </script>
</head>
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
    // Include the database connection
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
      $image_path = $image_path ? $image_path : '..\dashboard\extras\img1.jpg'; 
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
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('manageQuizActivitiesExams')">Manage Quiz, Activities and Exam</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('showProfile')">Show Profile</a></li>
        <li class="mb-4"><a class="hover:underline" href="studentsRecords.php" onclick="showSection('viewStudentScores')">View Records</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('assignedSubjects')">Assigned Subjects</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('manageAttendance')">Manage Attendance</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('assignSubjectsToSections')">Assign Subjects to Sections</a></li>
      </ul>
    </aside>
    <main class="flex-1 p-8">

      <!-- Assigned Subjects SECTION -->
      <section id="assignedSubjects" class="content-section hidden  ">  
        <h2 class="text-3xl font-bold mb-4">Assigned Subjects</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php
          include_once '../../config/config.php';
          $conn = conn();
          $instructorId = $user_id;

          $query = "SELECT s.id, s.subject_name 
                    FROM assigned_subjects AS asub 
                    JOIN subjects AS s ON asub.subject_id = s.id 
                    WHERE asub.instructor_id = ?";
          $stmt = $conn->prepare($query);
          $stmt->bind_param("i", $instructorId);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  echo '<div class="bg-white p-6 rounded-lg shadow-lg">
                          <h3 class="text-2xl font-semibold mb-2">' . htmlspecialchars($row['subject_name']) . '</h3>
                          <form action="../../controllers/loginController.php" method="post" onsubmit="return confirmDelete()">
                            <input type="hidden" name="assigned_subject_id" value="' . $row['id'] . '">
                            <button type="submit" class="text-red-700 bg-white border px-4 py-2 rounded mt-2">Remove Subject</button>
                          </form>
                        </div>';
              }
          } else {
              echo '<p class="text-gray-500">No subjects assigned yet.</p>';
          }
          ?>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg mt-6">
            <h3 class="text-2xl font-semibold mb-2">Add a New Subject</h3>
            <form action="../../controllers/loginController.php" method="post">
              <label for="subject_id">Available Subjects:</label>
              <select id="subject_id" name="subject_id" class="w-full border border-gray-300 p-2 rounded mb-4" required>
                <?php
                // Fetch all subjects not yet assigned to this instructor
                $query = "SELECT id, subject_name 
                          FROM subjects 
                          WHERE id NOT IN (
                              SELECT subject_id 
                              FROM assigned_subjects
                          )";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['subject_name']) . '</option>';
                }

                ?>
              </select>
              <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded">Add Subject</button>
            </form>
          </div>
      </section>

      <!-- Display Instructor Information -->
        <section id="showProfile" class="content-section hidden">
            <h2 style="font-family: Arial, sans-serif; color: #333;">Instructor Profile</h2>
            <div id="instructorDetails" style="padding: 20px; background-color: #f9f9f9; border: 1px solid #555; border-radius: 5px;">
                <!-- instructor details will show here -->
            </div>

            <h3 style="font-family: Arial, sans-serif; color: #555; margin-top: 20px;">Update Profile</h3>
            <form id="updateInstructorForm" method="POST" action="update_instructor.php" style="padding: 20px; background-color: #fff; border: 0.2px solid #555 ; border-radius: 5px;">
                    <label for="username" style="font-family: Arial, sans-serif; color: #333; display: block; margin-bottom: 5px;">Username:</label>
                    <input type="text" id="username" name="username" style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid gray ; border-radius: 4px;">

                    <label for="email" style="font-family: Arial, sans-serif; color: #333; display: block; margin-bottom: 5px;">Email:</label>
                    <input type="email" id="email" name="email" style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid gray ; border-radius: 4px;">

                    <label for="password" style="font-family: Arial, sans-serif; color: #333; display: block; margin-bottom: 5px;">New Password:</label>
                    <input type="password" id="password" name="password" style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid gray ; border-radius: 4px;">

                    <button type="submit" style="background-color:rgb(23, 174, 28); color: white; font-family: Arial, sans-serif; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Update</button>
                </form>
        </section>




     <!-- Manage Students Quiz, Activities and Exam SECTION -->
    <section id="manageQuizActivitiesExams" class="content-section">
        <h2 class="text-3xl font-bold mb-4">Manage Quizzes, Activities, and Exams</h2>

        <!-- Dropdown to select a subject assigned to the instructor -->
        <form method="POST" action="">
            <label for="subject_id">Select Subject:</label>
            <select name="subject_id" id="subject_id" onchange="this.form.submit()">
                <option value="">-- Choose Subject --</option>
                <?php 
                $conn = conn();

                $query = "
                    SELECT DISTINCT subjects.id, subjects.subject_name
                    FROM subjects
                    JOIN section_subject ON subjects.id = section_subject.subject_id
                    JOIN sections ON section_subject.section_id = sections.id
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
            <!-- Radio buttons to choose between quizzes, activities, and exams -->
            <form method="POST" action="">
                <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($_POST['subject_id']); ?>">
                <label>
                    <input type="radio" name="type" value="quiz" 
                        <?php echo (isset($_POST['type']) && $_POST['type'] == 'quiz') ? 'checked' : ''; ?>
                        onchange="this.form.submit()"> Manage Quizzes
                </label>
                <label>
                    <input type="radio" name="type" value="activity" 
                        <?php echo (isset($_POST['type']) && $_POST['type'] == 'activity') ? 'checked' : ''; ?>
                        onchange="this.form.submit()"> Manage Activities
                </label>
                <label>
                    <input type="radio" name="type" value="exam" 
                        <?php echo (isset($_POST['type']) && $_POST['type'] == 'exam') ? 'checked' : ''; ?>
                        onchange="this.form.submit()"> Manage Exams
                </label>
            </form>

            <?php if (isset($_POST['type']) && $_POST['type'] == 'quiz'): ?>
                <!-- Dropdown to select a quiz associated with the selected subject -->
                <form method="POST" action="">
                    <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($_POST['subject_id']); ?>">
                    <input type="hidden" name="type" value="quiz">
                    <label for="quiz_id">Select Quiz:</label>
                    <select name="quiz_id" id="quiz_id" onchange="this.form.submit()">
                        <option value="">-- Choose Quiz --</option>
                        <?php 
                        $query = "SELECT id, quiz_name FROM quizzes WHERE subject_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $_POST['subject_id']);
                        $stmt->execute();
                        $quizResult = $stmt->get_result();

                        while ($quizRow = $quizResult->fetch_assoc()) {
                            $selected = isset($_POST['quiz_id']) && $_POST['quiz_id'] == $quizRow['id'] ? 'selected' : '';
                            echo '<option value="' . $quizRow['id'] . '" ' . $selected . '>' . htmlspecialchars($quizRow['quiz_name']) . '</option>';
                        }
                        ?>
                    </select>
                </form>
            <?php elseif (isset($_POST['type']) && $_POST['type'] == 'activity'): ?>
                <!-- Dropdown to select an activity associated with the selected subject -->
                <form method="POST" action="">
                    <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($_POST['subject_id']); ?>">
                    <input type="hidden" name="type" value="activity">
                    <label for="activity_id">Select Activity:</label>
                    <select name="activity_id" id="activity_id" onchange="this.form.submit()">
                        <option value="">-- Choose Activity --</option>
                        <?php 
                        $query = "SELECT id, activity_name FROM activities WHERE subject_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $_POST['subject_id']);
                        $stmt->execute();
                        $activityResult = $stmt->get_result();

                        while ($activityRow = $activityResult->fetch_assoc()) {
                            $selected = isset($_POST['activity_id']) && $_POST['activity_id'] == $activityRow['id'] ? 'selected' : '';
                            echo '<option value="' . $activityRow['id'] . '" ' . $selected . '>' . htmlspecialchars($activityRow['activity_name']) . '</option>';
                        }
                        ?>
                    </select>
                </form>
            <?php elseif (isset($_POST['type']) && $_POST['type'] == 'exam'): ?>
                <!-- Dropdown to select an exam associated with the selected subject -->
                <form method="POST" action="">
                    <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($_POST['subject_id']); ?>">
                    <input type="hidden" name="type" value="exam">
                    <label for="exam_id">Select Exam:</label>
                    <select name="exam_id" id="exam_id" onchange="this.form.submit()">
                        <option value="">-- Choose Exam --</option>
                        <?php 
                        $query = "SELECT id, exam_name FROM exams WHERE subject_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $_POST['subject_id']);
                        $stmt->execute();
                        $examResult = $stmt->get_result();

                        while ($examRow = $examResult->fetch_assoc()) {
                            $selected = isset($_POST['exam_id']) && $_POST['exam_id'] == $examRow['id'] ? 'selected' : '';
                            echo '<option value="' . $examRow['id'] . '" ' . $selected . '>' . htmlspecialchars($examRow['exam_name']) . '</option>';
                        }
                        ?>
                    </select>
                </form>
            <?php endif; ?>

            <!-- Table to display students based on the selection -->
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-4">
                <tbody>
                <?php 
                    if ((isset($_POST['type']) && $_POST['type'] == 'quiz' && isset($_POST['quiz_id']) && $_POST['quiz_id'] != '') ||
                        (isset($_POST['type']) && $_POST['type'] == 'activity' && isset($_POST['activity_id']) && $_POST['activity_id'] != '') ||
                        (isset($_POST['type']) && $_POST['type'] == 'exam' && isset($_POST['exam_id']) && $_POST['exam_id'] != '')) {
                        $id = $_POST['type'] == 'quiz' ? $_POST['quiz_id'] : ($_POST['type'] == 'activity' ? $_POST['activity_id'] : $_POST['exam_id']);
                        $type = $_POST['type'];

                        // Query to fetch students grouped by section
                        $query = "
                            SELECT 
                                sections.id AS section_id,
                                sections.section_name,
                                students.id AS student_id, 
                                student_data.usn, 
                                users.full_name, 
                                users.email 
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
                        $stmt->bind_param("i", $_POST['subject_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $currentSection = null; // Track the current section for grouping

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                if ($currentSection !== $row['section_name']) {
                                    // Close previous section form
                                    if ($currentSection !== null) {
                                        echo '</tbody></table>';
                                        echo '<button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Save All for ' . htmlspecialchars($currentSection) . '</button>';
                                        echo '</form>';
                                    }

                                    $currentSection = $row['section_name'];
                                    $sectionId = $row['section_id']; // Save current section ID for the form

                                    // Open a new form for the section
                                    echo '<form method="POST" action="saveScores.php">';
                                    echo '<input type="hidden" name="section_id" value="' . htmlspecialchars($sectionId) . '">';
                                    echo '<input type="hidden" name="type_id" value="' . htmlspecialchars($id) . '">';
                                    echo '<input type="hidden" name="type" value="' . htmlspecialchars($type) . '">';

                                    echo '<table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-4">';
                                    echo '<thead><tr class="bg-gray-100"><th colspan="6" class="px-4 py-2 border">' . htmlspecialchars($currentSection) . '</th></tr></thead>';
                                    echo '<thead>
                                            <tr>
                                                <th class="px-4 py-2 border">ID</th>
                                                <th class="px-4 py-2 border">USN</th>
                                                <th class="px-4 py-2 border">Full Name</th>
                                                <th class="px-4 py-2 border">Email</th>
                                                <th class="px-4 py-2 border">Score</th>
                                                <th class="px-4 py-2 border">Existing Score</th>
                                            </tr>
                                        </thead>';
                                    echo '<tbody>';
                                }

                                // Fetch existing score for the student based on the type
                                if ($type == 'quiz') {
                                    $existingScoreQuery = "
                                        SELECT score FROM quiz_scores 
                                        WHERE student_id = ? AND quiz_id = ?";
                                } elseif ($type == 'activity') {
                                    $existingScoreQuery = "
                                        SELECT score FROM activity_scores 
                                        WHERE student_id = ? AND activity_id = ?";
                                } else { // exam
                                    $existingScoreQuery = "
                                        SELECT score FROM exam_scores 
                                        WHERE student_id = ? AND exam_id = ?";
                                }

                                $existingScoreStmt = $conn->prepare($existingScoreQuery);
                                $existingScoreStmt->bind_param("ii", $row['student_id'], $id);
                                $existingScoreStmt->execute();
                                $existingScoreResult = $existingScoreStmt->get_result();
                                $existingScore = $existingScoreResult->num_rows > 0 ? $existingScoreResult->fetch_assoc()['score'] : null;

                                // Display student row
                                echo '<tr>
                                        <td class="px-4 py-2 border">' . htmlspecialchars($row['student_id']) . '</td>
                                        <td class="px-4 py-2 border">' . htmlspecialchars($row['usn']) . '</td>
                                        <td class="px-4 py-2 border">' . htmlspecialchars($row['full_name']) . '</td>
                                        <td class="px-4 py-2 border">' . htmlspecialchars($row['email']) . '</td>
                                        <td class="px-4 py-2 border">
                                            <input type="number" name="scores[' . htmlspecialchars($row['student_id']) . ']" placeholder="Enter score" class="border px-2 py-1 rounded">
                                        </td>
                                        <td class="px-4 py-2 border">' . ($existingScore !== null ? htmlspecialchars($existingScore) : 'N/A') . '</td>
                                    </tr>';
                            }

                            // Close the last section form
                            echo '</tbody></table>';
                            echo '<button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Save All for ' . htmlspecialchars($currentSection) . '</button>';
                            echo '</form>';
                        } else {
                            echo '<tr><td colspan="6" class="px-4 py-2 border text-center">No students found.</td></tr>';
                        }
                    }
                ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>


       <!-- Manage attendance for students  -->
       <section id="manageAttendance" class="content-section hidden ">
            <h2 class="text-3xl font-bold mb-4">Manage Attendance</h2>

                <!-- Dropdown to select a subject assigned to the instructor -->
                <form method="POST" action="">
                    <input type="hidden" name="section_type" value="attendance">
                    <label for="subject_id">Select Subject:</label>
                    <select name="subject_id" id="subject_id" onchange="this.form.submit()" required>
                        <option value="">-- Choose Subject --</option>
                        <?php
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
                            $selected = isset($_POST['subject_id']) && $_POST['section_type'] === 'attendance' && $_POST['subject_id'] == $subjectRow['id'] ? 'selected' : '';
                            echo '<option value="' . $subjectRow['id'] . '" ' . $selected . '>' . htmlspecialchars($subjectRow['subject_name']) . '</option>';
                        }
                        ?>
                    </select>
                </form>

            <?php if (isset($_POST['section_type']) && $_POST['section_type'] === 'attendance' && isset($_POST['subject_id']) && $_POST['subject_id'] != ''): ?>
                <!-- Dropdown to select a class schedule -->
                <form method="POST" action="">
                    <input type="hidden" name="section_type" value="attendance">
                    <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($_POST['subject_id']); ?>">
                    <label for="class_schedule">Select Class Schedule:</label>
                    <select name="class_schedule" id="class_schedule" onchange="this.form.submit()" required>
                        <option value="">-- Choose Schedule --</option>
                        <?php
                        $query = "
                            SELECT id, DATE_FORMAT(schedule_date, '%Y-%m-%d') AS formatted_date, TIME_FORMAT(time_start, '%H:%i') AS start_time, TIME_FORMAT(time_end, '%H:%i') AS end_time
                            FROM class_schedule
                            WHERE subject_id = ?
                            ORDER BY schedule_date DESC, time_start";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $_POST['subject_id']);
                        $stmt->execute();
                        $scheduleResult = $stmt->get_result();

                        while ($scheduleRow = $scheduleResult->fetch_assoc()) {
                            $scheduleDisplay = htmlspecialchars($scheduleRow['formatted_date'] . ' (' . $scheduleRow['start_time'] . ' - ' . $scheduleRow['end_time'] . ')');
                            $selected = isset($_POST['class_schedule']) && $_POST['class_schedule'] == $scheduleRow['id'] ? 'selected' : '';
                            echo '<option value="' . $scheduleRow['id'] . '" ' . $selected . '>' . $scheduleDisplay . '</option>';
                        }
                        ?>
                    </select>
                </form>

                <?php if (isset($_POST['class_schedule']) && $_POST['class_schedule'] != ''): ?>
                    <!-- Display students connected to the subject, grouped by sections -->
                    <?php
                    $class_schedule_id = $_POST['class_schedule'];
                    $subject_id = $_POST['subject_id'];

                   // Fetch the selected schedule's date
                    $query = "SELECT schedule_date FROM class_schedule WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $class_schedule_id);
                    $stmt->execute();
                    $scheduleDateResult = $stmt->get_result();
                    $scheduleDateRow = $scheduleDateResult->fetch_assoc();
                    $attendance_date = $scheduleDateRow['schedule_date'];

                    $query = "
                        SELECT 
                            sections.section_name,
                            students.id AS student_id,
                            student_data.usn,
                            users.full_name,
                            users.email,
                            attendance.status
                        FROM students
                        JOIN users ON students.user_id = users.id
                        JOIN student_data ON student_data.student_id = students.id
                        JOIN sections ON students.section_id = sections.id
                        LEFT JOIN attendance ON attendance.student_id = students.id
                            AND attendance.subject_id = ?
                            AND attendance.class_schedule_id = ?
                        WHERE users.status = 'active' -- Only fetch students with active status
                        AND students.section_id IN (
                            SELECT section_id FROM section_subject WHERE subject_id = ?
                        )
                        ORDER BY sections.section_name, students.id";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("iii", $subject_id, $class_schedule_id, $subject_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $currentSection = null;

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            if ($currentSection !== $row['section_name']) {
                                // Close previous section table
                                if ($currentSection !== null) {
                                    echo '</tbody></table>';
                                    echo '<button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Save Attendance for ' . htmlspecialchars($currentSection) . '</button>';
                                    echo '</form>';
                                }

                                $currentSection = $row['section_name'];
                                echo '<h3 class="text-2xl font-semibold mt-4">' . htmlspecialchars($currentSection) . '</h3>';
                                echo '<form method="POST" action="saveAttendance.php">';
                                echo '<input type="hidden" name="subject_id" value="' . htmlspecialchars($subject_id) . '">';
                                echo '<input type="hidden" name="attendance_date" value="' . htmlspecialchars($attendance_date) . '">';
                                echo '<input type="hidden" name="class_schedule_id" value="' . htmlspecialchars($class_schedule_id) . '">';
                                echo '<table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-4">';
                                echo '<thead>
                                        <tr>
                                            <th class="px-4 py-2 border">USN</th>
                                            <th class="px-4 py-2 border">Full Name</th>
                                            <th class="px-4 py-2 border">Status</th>
                                        </tr>
                                    </thead>';
                                echo '<tbody>';
                            }

                            // Display student row with status options
                            $statusOptions = ['Present', 'Absent', 'Late', 'Excused'];
                            echo '<tr>
                                    <td class="px-4 py-2 border">' . htmlspecialchars($row['usn']) . '</td>
                                    <td class="px-4 py-2 border">' . htmlspecialchars($row['full_name']) . '</td>
                                    <td class="px-4 py-2 border">
                                        <select name="statuses[' . htmlspecialchars($row['student_id']) . ']" class="border px-2 py-1 rounded">
                                            <option value="">-- Choose Status --</option>';

                            // Loop through status options
                            foreach ($statusOptions as $status) {
                                $selected = $row['status'] === $status ? 'selected' : '';
                                $disabled = $row['status'] === $status ? 'disabled' : '';
                                echo '<option value="' . $status . '" ' . $selected . ' ' . $disabled . '>' . $status . '</option>';
                            }

                            echo '</select>
                                    </td>
                                </tr>';
                        }
                        echo '</tbody></table>';
                        echo '<button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Save Attendance for ' . htmlspecialchars($currentSection) . '</button>';
                        echo '</form>';
                    } else {
                        echo '<p>No students or attendance records available for this schedule.</p>';
                    }
                    ?>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <!-- Assign Subjects to Sections SECTION -->
        <section id="assignSubjectsToSections" class="content-section hidden">
          <h2 class="text-3xl font-bold mb-4">Assign Subjects to Sections</h2>
          <div class="bg-white p-6 rounded-lg shadow-lg">
              <form action="../../controllers/assignSubController.php" method="post">
                  <div class="form-group">
                      <label for="section_id" class="block text-lg font-semibold mb-2">Select Section:</label>
                      <select id="section_id" name="section_id" class="w-full border border-gray-300 p-2 rounded mb-4" required>
                          <?php
                          $query = "SELECT id, section_name FROM sections";
                          $result = $conn->query($query);
                          while ($row = $result->fetch_assoc()) {
                              echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['section_name']) . '</option>';
                          }
                          ?>
                      </select>
                  </div>
                  <div class="form-group">
                      <label for="subject_id" class="block text-lg font-semibold mb-2">Select Subject:</label>
                      <select id="subject_id" name="subject_id" class="w-full border border-gray-300 p-2 rounded mb-4" required>
                          <?php
                          // Fetch only the subjects assigned to the current instructor
                          $query = "
                              SELECT s.id, s.subject_name 
                              FROM assigned_subjects AS asub 
                              JOIN subjects AS s ON asub.subject_id = s.id 
                              WHERE asub.instructor_id = ?";
                          $stmt = $conn->prepare($query);
                          $stmt->bind_param("i", $user_id);
                          $stmt->execute();
                          $result = $stmt->get_result();

                          while ($row = $result->fetch_assoc()) {
                              echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['subject_name']) . '</option>';
                          }

                          $stmt->close();
                          ?>
                      </select>
                  </div>
                  <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded" name="assignSubjectToSectionBtn">
                      Assign Subject
                  </button>
              </form>
          </div>
        </section>

   
      
    </main>
  </div> 

  <script>    
// fetch instructor details
function fetchInstructorDetails() {
  fetch('fetch_instructor.php')
    .then(response => response.json())
    .then(data => {
      if (!data.error) {
        const details = `
          <p><strong>Username:</strong> ${data.username}</p>
          <p><strong>Email:</strong> ${data.email}</p>
          <p><strong>Full Name:</strong> ${data.full_name}</p>
        `;
        document.getElementById('instructorDetails').innerHTML = details;
      } else {
        alert(data.error);
      }
    })
    .catch(error => console.error('Error:', error));
}

// Handle the profile update form submission
document.getElementById('updateInstructorForm').addEventListener('submit', function (e) {
  e.preventDefault(); // Prevent default form submission

  const formData = new FormData(this);

  // Remove empty fields from the form data
  for (let [key, value] of formData.entries()) {
    if (!value.trim()) {
      formData.delete(key);
    }
  }

  fetch('update_instructor.php', {
    method: 'POST',
    body: formData,
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(data.success);
        fetchInstructorDetails(); // Refresh the displayed profile details
      } else if (data.error) {
        alert(data.error);
      }
    })
    .catch(error => console.error('Error:', error));
});

// Initialize by fetching the instructor's details
document.addEventListener('DOMContentLoaded', fetchInstructorDetails);

  </script>
</body>

</html>
