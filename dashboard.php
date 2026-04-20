<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$uid = $_SESSION['user_id'];
$role = $_SESSION['role'];

$user_query = mysqli_query($conn, "SELECT username FROM users WHERE id='$uid'");
$user = mysqli_fetch_assoc($user_query);

// Passing scores logic
$passing_score_assessment = 3; 

// --- COUNTERS PARA SA DASHBOARD ---
if($role == 'student') {
    $to_learn_q = "SELECT COUNT(*) as remaining FROM modules WHERE id NOT IN (SELECT module_id FROM progress WHERE student_id = '$uid')";
    $modules_remaining = mysqli_fetch_assoc(mysqli_query($conn, $to_learn_q))['remaining'];
} else {
    $count_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='student'"))['total'];
    $count_modules = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM modules"))['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - CodeQuest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="bg-visuals">
        <div class="bg-grid"></div>
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <div class="sidebar">
        <h2>CodeQuest</h2>
        <a href="dashboard.php" class="active">Dashboard</a>
        
        <?php if($role == 'teacher'): ?>
            <a href="manage_modules.php">Manage Modules</a>
            <a href="manage_quizzes.php">Manage Quizzes</a>
            <a href="manage_assessments.php">Assessments Pool</a>
            <a href="generate_reports.php">Reports</a>
        <?php else: ?>
            <a href="view_modules.php">Learn Modules</a>
            <a href="student_assessments.php">Take Assessment</a>
            <a href="certifications.php">Certificates</a>
        <?php endif; ?>
        
        <a href="profile.php">My Profile</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-header">
            <h1>Hello, <?= htmlspecialchars($user['username']) ?>! 👋</h1>
            <p style="color: var(--text-muted);">Welcome to your <?= ucfirst($role) ?> Dashboard.</p>
        </div>

        <div class="stats-grid">
            <?php if($role == 'teacher'): ?>
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <p><?= $count_students ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Modules</h3>
                    <p><?= $count_modules ?></p>
                </div>
            <?php else: ?>
                <div class="stat-card">
                    <h3>Modules to Learn</h3>
                    <p><?= $modules_remaining ?></p>
                </div>
                <div class="stat-card">
                    <h3>Status</h3>
                    <p>Active</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="notion-table-card">
            <h3>Recent Activity</h3>
            <table>
                <thead>
                    <tr>
                        <th>Activity / Chapter</th>
                        <th>Score</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $history_q = "SELECT c.title, s.score, s.date_taken FROM assessment_scores s JOIN assessment_chapters c ON s.chapter_id = c.id WHERE s.user_id = '$uid' ORDER BY s.date_taken DESC LIMIT 5";
                    $history_res = mysqli_query($conn, $history_q);
                    if(mysqli_num_rows($history_res) > 0):
                        while($h = mysqli_fetch_assoc($history_res)):
                            $passed = ($h['score'] >= $passing_score_assessment);
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($h['title']) ?></strong></td>
                            <td><?= $h['score'] ?> / 5</td>
                            <td><?= date('M d, Y', strtotime($h['date_taken'])) ?></td>
                            <td><span class="status-badge <?= $passed ? 'pass' : 'fail' ?>"><?= $passed ? 'Passed' : 'Failed' ?></span></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="4" style="text-align:center;">No recent activity found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
