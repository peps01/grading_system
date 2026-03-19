<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="../public/assets/css/login.css">

</head>
<body>
  <nav>
    <div id="navi">
        <a class="a" href="..\index.html">Grading System</a>
        <div class="remind">
        <h1 class="reminder" >Instructor Access</h1> 
        </div>
    </div>

    <button onclick="window.location.href='../public/role.php'"; class="back-button">Back</button>
  </nav>
    <div class="container">
        <div class="login-box">
            <h1>Login</h1>
            <form method="POST" action="..\controllers\loginController.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                    <!-- <span class="toggle-password" onclick="togglePassword('password')">&#128065;</span> -->
                </div>
                <button type="submit" name="instructorSubmitBtn" class="btn">Login</button>
            </form>
        </div>
    </div>
    <footer>
        <p>© 2024 Grading System. All rights reserved.</p>
    </footer>
    <script src="../public/assets/js/main.js"></script>
</body>
</html>
