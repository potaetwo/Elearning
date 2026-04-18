<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

// Fetch available assessment chapters
$chapters = mysqli_query($conn, "SELECT * FROM assessment_chapters ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assessments - CodeQuest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar">
        <h2>CodeQuest</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="view_modules.php">Learn Modules</a>
        <a href="student_assessments.php" class="active">Take Assessment</a>
        <a href="certifications.php">Badges & Certificates</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h2>Available Assessments</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
            <?php if(mysqli_num_rows($chapters) > 0): ?>
                <?php while($c = mysqli_fetch_assoc($chapters)): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($c['title']); ?></h3>
                        <p>Format: 5 Random Questions</p>
                        <a href="take_assessment.php?id=<?php echo $c['id']; ?>" class="btn-block" 
                           style="background: #9b59b6; text-decoration: none; text-align: center; display: block; color: white; padding: 10px; border-radius: 5px;">
                           Start Exam
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No assessments available at this time.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>