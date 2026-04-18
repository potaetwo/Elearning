<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php"); exit();
}

$uid = $_SESSION['user_id'];

// UPDATED: Match your dashboard logic where 2/3 or 3/3 is a pass
$passing_score = 2; 

// These are the prefixes we are looking for
$required_chapters = ['Chapter 1', 'Chapter 2', 'Chapter 3'];

$passed_chapters = [];
foreach ($required_chapters as $chapter_prefix) {
    // UPDATED: Use LIKE '$chapter_prefix%' to match titles starting with "Chapter 1..."
    $check_q = "SELECT p.id FROM progress p 
                JOIN modules m ON p.module_id = m.id 
                WHERE p.student_id = '$uid' 
                AND m.title LIKE '$chapter_prefix%' 
                AND p.quiz_score >= $passing_score";
    
    $res = mysqli_query($conn, $check_q);
    if ($res && mysqli_num_rows($res) > 0) {
        $passed_chapters[] = $chapter_prefix;
    }
}

$is_fully_completed = (count($passed_chapters) === count($required_chapters));
$progress_percent = (count($required_chapters) > 0) ? (count($passed_chapters) / count($required_chapters)) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certifications & Badges - CodeQuest</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .badge-container { display: flex; justify-content: center; gap: 30px; margin-top: 30px; flex-wrap: wrap; }
        .badge-card { width: 180px; padding: 25px; text-align: center; background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 3px solid #eee; transition: 0.3s; }
        
        .badge-card.earned { border-color: #f1c40f; background: #fffdf2; transform: translateY(-5px); }
        .badge-card.locked { opacity: 0.5; background: #f9f9f9; }
        
        .icon { font-size: 60px; display: block; margin-bottom: 15px; }
        .badge-name { font-weight: bold; color: #2c3e50; display: block; margin-bottom: 5px; }
        .status-text { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }

        .cert-hero { margin-top: 50px; padding: 60px 40px; background: linear-gradient(135deg, #1a2a3a, #2c3e50); color: white; border-radius: 20px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        
        .cert-title {
            font-size: 3em;
            font-weight: 900;
            margin: 15px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: linear-gradient(to right, #bf953f, #fcf6ba, #b38728, #fbf5b7, #aa771c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0px 3px 2px rgba(0,0,0,0.4));
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .btn-download { background: #f1c40f; color: #2c3e50; padding: 18px 35px; border-radius: 50px; text-decoration: none; font-weight: bold; display: inline-block; margin-top: 25px; transition: 0.3s; box-shadow: 0 4px 15px rgba(241, 196, 15, 0.3); }
        .btn-download:hover { background: #d4ac0d; transform: scale(1.05); }
        
        .progress-bar { width: 100%; max-width: 450px; height: 14px; background: rgba(255,255,255,0.1); border-radius: 10px; margin: 25px auto; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); }
        .progress-fill { height: 100%; background: linear-gradient(to right, #f1c40f, #f39c12); transition: width 1s ease-in-out; }
        
        .cert-icon { font-size: 100px; display: block; margin-bottom: 5px; filter: drop-shadow(0 0 15px rgba(241, 196, 15, 0.3)); }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>CodeQuest</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="view_modules.php">Learn Modules</a>
        <a href="student_assessments.php">Take Assessment</a>
        <a href="certifications.php" class="active">Badges & Certificates</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Your Learning Achievements</h1>
        <p>Complete the assessments for Chapters 1, 2, and 3 to earn your Master Certificate.</p>

        <div class="badge-container">
            <?php foreach ($required_chapters as $chapter): 
                $has_earned = in_array($chapter, $passed_chapters);
            ?>
                <div class="badge-card <?= $has_earned ? 'earned' : 'locked' ?>">
                    <span class="icon"><?= $has_earned ? '🏅' : '🔒' ?></span>
                    <span class="badge-name"><?= $chapter ?></span>
                    <span class="status-text" style="color: <?= $has_earned ? '#d4ac0d' : '#95a5a6' ?>;">
                        <?= $has_earned ? 'Mastered' : 'Locked' ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cert-hero">
            <span class="cert-icon"><?= $is_fully_completed ? '🏆' : '📜' ?></span>
            
            <h2 class="cert-title">CodeQuest Mastery Certificate</h2>
            
            <?php if($is_fully_completed): ?>
                <p style="font-size: 1.3em; color: #fff; margin-top: 10px;">
                    This is to certify that you have successfully mastered the CodeQuest Curriculum.
                </p>
                <a href="generate_cert_pdf.php" class="btn-download">Download Official PDF Certificate</a>
            <?php else: ?>
                <div style="background: rgba(0,0,0,0.2); padding: 20px; border-radius: 15px; display: inline-block; width: 100%; max-width: 500px;">
                    <p style="margin-top:0;">Requirement: Complete all 3 Chapters</p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $progress_percent ?>%;"></div>
                    </div>
                    <p style="font-size: 14px; color: #bdc3c7; margin-bottom: 0;">
                        Current Progress: <strong><?= count($passed_chapters) ?> / 3</strong> Chapters Mastered
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>