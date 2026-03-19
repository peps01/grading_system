<?php
session_start(); // Start session for login tracking
require_once '../config/config.php'; // Database connection
$conn = conn();

$message = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user data based on the username
    $query = $conn->prepare("
    SELECT users.*, roles.role_name 
    FROM users 
    JOIN roles ON users.role_id = roles.id 
    WHERE users.username = ? AND users.status = 'active'
");
$query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables using $user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];

            // Redirect based on role_id
            if ($user['role_id'] == 5) { // Super Admin
                header("Location: ../public/dashboard/admin.php");
                exit();
            } elseif ($user['role_id'] == 4) { // Admin
                header("Location: ../public/dashboard/admin.php");
                exit();
            } else {
                $message = "<script>alert('Access denied. Invalid role.')</script>";
            }
        } else {
            $message = "<script>alert('Incorrect password.')</script>";
        }
    } else {
        $message = "<script>alert('User not found or Inactive.')</script>";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../public/assets/css/login.css">
</head>
<body>
    <nav>
        <div id="navi">
            <a class="a" href="../index.html">Grading System</a>
            <div class="remind">
                <h1 class="reminder">Admin Portal</h1>
            </div>
        </div>  
    </nav>
    <div class="container">
        <div class="login-box">
            <h1>Login</h1>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn">Login</button>
            </form>
            <p><?php echo $message; ?></p>
        </div>
    </div>
    <footer>
        <p>© 2024 Portal</p>
    </footer>
</body>
</html>
