<!DOCTYPE html>
<html>
<head>
    <title>Parent Access</title>
    <link rel="stylesheet" href="..\public\assets\css\login.css">
</head>
<body>
<nav>
    <div id="navi">
        <a class="a" href="..\index.html">Grading System</a>
        <div class="remind">
            <h1 class="reminder">Parent Access</h1>
        </div>
    </div>  
    <div class="bck">
        <button onclick="window.location.href='../public/role.php'" class="back-button">Back</button>
    </div>
</nav>
<div class="container">
    <div class="login-box">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_code'): ?>
            <?php echo '<script>alert("Invalid access code. Please try again.")</script>'; ?> 
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'no_code'): ?>
           <?php echo '<script>alert("No access code provided. Please enter a valid code.")</script>'; ?> 
        <?php endif; ?>
        <form method="POST" action="parent_login.php">
            <div class="form-group">
                <label>Access Code:</label>
                <input type="text" name="code" required><br>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</div>
<footer>
    <p>© 2024 Grading System. All rights reserved.</p>
</footer>
</body>
</html>