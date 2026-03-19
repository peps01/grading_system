<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_name'])) {
    header("Location: /grading-system/index.html");
    exit();
}

// Check if the user is Super Admin or Admin
if ($_SESSION['role_name'] !== 'Super Admin' && $_SESSION['role_name'] !== 'Admin') {
    header("Location: /grading-system/index.html");
    exit();
}

// Retrieve session variables
$role_name = $_SESSION['role_name'];
$role_id = $_SESSION['role_id'];
$full_name = $_SESSION['full_name'];
$user_id = $_SESSION['user_id'];

if (isset($_GET['logout'])) {
    session_unset(); 
    session_destroy(); 

    header("Location: /grading-system/index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- <link rel="stylesheet" href=""> -->
  <link rel="stylesheet" href="../assets/css/admin.css">

  <style>
    .hidden {
      display: none;
    }
    .active {
      display: block;
    }
  </style>
  <script>
    function showSection(sectionId) {
      const sections = document.querySelectorAll('.content-section');
      sections.forEach(section => section.classList.add('hidden'));
      document.getElementById(sectionId).classList.remove('hidden');
    }

    // UPDATED: Function to update user status
    // Function to approve the user
    function updateStatus(userId, status) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json'); // Set Content-Type to JSON

        xhr.onload = function () {
            if (xhr.status === 200) {
                alert('User approved successfully');
                location.reload();  // Refresh the page to reflect the change
            } else {
                alert('Error updating user status');
            }
        };

        // Send the data as JSON
        const data = JSON.stringify({
            id: userId,
            status: status
        });

        xhr.send(data);
    }

    function fetchSubjects(courseId) {
    const subjectList = document.getElementById('subjectList');

    // Check if a course is selected
    if (!courseId) {
        subjectList.innerHTML = "<p>Please select a course to view its subjects.</p>";
        return;
    }

    // Make an AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `fetch_subjects.php?course_id=${courseId}`, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            subjectList.innerHTML = xhr.responseText;
        } else {
            subjectList.innerHTML = "<p>Error fetching subjects. Please try again later.</p>";
        }
    };
    xhr.send();
}


