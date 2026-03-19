<?php
session_start();
require_once '../config/config.php';
$conn = conn();
$message = '';
if (!isset($_SESSION['user_id']) || $_SESSION['role_name'] !== 'Super Admin') {
    header("Location: " . $_SERVER['PHP_SELF']); // self direct
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    
    // Validate if the code exists and is unused
    $codeQuery = $conn->prepare("SELECT * FROM superadmin_codes WHERE code = ? AND status = 'unused'");
    $codeQuery->bind_param("s", $code);
    $codeQuery->execute();
    $codeResult = $codeQuery->get_result();

    if ($codeResult->num_rows > 0) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // para sure superadmin
        $role_id = 5; 
        
        // Insert new user into the database
        $insertUser = $conn->prepare("INSERT INTO users (username, password, full_name, email, role_id, status) 
                                     VALUES (?, ?, ?, ?, ?, 'active')");
        $insertUser->bind_param("ssssi", $username, $hashed_password, $full_name, $email, $role_id);
        
        if ($insertUser->execute()) {
            // Mark the code as 'used'
            $updateCode = $conn->prepare("UPDATE superadmin_codes SET status = 'used' WHERE code = ?");
            $updateCode->bind_param("s", $code);
            $updateCode->execute();

            $message = "<script>alert('Super Admin registration successful!')</script>.";
            header("Location: s&admin.php");
        } else {
        $message = "<script>alert('Error: Could not register. Please try again.')</script>";
        }
    } else {
        $message = "<script>alert('Invalid or already used Registration code.')</script>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Super Admin Registration</title>
    <link rel="stylesheet" href="..\public\assets\css\login.css">
</head>
<body>
    <!-- Navigation -->
    <div id="navi">
        <span class="reminder" style="color: white; font-size:2rem; font-weight: bold;" >Super Admin Registration</span>
        <a href="../public/dashboard/admin.php" class="a back-button">Back</a>
    </div>

    <!-- Main Content Container -->
    <div class="container">
        <div class="login-box">
            <h1>Super Admin Registration</h1>
            <br>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="code">Registration Code:</label>
                    <input type="text" id="code" name="code" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <button type="submit" class="btn">Register</button>
            </form>
            <p><?php echo $message; ?></p>
        </div>
    </div>
    <footer>
        <p>© 2024 Super Admin Portal</p>
    </footer>
</body>
</html>

