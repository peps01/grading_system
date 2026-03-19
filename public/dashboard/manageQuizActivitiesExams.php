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
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border">ID</th>
                            <th class="px-4 py-2 border">USN</th>
                            <th class="px-4 py-2 border">Full Name</th>
                            <th class="px-4 py-2 border">Email</th>
                            <th class="px-4 py-2 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ((isset($_POST['type']) && $_POST['type'] == 'quiz' && isset($_POST['quiz_id']) && $_POST['quiz_id'] != '') ||
                            (isset($_POST['type']) && $_POST['type'] == 'activity' && isset($_POST['activity_id']) && $_POST['activity_id'] != '') ||
                            (isset($_POST['type']) && $_POST['type'] == 'exam' && isset($_POST['exam_id']) && $_POST['exam_id'] != '')) {
                            $id = $_POST['type'] == 'quiz' ? $_POST['quiz_id'] : ($_POST['type'] == 'activity' ? $_POST['activity_id'] : $_POST['exam_id']);
                            $type = $_POST['type'];

                            $query = "
                                SELECT 
                                    students.id AS student_id, 
                                    student_data.usn, 
                                    users.full_name, 
                                    users.email 
                                FROM students 
                                JOIN users ON students.user_id = users.id 
                                JOIN student_data ON student_data.student_id = students.id 
                                WHERE users.status = 'active' 
                                AND students.section_id IN (
                                    SELECT section_id FROM section_subject WHERE subject_id = ?
                                )
                                ORDER BY students.id";

                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $_POST['subject_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<tr>
                                            <td class="px-4 py-2 border">' . htmlspecialchars($row['student_id']) . '</td>
                                            <td class="px-4 py-2 border">' . htmlspecialchars($row['usn']) . '</td>
                                            <td class="px-4 py-2 border">' . htmlspecialchars($row['full_name']) . '</td>
                                            <td class="px-4 py-2 border">' . htmlspecialchars($row['email']) . '</td>
                                            <td class="px-4 py-2 border">
                                                <form method="POST" action="' . ($type == 'quiz' ? 'saveQuizscores.php' : ($type == 'activity' ? 'saveActivityScore.php' : 'saveExamScores.php')) . '">
                                                    <input type="hidden" name="student_id" value="' . $row['student_id'] . '">
                                                    <label for="score">Score:</label>
                                                    <input type="number" name="score" placeholder="Enter score" required>
                                                    <input type="hidden" name="' . $type . '_id" value="' . $id . '">
                                                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Score</button>
                                                </form>
                                            </td>
                                        </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5" class="px-4 py-2 border text-center">No students found.</td></tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>