</script>
</head>
<body class="bg-gray-100 text-black">
  <!-- Navbar -->
  <nav class="bg-blue-900 text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
      <a class="text-3xl font-bold" href="#">Admin Dashboard</a>
      <a class="px-4 py-2 rounded bg-blue-700 hover:bg-blue-600" href="?logout=true">Logout</a>
    </div>
  </nav>
  <div class="flex">
    <aside class="w-64 bg-blue-800 text-white min-h-screen p-4">
      <manageCourses>
        <div class="text-center mb-6">
            <img id="profileImage" alt="Portrait of the instructor" class="w-24 h-24 rounded-full mx-auto mb-4" src="<?php echo $image_path ?>" />
            <h2 class="text-xl font-bold"><?php echo $full_name ?></h2>
            <p class="text-sm"><?php echo $role_name ?></p>
        </div>
        <ul>
            <li class="mb-4"><a href="#" onclick="showSection('studentsSection')">View Students</a></li>
            <li class="mb-4"><a href="#" onclick="showSection('instructorsSection')">View Instructors</a></li>
            <li class="mb-4"><a href="#" onclick="showSection('pendingRegistrationsSection')">Pending Registrations</a></li> 
            <li class="mb-4"><a href="#" onclick="showSection('courseSubject')">Add Subjects to Course</a></li>
            <li class="mb-4"><a href="/grading-system/login/adminReg.php" >Register Admin</a></li>
            <li class="mb-4"><a href="#" onclick="showSection('manageCourses')">Add Courses</a></li>
            <li class="mb-4"><a href="#" onclick="showSection('manageSubjects')">Add Subjects</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
      <!-- student section -->
      <section id="studentsSection" class="content-section">
            <h2 class="text-2xl font-bold mb-4">All Active Students</h2>
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border">USN</th>
                        <th class="px-4 py-2 border">Full Name</th>
                        <th class="px-4 py-2 border">Email</th>
                        <th class="px-4 py-2 border">Section</th>
                        <th class="px-4 py-2 border">Enrolled Subjects</th>
                        <th class="px-4 py-2 border">Parent Code</th>
                        <th class="px-4 py-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Include the database connection
                    include '../../config/config.php';
                    $conn = conn();

                    // Updated query: Only show students with active status and fetch parent code if it exists
                    $query = "
                        SELECT 
                            students.id AS student_id,
                            student_data.usn,
                            users.full_name,
                            users.email,
                            sections.section_name,
                            COALESCE(GROUP_CONCAT(DISTINCT subjects.subject_name SEPARATOR ', '), 'No subjects enrolled') AS enrolled_subjects,
                            (SELECT code FROM parent_codes WHERE parent_codes.student_id = students.id LIMIT 1) AS parent_code
                        FROM students
                        JOIN users ON students.user_id = users.id
                        JOIN sections ON students.section_id = sections.id
                        LEFT JOIN section_subject ON sections.id = section_subject.section_id
                        LEFT JOIN subjects ON section_subject.subject_id = subjects.id
                        JOIN student_data ON student_data.student_id = students.id
                        WHERE users.status = 'active' -- Filter for active students only
                        GROUP BY students.id
                        ORDER BY students.id;
                    ";

                    $result = $conn->query($query);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "
                                <tr>
                                    <td class='px-4 py-2 border'>{$row['usn']}</td> <!-- Displaying USN from student_data -->
                                    <td class='px-4 py-2 border'>{$row['full_name']}</td>
                                    <td class='px-4 py-2 border'>{$row['email']}</td>
                                    <td class='px-4 py-2 border'>{$row['section_name']}</td>
                                    <td class='px-4 py-2 border'>{$row['enrolled_subjects']}</td>
                                    <td class='px-4 py-2 border'>" . ($row['parent_code'] ? $row['parent_code'] : 'Not generated') . "</td>
                                    <td class='px-4 py-2 border'>
                                        <form method='POST' action='' class='inline'>
                                            <input type='hidden' name='student_id' value='{$row['student_id']}'>
                                            <button type='submit' name='generate_code' class='bg-blue-500 text-white px-2 py-1 rounded'>
                                                Generate Parent Code
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='px-4 py-2 border text-center'>No active students found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <?php
        // use onlu once db conection
        require_once '../../config/config.php';
        $conn = conn();

        // parent code generate
        if (isset($_POST['generate_code']) && isset($_POST['student_id'])) {
            $student_id = intval($_POST['student_id']);
            $unique_code = bin2hex(random_bytes(8));

            // if student already has a code 
            $stmt_check = $conn->prepare("SELECT id FROM parent_codes WHERE student_id = ?");
            $stmt_check->bind_param("i", $student_id);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                // update existing code 
                $stmt_update = $conn->prepare("UPDATE parent_codes SET code = ? WHERE student_id = ?");
                $stmt_update->bind_param("si", $unique_code, $student_id);
                $stmt_update->execute();
                echo "<script>alert('Parent code updated successfully for student ID {$student_id}');</script>";
            } else {
                // insert the code created
                $stmt_insert = $conn->prepare("INSERT INTO parent_codes (code, student_id) VALUES (?, ?)");
                $stmt_insert->bind_param("si", $unique_code, $student_id);
                $stmt_insert->execute();
                echo "<script>alert('Parent code generated successfully for student ID {$student_id}');</script>";
            }

            // reload tosee
            echo "<script>window.location.href = window.location.href;</script>";
            exit;
        }
        ?>


      <!-- Instructors Section -->
      <section id="instructorsSection" class="content-section hidden">
        <h2 class="text-2xl font-bold mb-4">All Instructors</h2>
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg">
          <thead>
            <tr>
              <th class="px-4 py-2 border">Username</th>
              <th class="px-4 py-2 border">Full Name</th>
              <th class="px-4 py-2 border">Email</th>
              <th class="px-4 py-2 border">Assigned Subjects</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // UPDATED: Enhanced query for instructors
            $query = "
                SELECT 
                    u.username,
                    u.full_name,
                    u.email,
                    COALESCE(GROUP_CONCAT(s.subject_name SEPARATOR ', '), 'No subjects assigned') AS assigned_subjects
                FROM users u
                LEFT JOIN assigned_subjects asub ON u.id = asub.instructor_id
                LEFT JOIN subjects s ON asub.subject_id = s.id
                WHERE u.role_id = 1
                GROUP BY u.id;
            ";


            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "
                      <tr>
                        <td class='px-4 py-2 border'>{$row['username']}</td>
                        <td class='px-4 py-2 border'>{$row['full_name']}</td>
                        <td class='px-4 py-2 border'>{$row['email']}</td>
                        <td class='px-4 py-2 border'>{$row['assigned_subjects']}</td>
                      </tr>
                    ";
                }
            } else {
                echo "<tr><td colspan='4' class='px-4 py-2 border text-center'>No instructors found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </section>

      <!-- Pending Registrations Section -->
      <section id="pendingRegistrationsSection" class="content-section hidden">
        <h2 class="text-2xl font-bold mb-4">Pending Registrations</h2>

        <!-- students -->
        <h3 class="text-xl font-bold mb-4">Pending Students</h3>
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg mb-8">
            <thead>
                <tr>
                    <th class="px-4 py-2 border">USN</th>
                    <th class="px-4 py-2 border">Username</th>
                    <th class="px-4 py-2 border">Full Name</th>
                    <th class="px-4 py-2 border">Email</th>
                    <th class="px-4 py-2 border">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query_students = "
                    SELECT 
                        student_data.usn, 
                        users.id AS user_id, 
                        users.username, 
                        users.full_name, 
                        users.email 
                    FROM 
                        users
                    LEFT JOIN 
                        students ON users.id = students.user_id
                    LEFT JOIN 
                        student_data ON students.id = student_data.student_id
                    WHERE 
                        users.status = 'pending' AND users.role_id = (SELECT id FROM roles WHERE role_name = 'Student')
                ";

                $result_students = $conn->query($query_students);

                if ($result_students->num_rows > 0) {
                    while ($row = $result_students->fetch_assoc()) {
                        echo "
                            <tr>
                                <td class='px-4 py-2 border'>" . ($row['usn'] ?? 'No USN') . "</td>
                                <td class='px-4 py-2 border'>{$row['username']}</td>
                                <td class='px-4 py-2 border'>{$row['full_name']}</td>
                                <td class='px-4 py-2 border'>{$row['email']}</td>
                                <td class='px-4 py-2 border'>
                                    <button onclick=\"updateStatus({$row['user_id']}, 'active')\" class='bg-green-500 text-white px-4 py-2 rounded'>Approve</button>
                                    <button onclick=\"confirmReject({$row['user_id']}, '{$row['email']}')\" class='bg-red-500 text-white px-4 py-2 rounded'>Reject</button>
                                </td>
                            </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='5' class='px-4 py-2 border text-center'>No pending student registrations.</td></tr>";
                }              
                ?>
            </tbody>
        </table>

        <!-- Instructors Table -->
        <h3 class="text-xl font-bold mb-4">Pending Instructors</h3>
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead>
                <tr>
                    <th class="px-4 py-2 border">Username</th>
                    <th class="px-4 py-2 border">Full Name</th>
                    <th class="px-4 py-2 border">Email</th>
                    <th class="px-4 py-2 border">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query_instructors = "
                    SELECT 
                        users.id AS user_id, 
                        users.username, 
                        users.full_name, 
                        users.email 
                    FROM 
                        users
                    WHERE 
                        users.status = 'pending' AND users.role_id = (SELECT id FROM roles WHERE role_name = 'Instructor')
                ";

                $result_instructors = $conn->query($query_instructors);

                if ($result_instructors->num_rows > 0) {
                    while ($row = $result_instructors->fetch_assoc()) {
                        echo "
                            <tr>
                                <td class='px-4 py-2 border'>{$row['username']}</td>
                                <td class='px-4 py-2 border'>{$row['full_name']}</td>
                                <td class='px-4 py-2 border'>{$row['email']}</td>
                                <td class='px-4 py-2 border'>
                                    <button onclick=\"updateStatus({$row['user_id']}, 'active')\" class='bg-green-500 text-white px-4 py-2 rounded'>Approve</button>
                                    <button onclick=\"confirmReject({$row['user_id']}, '{$row['email']}')\" class='bg-red-500 text-white px-4 py-2 rounded'>Reject</button>
                                </td>
                            </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='4' class='px-4 py-2 border text-center'>No pending instructor registrations.</td></tr>";
                }              
                ?>
            </tbody>
        </table>
      </section>

                <!-- add subject under the course -->
        <section id="courseSubject" class="content-section hidden">
            <h2>Add Subject to a Course</h2>
            <form action="add_subject.php" method="post" class="table-form">
                <!-- Course Selection -->
                <div class="form-row">
                    <label for="course_id">Select Course:</label>
                    <select id="course_id" name="course_id" required onchange="fetchSubjects(this.value)">
                        <?php
                        // Fetch all courses from the database
                        $conn = conn();
                        $query = "SELECT id, course_name FROM courses";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['course_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Subject Selection -->
                <div class="form-row">
                    <label for="subject_id">Select Subject:</label>
                    <select id="subject_id" name="subject_id" required>
                        <?php
                        // Fetch all subjects from the subject table
                        $query = "SELECT id, subject_name FROM subjects";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['subject_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-row">
                    <button type="submit" name="add_subject_to_course" class="blue-btn">Add Subject to Course</button>
                </div>
            </form>

            <h3>Subjects Assigned to the Selected Course</h3>
            <div id="subjectList" class="subject-table">
                <!-- Subjects will be dynamically loaded here using JavaScript -->
            </div>
        </section>

        <!-- add course -->
        <section id="manageCourses" class="content-section hidden">
            <h2 class="text-3xl font-bold mb-4">Manage Courses</h2>
            <?php
            include_once '../../config/config.php';

            // Check if the user is logged in and has the appropriate role
            if (!isset($_SESSION['user_id'])) {
                echo "<p>Please log in to manage courses.</p>";
            } else {
                $userId = $_SESSION['user_id'];

                // Fetch the user's role
                $roleQuery = "SELECT role_id FROM users WHERE id = ?";
                $roleStmt = $conn->prepare($roleQuery);
                $roleStmt->bind_param('i', $userId);
                $roleStmt->execute();
                $roleResult = $roleStmt->get_result();
                $userRole = $roleResult->fetch_assoc()['role_id'];

                // Check if the user is Admin (4) or Super Admin (5)
                if ($userRole == 4 || $userRole == 5) {
                    // Form for adding a new course
                    echo '<div class="bg-white p-6 rounded-lg shadow-lg">';
                    echo '<h3 class="text-2xl font-semibold mb-4">Add a New Course</h3>';
                    echo '<form action="../../controllers/courseController.php" method="post">';
                    echo '<input type="text" name="course_name" class="w-full border border-gray-300 p-2 rounded mb-4" placeholder="Enter course name" required>';

                    // Dropdown to select an instructor
                    echo '<label for="instructor_id" class="block mb-2">Assign Instructor:</label>';
                    echo '<select name="instructor_id" id="instructor_id" class="w-full border border-gray-300 p-2 rounded mb-4" required>';
                    echo '<option value="">-- Select Instructor --</option>';

                    // Fetch all active instructors
                    $query = "SELECT id, full_name FROM users WHERE role_id = 1 AND status = 'active'"; // Assuming role_id 1 is for instructors
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['full_name']) . '</option>';
                    }

                    $stmt->close();
                    echo '</select>';
                    echo '<button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded">Add Course</button>';
                    echo '</form>';
                    echo '</div>';

                    // Table displaying courses
                    echo '<div class="mt-6">';
                    echo '<h3 class="text-2xl font-semibold mb-4">Courses List</h3>';
                    echo '<table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th class="px-4 py-2 border">Course Name</th>';
                    echo '<th class="px-4 py-2 border">Assigned Instructor</th>';
                    echo '<th class="px-4 py-2 border">Subjects</th>';
                    echo '<th class="px-4 py-2 border">Actions</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';

                    // Fetch courses
                    $query = "SELECT c.id AS course_id, c.course_name, u.full_name AS instructor_name, s.subject_name
                            FROM courses c
                            LEFT JOIN course_subject cs ON c.id = cs.course_id
                            LEFT JOIN subjects s ON cs.subject_id = s.id
                            LEFT JOIN users u ON c.instructor_id = u.id
                            WHERE u.status = 'active'";

                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $courses = [];

                    // Group subjects under their respective courses
                    while ($row = $result->fetch_assoc()) {
                        $courses[$row['course_id']]['course_name'] = $row['course_name'];
                        $courses[$row['course_id']]['instructor_name'] = $row['instructor_name'];
                        $courses[$row['course_id']]['subjects'][] = $row['subject_name'];
                    }

                    if (!empty($courses)) {
                        foreach ($courses as $courseId => $course) {
                            echo "<tr>
                                    <td class='px-4 py-2 border'>" . htmlspecialchars($course['course_name']) . "</td>
                                    <td class='px-4 py-2 border'>" . htmlspecialchars($course['instructor_name']) . "</td>
                                    <td class='px-4 py-2 border'>" . (count($course['subjects']) > 0 ? implode(", ", $course['subjects']) : 'No subjects assigned') . "</td>
                                    <td class='px-4 py-2 border'>
                                        <form action='delete_course.php' method='post' class='inline' onsubmit='return confirmDelete();'>
                                            <input type='hidden' name='course_id' value='$courseId'>
                                            <button type='submit' class='bg-red-600 text-white px-2 py-1 rounded'>Delete Course</button>
                                        </form>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='px-4 py-2 border'>No courses found.</td></tr>";
                    }

                    $stmt->close();
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo "<p>You do not have permission to manage courses.</p>";
                }
            }
            ?>
        </section>


        <!-- add new subjects -->
        <section id="manageSubjects" class="content-section hidden">
            <h2 class="text-3xl font-bold mb-4">Manage Subjects</h2>

            <!-- Form to Add New Subject -->
            <form method="POST" action="addSubjects.php" class="mb-6">
                <label for="subject_name" class="block text-lg font-medium">Add New Subject:</label>
                <input type="text" name="subject_name" id="subject_name" class="border border-gray-300 rounded px-4 py-2 mt-2 w-full" placeholder="Enter subject name" required>
                <button type="submit" name="add_subject" class="bg-blue-500 text-white px-4 py-2 rounded mt-4 hover:bg-blue-600">Add Subject</button>
            </form>
            <?php
                // Fetch all subjects to display in the table
            $query = "SELECT id, subject_name FROM subjects ORDER BY id ASC";
            $result = $conn->query($query);
            ?>
            <!-- Table to Display Subjects -->
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border">Id</th>
                            <th class="px-4 py-2 border">Subject Name</th>
                            <th class="px-4 py-2 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['id']); ?></td>
                                <td class="px-4 py-2 border">
                                    <form method="POST" action="addSubjects.php" class="inline">
                                        <input type="hidden" name="subject_id" value="<?php echo $row['id']; ?>">
                                        <input type="text" name="updated_subject_name" 
                                            value="<?php echo htmlspecialchars($row['subject_name']); ?>" 
                                            class="border border-gray-300 rounded px-4 py-1 w-full" 
                                            style="max-width: 400px;"> <!-- Adjust the width here -->
                                        <button type="submit" name="update_subject" 
                                                class="bg-yellow-500 text-white px-2 py-1 rounded ml-2 hover:bg-yellow-600">
                                            update
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-2 border">
                                    <form method="POST" action="addSubjects.php" class="inline">
                                        <input type="hidden" name="subject_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_subject" 
                                                class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                                            delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
        </section>


    </main>
  </div>
  <script>
        function confirmReject(userId, email) {
    const reason = prompt("Please enter the reason for rejection:");
    if (reason) {
        // Confirm rejection with a reason
        if (confirm(`Are you sure you want to reject the registration for ${email}?`)) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'reject_user.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json'); // Set Content-Type to JSON

            xhr.onload = function () {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    alert(response.message);
                    if (response.success) {
                        location.reload(); // Refresh the page to reflect the change
                    }
                } else {
                    alert('Error rejecting user');
                }
            };

            // Send the data as JSON
            const data = JSON.stringify({
                id: userId,
                status: 'rejected',
                email: email,
                reason: reason
            });

            xhr.send(data);
        }
    } else {
        alert("Rejection reason is required.");
    }
}

    // Confirm delete action
    function confirmDelete() {
        return confirm("Are you sure you want to delete this course?");
    }

  </script>
</body>
</html>
