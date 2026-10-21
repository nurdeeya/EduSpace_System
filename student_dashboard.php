<?php
session_start();

// Allow only logged-in students.
if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'student'
) {
    header('Location: login.php');
    exit;
}

$fullName = $_SESSION['full_name'] ?? 'Student';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>

    <link rel="stylesheet" href="css/student_dashboard_style.css">

</head>
<body>

    <!-- Navigation bar -->
    <header class="navbar">
        <div class="brand"> EduSphere</div>

        <div class="navbar-right">
            <span class="role-label">Student Account</span>
            <a class="logout-btn" href="logout.php">Log Out</a>
        </div>
    </header>

    <!--Main DAshboard -->
    <main class="container">
        <section class="welcome-section">
            <h1>
                Welcome, <?= htmlspecialchars(
                    $fullName,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </h1>
            
            <p>
                Welcome to EduSphere. Access your learning resources
                and manage your study activities from your dashboard.
            </p>
        </section>

        <h2 class="section-title">Your Dashboard</h2>

        <section class="card-container">
            <div class="card">
                <h3>Account Role</h3>
                <p>Student</p>
            </div>

            <div class="card">
                <h3>Account ID</h3>
                <p><?= htmlspecialchars(
                    (string) $_SESSION['user_id'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></p>
            </div> 

            <div class="card">
                <h3>Account Status</h3>
                <p>Active</p>
            </div>
        </section>

        <section class="information">
            <h2>Getting Started</h2>

            <p>
                You have successfully logged in to EduSphere.
                This dashboard is your starting point for accessing
                student learning features.
            </p>

            <p>
                More features, such as learning materials,
                courses and announcements, can be added later.
            </p>
        </section>

    </main>

    <footer>
        &copy; <?= date('Y') ?> EduSphere System
    </footer>

</body>
</html>
