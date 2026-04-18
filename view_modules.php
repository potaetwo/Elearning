<?php
include 'db.php';
session_start();

if(!isset($_SESSION['user_id'])) header("Location: login.php");

$result = mysqli_query($conn, "SELECT * FROM modules");
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar">
        <h2>CodeQuest</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="view_modules.php" class="active">Learn Modules</a>
        <a href="student_assessments.php">Take Assessment</a>
        <a href="certifications.php">Badges & Certificates</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h2>Available Programming Modules</h2>
        <div class="module-grid">
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($row['content'], 0, 100)); ?>...</p>
                    <a href="study.php?id=<?php echo $row['id']; ?>" class="btn-block" style="text-decoration:none; display:inline-block; text-align:center; margin-top:10px;">Start Learning</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>