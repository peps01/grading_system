<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Grading System - Landing Page</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet"/>
    <style>
        .container {
            padding-top: 150px;
        }
        .text-5xl {
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
        }
        .px-8 {
            background-color: blue;
            text-decoration: none;
            padding: 10px;
            color: white;
            font-size: 1rem;
            font-weight: bold;
        }
        .feature-icon {
            transition: transform 0.3s;
        }
        .feature-icon:hover {
            cursor: pointer;
            transform: scale(1.05);
        }
        .feature-card:hover {
            background-color: rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s;
        }
    </style>
</head>
<body class="body-font">

    <nav class="navbar">
        <div class="navbar-container">
            <a class="navbar-logo" href="../index.html">Grading System</a>
        </div>
    </nav>

    <section class="features">
        <div class="features-container">
            <h2 class="features-title">Your Role</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <img alt="Instructor Dashboard Icon" class="feature-icon" src="../public/img/teach.jpg" onclick="window.location.href='../login/instructor.php'"/>
                    <h3 class="feature-title">Instructor Dashboard</h3>
                    <p class="feature-description">Manage sections, students, and grades with ease.</p>
                </div>
                <div class="feature-card">
                    <img alt="Student Dashboard Icon" class="feature-icon" src="../public/img/stud.jpg" onclick="window.location.href='../login/student.php'"/>
                    <h3 class="feature-title">Student Dashboard</h3>
                    <p class="feature-description">View courses and track your grades in real-time.</p>
                </div>
                <div class="feature-card">
                    <img alt="Parent Dashboard Icon" class="feature-icon" src="../public/img/par.jpg" onclick="window.location.href='../login/parent.php'"/>
                    <h3 class="feature-title">Parent Dashboard</h3>
                    <p class="feature-description">Monitor your child's academic progress effortlessly.</p>
                </div>
            </div>
        </div>
    </section>
    
    <script src="assets/js/main.js"></script>

    <footer class="footer">
        <div class="footer-container">
            <p>&copy; 2024 Grading System. All rights reserved.</p>
        </div>
    </footer>
    
</body>
</html>