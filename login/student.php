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
        <h1 class="reminder" >Student Access</h1>   
    </div>
    <div class="bck">
    <button onclick="window.location.href='../public/role.php'"; class="back-button">Back</button>
    </div>
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
                </div>
                <button type="submit" name="studentSubmitBtn" class="btn">Login</button>
            </form>
        </div>
    </div>
    <footer>
        <p>© 2024 Grading System. All rights reserved.</p>
    </footer>
</body>
</html>
