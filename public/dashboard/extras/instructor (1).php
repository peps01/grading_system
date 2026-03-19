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

    // Assuming you have a form with id 'addCourseForm'
    document.getElementById('addCourseForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        const formData = new FormData(this); // Create a FormData object from the form

        fetch('../../controllers/courseController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message); // Show success message
                // Optionally, fetch the updated list of courses
                fetchCourses(); // Call a function to fetch and display courses
            } else {
                alert('Error: ' + data.message); // Show error message
            }
        })  
        .catch(error => {
            console.error('Error:', error);
        });
    });

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
      $user_id = $_SESSION['user_id']; // Example, replace with the actual method you're using to get the user ID

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
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('manageQuizActivitiesExams')">Manage Quiz, Activities and Exam</a></li>
        <li class="mb-4"><a class="hover:underline" href="studentsRecords.php" onclick="showSection('viewStudentScores')">View Students Records</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('assignedSubjects')">Assigned Subjects</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('manageAttendance')">Manage Attendance</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('assignSubjectsToSections')">Assign Subjects to Sections</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('manageCourses')">Manage Courses</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('inputRecords')">Records</a></li>
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

      <!-- Manage Students Quiz, Activities and Exam  SECITON -->
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
                                        echo '<thead><tr class="bg-gray-100"><th colspan="5" class="px-4 py-2 border">' . htmlspecialchars($currentSection) . '</th></tr></thead>';
                                        echo '<thead>
                                                <tr>
                                                    <th class="px-4 py-2 border">ID</th>
                                                    <th class="px-4 py-2 border">USN</th>
                                                    <th class="px-4 py-2 border">Full Name</th>
                                                    <th class="px-4 py-2 border">Email</th>
                                                    <th class="px-4 py-2 border">Score</th>
                                                </tr>
                                            </thead>';
                                        echo '<tbody>';
                                    }

                                    // Display student row
                                    echo '<tr>
                                            <td class="px-4 py-2 border">' . htmlspecialchars($row['student_id']) . '</td>
                                            <td class="px-4 py-2 border">' . htmlspecialchars($row['usn']) . '</td>
                                            <td class="px-4 py-2 border">' . htmlspecialchars($row['full_name']) . '</td>
                                            <td class="px-4 py-2 border">' . htmlspecialchars($row['email']) . '</td>
                                            <td class="px-4 py-2 border">
                                                <input type="number" name="scores[' . htmlspecialchars($row['student_id']) . ']" placeholder="Enter score" class="border px-2 py-1 rounded">
                                            </td>
                                        </tr>';
                                }

                                // Close the last section form
                                echo '</tbody></table>';
                                echo '<button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mt-4">Save All for ' . htmlspecialchars($currentSection) . '</button>';
                                echo '</form>';
                            } else {
                                echo '<tr><td colspan="5" class="px-4 py-2 border text-center">No students found.</td></tr>';
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
                <!-- Dropdown to select a class schedule date -->
                <form method="POST" action="">
                    <input type="hidden" name="section_type" value="attendance">
                    <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($_POST['subject_id']); ?>">
                    <label for="class_schedule_id">Select Schedule:</label>
                    <select name="class_schedule_id" id="class_schedule_id" onchange="this.form.submit()" required>
                        <option value="">-- Choose Schedule --</option>
                        <?php
                        
                        $query = "
                            SELECT id, schedule_date, time_start, time_end
                            FROM class_schedule
                            WHERE subject_id = ?
                            ORDER BY schedule_date DESC";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $_POST['subject_id']);
                        $stmt->execute();
                        $scheduleResult = $stmt->get_result();

                        while ($scheduleRow = $scheduleResult->fetch_assoc()) {
                            $selected = isset($_POST['class_schedule_id']) && $_POST['class_schedule_id'] == $scheduleRow['id'] ? 'selected' : '';
                            echo '<option value="' . $scheduleRow['id'] . '" ' . $selected . '>' . htmlspecialchars($scheduleRow['schedule_date']) . ' (' . htmlspecialchars($scheduleRow['time_start']) . ' - ' . htmlspecialchars($scheduleRow['time_end']) . ')</option>';
                        }
                        ?>
                    </select>
                </form>

                <?php if (isset($_POST['class_schedule_id']) && $_POST['class_schedule_id'] != ''): ?>
                    <!-- Display students connected to the subject and schedule -->
                    <?php
                    $class_schedule_id = $_POST['class_schedule_id'];
                    $subject_id = $_POST['subject_id'];

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
                        WHERE students.section_id IN (
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
      

        <!-- Manage Courses SECTION -->
        <section id="manageCourses" class="content-section hidden">
            <h2 class="text-3xl font-bold mb-4">Manage Courses</h2>
            <!-- Form for adding a new course -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-2xl font-semibold mb-4">Add a New Course</h3>
                <form action="../../controllers/courseController.php" method="post">
                    <input type="text" name="course_name" class="w-full border border-gray-300 p-2 rounded mb-4" placeholder="Enter course name" required>
                    <input type="hidden" name="instructor_id" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>"> <!-- Dynamic instructor ID -->
                    <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded">Add Course</button>
                </form>
            </div>

            <!-- Table displaying courses -->
            <div class="mt-6">
                <h3 class="text-2xl font-semibold mb-4">Courses List</h3>
                <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border">Course Name</th>
                            <th class="px-4 py-2 border">Subjects</th>
                            <th class="px-4 py-2 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        include_once '../../config/config.php';
                        
                        if (!isset($_SESSION['user_id'])) {
                            echo "<tr><td colspan='3' class='px-4 py-2 border'>Please log in to view your courses.</td></tr>";
                        } else {
                            $instructorId = $_SESSION['user_id'];

                            $query = "SELECT c.id AS course_id, c.course_name, s.subject_name
                                    FROM courses c
                                    LEFT JOIN course_subject cs ON c.id = cs.course_id
                                    LEFT JOIN subjects s ON cs.subject_id = s.id
                                    WHERE c.instructor_id = ?";

                            $stmt = $conn->prepare($query);
                            $stmt->bind_param('i', $instructorId);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            $courses = [];

                            // Group subjects under their respective courses
                            while ($row = $result->fetch_assoc()) {
                                $courses[$row['course_id']]['course_name'] = $row['course_name'];
                                $courses[$row['course_id']]['subjects'][] = $row['subject_name'];
                            }

                            if (!empty($courses)) {
                                foreach ($courses as $courseId => $course) {
                                    echo "<tr>
                                            <td class='px-4 py-2 border'>" . htmlspecialchars($course['course_name']) . "</td>
                                            <td class='px-4 py-2 border'>" . (count($course['subjects']) > 0 ? implode(", ", $course['subjects']) : 'No subjects assigned') . "</td>
                                            <td class='px-4 py-2 border'>
                                                <button class='bg-blue-600 text-white px-2 py-1 rounded' onclick='openModal($courseId)'>Add Subject</button>
                                                <form action='delete_course.php' method='post' class='inline' onsubmit='return confirmDelete();'>
                                                    <input type='hidden' name='course_id' value='$courseId'>
                                                    <button type='submit' class='bg-red-600 text-white px-2 py-1 rounded'>Delete Course</button>
                                                </form>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='px-4 py-2 border'>No courses found.</td></tr>";
                            }

                            $stmt->close();
                            $conn->close();
                        }
                    ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal for Adding Subject -->
            <div id="addSubjectModal" class="modal hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <span class="close cursor-pointer" onclick="closeModal()">&times;</span>
                    <h2 class="text-2xl font-semibold mb-4">Add Subject</h2>
                    <form id="addSubjectForm" action="insert_subject.php" method="POST">
                        <input type="hidden" name="course_id" id="modalCourseId" value="">
                        <label for="subject_name" class="block mb-2">Subject Name:</label>
                        <input type="text" id="subject_name" name="subject_name" class="w-full border border-gray-300 p-2 rounded mb-4" required>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Add Subject</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Input Records Section -->
        <section id="inputRecords" class="content-section hidden">
            <h2 class="text-3xl font-bold mb-4">Input Records</h2>
            <form action="../../controllers/recordController.php" method="post">
                <!-- Subject Selection -->
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

                <!-- Record Type Selection -->
                <div class="mb-4">
                    <label for="record_type" class="block text-lg font-semibold mb-2">Select Record Type:</label>
                    <select id="record_type" name="record_type" class="w-full border border-gray-300 p-2 rounded mb-4" required>
                        <option value="quiz">Quiz</option>
                        <option value="activity">Activity</option>
                        <option value="attendance">Attendance</option>
                        <option value="exam">Exam</option>
                    </select>
                </div>

                <!-- Quiz Fields -->
                <div id="quiz_fields" class="hidden">
                    <label for="quiz_name" class="block text-lg font-semibold mb-2">Quiz Name:</label>
                    <input type="text" id="quiz_name" name="quiz_name" class="w-full border border-gray-300 p-2 rounded mb-4">
                    <label for="quiz_date" class="block text-lg font-semibold mb-2">Quiz Date:</label>
                    <input type="date" id="quiz_date" name="quiz_date" class="w-full border border-gray-300 p-2 rounded mb-4">
                    <label for="quiz_score" class="block text-lg font-semibold mb-2">Quiz Total Score:</label>
                    <input type="number" id="quiz_score" name="quiz_score" class="w-full border border-gray-300 p-2 rounded mb-4">
                </div>

                <!-- Activity Fields -->
                <div id="activity_fields" class="hidden">
                    <label for="activity_name" class="block text-lg font-semibold mb-2">Activity Name:</label>
                    <input type="text" id="activity_name" name="activity_name" class="w-full border border-gray-300 p-2 rounded mb-4">
                    <label for="activity_score" class="block text-lg font-semibold mb-2">Activity Total Score:</label>
                    <input type="number" id="activity_score" name="activity_score" class="w-full border border-gray-300 p-2 rounded mb-4">
                </div>

                <!-- Attendance Fields -->
                <div id="attendance_fields">
                    <label for="schedule_date">Schedule Date:</label>
                    <input type="date" name="schedule_date" id="schedule_date" required>
                    <label for="time_start">Start Time:</label>
                    <input type="time" name="time_start" id="time_start" required>
                    <label for="time_end">End Time:</label>
                    <input type="time" name="time_end" id="time_end" required>
                </div>

                <!-- Exam Fields -->
                <div id="exam_fields" class="hidden">
                    <label for="exam_name" class="block text-lg font-semibold mb-2">Exam Name:</label>
                    <input type="text" id="exam_name" name="exam_name" class="w-full border border-gray-300 p-2 rounded mb-4">
                    <label for="exam_date" class="block text-lg font-semibold mb-2">Exam Date:</label>
                    <input type="date" id="exam_date" name="exam_date" class="w-full border border-gray-300 p-2 rounded mb-4">
                    <label for="exam_score" class="block text-lg font-semibold mb-2">Exam Total Score:</label>
                    <input type="number" id="exam_score" name="exam_score" class="w-full border border-gray-300 p-2 rounded mb-4">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded">Submit Record</button>
            </form>
            <br><br>
                <?php
                // Database connection
                $conn = conn();
                if (isset($conn) && isset($user_id)) {
                    // Fetch subjects assigned to the instructor
                    $query = "SELECT s.id, s.subject_name FROM assigned_subjects AS asub
                            JOIN subjects AS s ON asub.subject_id = s.id
                            WHERE asub.instructor_id = ?";
                    $stmt = $conn->prepare($query);
                    if ($stmt) {
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $subjects_result = $stmt->get_result();
                        
                        // Check if subjects are available
                        if ($subjects_result->num_rows > 0) {
                            $subject_ids = [];
                            while ($row = $subjects_result->fetch_assoc()) {
                                $subject_ids[] = $row['id']; // Store the subject ids
                            }

                            // Fetch records for each subject (Quiz, Activity, Attendance, Exam)
                            $subject_placeholders = implode(',', array_fill(0, count($subject_ids), '?'));
                            
                            // Queries for quizzes, activities, attendance, and exams
                            $queries = [
                                'quiz' => "SELECT q.quiz_name, q.date, q.total_score, s.subject_name FROM quizzes AS q
                                        JOIN subjects AS s ON q.subject_id = s.id
                                        WHERE q.subject_id IN ($subject_placeholders)",
                                'activity' => "SELECT a.activity_name, a.total_score, s.subject_name FROM activities AS a
                                            JOIN subjects AS s ON a.subject_id = s.id
                                            WHERE a.subject_id IN ($subject_placeholders)",
                                'attendance' => "SELECT at.date, at.status, s.subject_name FROM attendance AS at
                                                JOIN subjects AS s ON at.subject_id = s.id
                                                WHERE at.subject_id IN ($subject_placeholders)",
                                'exam' => "SELECT e.exam_name, e.date, e.total_score, s.subject_name FROM exams AS e
                                        JOIN subjects AS s ON e.subject_id = s.id
                                        WHERE e.subject_id IN ($subject_placeholders)"
                            ];

                            // Prepare and execute queries for each type
                            foreach ($queries as $type => $query) {
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param(str_repeat('i', count($subject_ids)), ...$subject_ids);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                // Display the records based on the type (Quiz, Activity, Attendance, Exam)
                                echo "<div class='border-2 border-gray-400 p-4 mb-6'>";
                                echo "<h3 class='text-2xl font-bold mb-4'>" . ucfirst($type) . " Records</h3>";
                                if ($result->num_rows > 0) {
                                    echo "<table class='table-auto w-full border-collapse'>";
                                    echo "<thead><tr><th class='border px-4 py-2'>Subject</th><th class='border px-4 py-2'>Details</th></tr></thead><tbody>";
                                    while ($row = $result->fetch_assoc()) {
                                        // Dynamically show details based on type
                                        echo "<tr><td class='border px-4 py-2'>" . htmlspecialchars($row['subject_name']) . "</td>";
                                        if ($type == 'quiz' || $type == 'exam') {
                                            echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['quiz_name'] ?? $row['exam_name']) . " - " . htmlspecialchars($row['date']) . " (Total Score: " . htmlspecialchars($row['total_score']) . ")</td></tr>";
                                        } elseif ($type == 'activity') {
                                            echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['activity_name']) . " (Total Score: " . htmlspecialchars($row['total_score']) . ")</td></tr>";
                                        } elseif ($type == 'attendance') {
                                            echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['date']) . " - " . htmlspecialchars($row['status']) . "</td></tr>";
                                        }
                                    }
                                    echo "</tbody></table>";
                                } else {
                                    echo "<p>No records found for this instructor.</p>";
                                }
                                $stmt->close();
                                echo "</div>"; // Closing the container for each type
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
        </section>
      
      

    </main>
  </div>
  
  <script>    
    // Function to fetch and display courses
    function fetchCourses() {
        const instructorId = /* Get the instructor ID from session or other source */;
        
        fetch('../../controllers/courseController.php?instructor_id=' + instructorId)
        .then(response => response.json())
        .then(courses => {
            // Code to update the courses list in the UI
            console.log(courses); // For debugging
        })
        .catch(error => {
            console.error('Error fetching courses:', error);
        });
    }


     // Open modal and set course ID dynamically
     function openModal(courseId) {
        document.getElementById('modalCourseId').value = courseId;
        document.getElementById('addSubjectModal').classList.remove('hidden');
    }

    // Close modal
    function closeModal() {
        document.getElementById('addSubjectModal').classList.add('hidden');
    }

    // Confirm delete action
    function confirmDelete() {
        return confirm("Are you sure you want to delete this course?");
    }

    
  </script> 
</body>
</html>
