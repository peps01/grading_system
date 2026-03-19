<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/assets/css/login.css">
    <title>Create Instructor Account</title>
</head>
<body>
<nav>
    <div id="navi">
        <a class="a" href="..\index.html">Grading System</a>
    </div>
    <div class="button-container">
        <button onclick="window.location.href='../index.html'" class="back-button">Back</button>
        <button onclick="window.location.href='../login/studentCreate.php'" class="back-button">Register as Student</button>
    </div>
</nav>

<div class="container">
    <div class="login-box">
        <h1>Create Instructor Account</h1>
        <form action="../controllers/instructorRegister.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" placeholder="Enter Username" required>
                <span></span><br>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Enter Password" required>
                <span></span><br>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" placeholder="Enter Full Name" required>
                <span></span><br>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" placeholder="Email" required>
                <span></span><br>
            </div>
            <div class="form-group">
                <label for="instructor_code">Instructor Code</label>
                <input type="text" name="instructor_code" placeholder="Enter Instructor Code" required>
                <span></span><br>
            </div>
            <div class="form-group">
                <input type="hidden" name="role_id" value="1"> <!-- Fixed role for Instructor -->
                <input type="submit" value="Create" class="btn" name="createInstructorBtn">
            </div>  
        </form>
    </div>
</div>
</body>
</html>
