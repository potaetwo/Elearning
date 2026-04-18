<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];
$role = $_SESSION['role'];

$user_query = mysqli_query($conn, "SELECT username FROM users WHERE id='$uid'");
$user = mysqli_fetch_assoc($user_query);

// Passing scores
$passing_score_assessment = 3; // 4 out of 5
$passing_score_module = 2;     // Updated to 2: Students pass with 2/3 or 3/3

// --- STUDENT SPECIFIC COUNTERS ---
// --- STUDENT SPECIFIC COUNTERS ---
if($role == 'student') {
    // FIX: Count individual modules (9) instead of chapters (3)
    $to_learn_q = "SELECT COUNT(*) as remaining FROM modules WHERE id NOT IN (SELECT module_id FROM progress WHERE student_id = '$uid')";
    $modules_remaining = mysqli_fetch_assoc(mysqli_query($conn, $to_learn_q))['remaining'];

    // Assessments remain based on chapters
    $to_take_q = "SELECT COUNT(*) as pending FROM assessment_chapters WHERE id NOT IN (SELECT chapter_id FROM assessment_scores WHERE user_id = '$uid' AND score >= $passing_score_assessment)";
    $assessments_pending = mysqli_fetch_assoc(mysqli_query($conn, $to_take_q))['pending'];

    $assessments_passed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT chapter_id) as passed FROM assessment_scores WHERE user_id = '$uid' AND score >= $passing_score_assessment"))['passed'];

    // --- UPDATED PROGRESS LOGIC ---
    // Count total modules (9)
    $total_modules_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM modules");
    $total_modules = mysqli_fetch_assoc($total_modules_res)['total'];

    // Count modules completed by user
    $completed_modules_res = mysqli_query($conn, "SELECT COUNT(DISTINCT module_id) as earned FROM progress WHERE student_id = '$uid' AND quiz_score >= $passing_score_module");
    $earned_count = mysqli_fetch_assoc($completed_modules_res)['earned'];

    // Calculate progress based on 9 modules
    $achievement_progress = ($total_modules > 0) ? ($earned_count / $total_modules) * 100 : 0;
}

// --- TEACHER REPORT FILTER LOGIC ---
// --- TEACHER REPORT FILTER LOGIC ---
$filter_query = "";
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

