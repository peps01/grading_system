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
    function toggleFields() {
        // Hide all fields initially
        document.getElementById('quiz_fields').classList.add('hidden');
        document.getElementById('activity_fields').classList.add('hidden');
        document.getElementById('attendance_fields').classList.add('hidden');
        document.getElementById('exam_fields').classList.add('hidden');

        // Get the selected record type
        const recordType = document.getElementById('record_type').value;

        // Show the corresponding fields based on the selected type
        if (recordType === 'quiz') {
            document.getElementById('quiz_fields').classList.remove('hidden');
        } else if (recordType === 'activity') {
            document.getElementById('activity_fields').classList.remove('hidden');
        } else if (recordType === 'attendance') {
            document.getElementById('attendance_fields').classList.remove('hidden');
        } else if (recordType === 'exam') {
            document.getElementById('exam_fields').classList.remove('hidden');
        }
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
      <li class="mb-4"><a class="hover:underline" href="instructor.php" >Dashboard</a></li>
        <li class="mb-4"><a class="hover:underline" href="#" onclick="showSection('inputRecords')">Manage Records</a></li>
      </ul>
    </aside>
    <main class="flex-1 p-8">

    <section id="inputRecords" class="content-section ">
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
            <select id="record_type" name="record_type" class="w-full border border-gray-300 p-2 rounded mb-4" required onchange="toggleFields()">
                <option value="">-- Choose Type --</option>
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
        <div id="attendance_fields" class="hidden">
            <label for="attendance_date" class="block text-lg font-semibold mb-2">Attendance Date:</label>
            <input type="date" id="attendance_date" name="attendance_date" class="w-full border border-gray-300 p-2 rounded mb-4">
            <label for="attendance_status" class="block text-lg font-semibold mb-2">Attendance Status:</label>
            <select id="attendance_status" name="attendance_status" class="w-full border border-gray-300 p-2 rounded mb-4">
                <option value="Present">Present</option>
                <option value="Absent">Absent</option>
                <option value="Late">Late</option>
                <option value="Excused">Excused</option>
            </select>
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
</section>

 

    </main>
  </div>
  

</body>
</html>
