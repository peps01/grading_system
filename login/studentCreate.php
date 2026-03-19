<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Student Account</title>
    <link rel="stylesheet" href="../public/assets/css/login.css">
</head>
<body>
<nav>
    <div id="navi">
        <a class="a" href="../index.html">Grading System</a>
    </div>
    <button onclick="window.location.href='../login/createAccount.php';" class="back-button">Back</button>
</nav>

<div class="container">
    <div class="login-box">
        <h1>Create Student Account</h1><br>
        <p class="info">Your account will be pending approval by the administrator before activation.</p> <br>
        <form action="/grading-system/controllers/studentRegister.php" method="post">
            <div class="form-group">
                <label for="usn">USN</label>
                <input type="number" name="usn" placeholder="Enter USN" required>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" placeholder="Enter Full Name" required>
            </div>
            <div class="form-group">
                <label for="section_id">Section</label>
                <select name="section_id" required>
                    <option value="" disabled selected>Select Section</option>
                    <?php
                    include '../config/config.php';
                    $conn = conn();
                    $query = "SELECT id, section_name FROM sections";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['section_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" placeholder="Enter Email" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" placeholder="Enter Username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Enter Password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <button type="submit" name="createStudentAccountBtn" class="btn">Create Account</button>
        </form>
    </div>
</div>
</body>
</html>