if($date_filter !== 'all'){
    switch($date_filter) {
        case 'day':   
            $filter_query = " AND DATE(s.date_taken) = CURDATE()"; 
            break;
        case 'week':  
            $filter_query = " AND s.date_taken >= DATE_SUB(NOW(), INTERVAL 1 WEEK)"; 
            break;
        case 'month': 
            $filter_query = " AND s.date_taken >= DATE_SUB(NOW(), INTERVAL 1 MONTH)"; 
            break;
        case 'year':  
            $filter_query = " AND s.date_taken >= DATE_SUB(NOW(), INTERVAL 1 YEAR)"; 
            break;
        case 'custom':
            if(!empty($start_date) && !empty($end_date)){
                // Sanitizing dates for the query
                $filter_query = " AND s.date_taken BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CodeQuest Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .pass { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .fail { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 25px; background: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #333; font-size: 13px; }
        
        .dashboard-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px; }
        .stat-card { padding: 20px; border-radius: 8px; text-align: center; color: white; }
        .bg-blue { background: #3498db; }
        .bg-green { background: #27ae60; }
        .bg-orange { background: #e67e22; }
        .stat-card h3 { margin: 0; font-size: 11px; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card h2 { margin: 8px 0 0; font-size: 28px; }

        .progress-bar-bg { background: #eee; border-radius: 10px; height: 20px; width: 100%; margin: 10px 0; }
        .progress-bar-fill { background: #27ae60; height: 100%; border-radius: 10px; transition: width 0.5s; }
        
        .section-title { margin-top: 30px; margin-bottom: 15px; color: #2c3e50; font-size: 1.2em; border-left: 5px solid #3498db; padding-left: 12px; font-weight: bold; }
        .filter-container { background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd; display: flex; align-items: flex-end; gap: 15px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>CodeQuest</h2>
        <a href="dashboard.php" class="active">Dashboard</a>
        <?php if($role == 'teacher'): ?>
            <a href="manage_modules.php">Manage Modules</a>
            <a href="manage_quizzes.php">Manage Quizzes</a>
            <a href="manage_assessments.php">Manage Assessments</a>
            <a href="generate_reports.php">Export PDF Reports</a>
        <?php else: ?>
            <a href="view_modules.php">Learn Modules</a>
            <a href="student_assessments.php">Take Assessment</a>
            <a href="certifications.php">Badges & Certificates</a>
        <?php endif; ?>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <p>Role: <strong><?php echo ucfirst($role); ?></strong></p>
        </div>

        <?php if($role == 'teacher'): ?>
            <h2 class="section-title">Teacher Insights</h2>
            
            <div class="filter-container">
    <form method="GET" action="dashboard.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <label><strong>Filter Logs:</strong></label>
        <select name="date_filter" onchange="this.form.submit()" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
            <option value="all" <?= $date_filter == 'all' ? 'selected' : '' ?>>All Time</option>
            <option value="day" <?= $date_filter == 'day' ? 'selected' : '' ?>>Today</option>
            <option value="week" <?= $date_filter == 'week' ? 'selected' : '' ?>>This Week</option>
            <option value="month" <?= $date_filter == 'month' ? 'selected' : '' ?>>This Month</option>
            <option value="year" <?= $date_filter == 'year' ? 'selected' : '' ?>>This Year</option>
            <option value="custom" <?= $date_filter == 'custom' ? 'selected' : '' ?>>Custom Range</option>
        </select>

        <?php if($date_filter == 'custom'): ?>
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required style="padding: 7px; border-radius: 4px; border: 1px solid #ccc;">
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required style="padding: 7px; border-radius: 4px; border: 1px solid #ccc;">
            <button type="submit" style="padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">Apply Range</button>
        <?php endif; ?>
        
        <?php if($date_filter != 'all'): ?>
            <a href="dashboard.php" style="font-size: 12px; color: #e74c3c; text-decoration: none;">Clear Filter</a>
        <?php endif; ?>
    </form>
</div>

            <div class="dashboard-grid">
                <div class="stat-card bg-blue"><h3>Total Students</h3><h2><?= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role='student'"))['t'] ?></h2></div>
                <div class="stat-card bg-green"><h3>Assessments Passed</h3><h2><?= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM assessment_scores s WHERE s.score >= $passing_score_assessment $filter_query"))['t'] ?></h2></div>
                <div class="stat-card bg-orange"><h3>Total Chapters</h3><h2><?= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM assessment_chapters"))['t'] ?></h2></div>
            </div>

            <h3 class="section-title">Student List & Achievements</h3>
<div class="card">
    <table>
        <thead><tr><th>Username</th><th>Modules Completed</th><th>Assessments Passed</th><th>Progress</th></tr></thead>
        <tbody>
            <?php
            $st_res = mysqli_query($conn, "SELECT id, username FROM users WHERE role='student'");
            while($st = mysqli_fetch_assoc($st_res)):
                $sid = $st['id'];
                $m_done = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM progress WHERE student_id='$sid' AND quiz_score >= 2"))['c'];
                $a_done = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM assessment_scores WHERE user_id='$sid' AND score >= 4"))['c'];
                $total_m = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM modules"))['t'];
                $prog = ($total_m > 0) ? ($m_done / $total_m) * 100 : 0;
            ?>
                <tr>
                    <td><?= htmlspecialchars($st['username']) ?></td>
                    <td><?= $m_done ?></td>
                    <td><?= $a_done ?></td>
                    <td><?= round($prog) ?>%</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<h3 class="section-title">Module Quiz Logs</h3>
<table>
    <thead>
        <tr>
            <th>Student</th>
            <th>Module Title</th>
            <th>Score</th>
            <th>Date</th>
            <th>Status</th> </tr>
    </thead>
    <tbody>
        <?php
        $module_filter = str_replace('s.date_taken', 's.date_completed', $filter_query);
        $m_q = "SELECT u.username, m.title, s.quiz_score, s.date_completed FROM progress s 
                JOIN users u ON s.student_id = u.id 
                JOIN modules m ON s.module_id = m.id 
                WHERE 1=1 $module_filter ORDER BY s.date_completed DESC";
        
        $m_res = mysqli_query($conn, $m_q);
        while($r = mysqli_fetch_assoc($m_res)): 
            // Logic: Pass if score is 2 or 3
            $is_pass = ($r['quiz_score'] >= $passing_score_module); 
        ?>
            <tr>
                <td><?= htmlspecialchars($r['username']) ?></td>
                <td><?= htmlspecialchars($r['title']) ?></td>
                <td><?= $r['quiz_score'] ?>/3</td>
                <td><?= date('M d, Y', strtotime($r['date_completed'])) ?></td>
                <td>
                    <span class="status-badge <?= $is_pass ? 'pass' : 'fail' ?>">
                        <?= $is_pass ? 'PASSED' : 'FAILED' ?>
                    </span>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

           <h3 class="section-title">Chapter Assessment Logs</h3>
<table>
    <thead>
        <tr>
            <th>Student</th>
            <th>Chapter Title</th>
            <th>Score</th>
            <th>Date</th>
            <th>Status</th> </tr>
    </thead>
    <tbody>
        <?php
        $a_q = "SELECT u.username, c.title, s.score, s.date_taken FROM assessment_scores s 
                JOIN users u ON s.user_id = u.id 
                JOIN assessment_chapters c ON s.chapter_id = c.id 
                WHERE 1=1 $filter_query ORDER BY s.date_taken DESC";
        
        $a_res = mysqli_query($conn, $a_q);
        while($r = mysqli_fetch_assoc($a_res)): 
            // Logic: Pass if score is 4 or 5
            $is_pass = ($r['score'] >= $passing_score_assessment); 
        ?>
            <tr>
                <td><?= htmlspecialchars($r['username']) ?></td>
                <td><?= htmlspecialchars($r['title']) ?></td>
                <td><?= $r['score'] ?>/5</td>
                <td><?= date('M d, Y', strtotime($r['date_taken'])) ?></td>
                <td>
                    <span class="status-badge <?= $is_pass ? 'pass' : 'fail' ?>">
                        <?= $is_pass ? 'PASSED' : 'FAILED' ?>
                    </span>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

        <?php else: ?>
            <h2 class="section-title">My Learning Progress</h2>
            <div class="dashboard-grid">
                <div class="stat-card bg-blue"><h3>Modules To Learn</h3><h2><?= $modules_remaining ?></h2></div>
                <div class="stat-card bg-green"><h3>Passed Chapters</h3><h2><?= $assessments_passed ?></h2></div>
                <div class="stat-card bg-orange"><h3>Pending Chapters</h3><h2><?= $assessments_pending ?></h2></div>
            </div>

            <div class="card">
                <h3>Course Completion</h3>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?= $achievement_progress ?>%;"></div></div>
                <p><?= round($achievement_progress) ?>% of all chapters completed</p>
            </div>

            <h3 class="section-title">Module Quiz Results</h3>
            <div class="card" style="margin-bottom: 30px;">
                <table>
                    <thead>
                        <tr><th>Module Title</th><th>Quiz Score</th><th>Date Completed</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $module_q = "SELECT m.title, p.quiz_score, p.date_completed FROM progress p JOIN modules m ON p.module_id = m.id WHERE p.student_id = '$uid' ORDER BY p.date_completed DESC";
                        $module_res = mysqli_query($conn, $module_q);
                        if(mysqli_num_rows($module_res) > 0):
                            while($m_row = mysqli_fetch_assoc($module_res)):
                                // Pass logic: Only fail if score is 0 or 1. (Score >= 2 passes)
                                $is_m_pass = ($m_row['quiz_score'] >= $passing_score_module);
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($m_row['title']) ?></td>
                                <td><?= $m_row['quiz_score'] ?> / 3</td>
                                <td><?= date('M d, Y', strtotime($m_row['date_completed'])) ?></td>
                                <td><span class="status-badge <?= $is_m_pass ? 'pass' : 'fail' ?>"><?= $is_m_pass ? 'PASSED' : 'FAILED' ?></span></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4" style="text-align:center;">No module quizzes found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h3 class="section-title">Chapter Assessment History</h3>
            <div class="card">
                <table>
                    <thead>
                        <tr><th>Chapter</th><th>Score</th><th>Date Taken</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $history_q = "SELECT c.title, s.score, s.date_taken FROM assessment_scores s JOIN assessment_chapters c ON s.chapter_id = c.id WHERE s.user_id = '$uid' ORDER BY s.date_taken DESC";
                        $history_res = mysqli_query($conn, $history_q);
                        if(mysqli_num_rows($history_res) > 0):
                            while($h = mysqli_fetch_assoc($history_res)):
                                $passed = ($h['score'] >= $passing_score_assessment);
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($h['title']) ?></strong></td>
                                <td><?= $h['score'] ?> / 5</td>
                                <td><?= date('M d, Y h:i A', strtotime($h['date_taken'])) ?></td>
                                <td><span class="status-badge <?= $passed ? 'pass' : 'fail' ?>"><?= $passed ? 'Passed' : 'Failed' ?></span></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4" style="text-align:center;">No assessments taken yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>