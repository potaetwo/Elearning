<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php"); exit();
}

// --- FILTER LOGIC ---
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Function to generate SQL based on specific date column
function getFilterSQL($filter, $start, $end, $column) {
    if($filter == 'day')   return " AND DATE($column) = CURDATE()";
    if($filter == 'week')  return " AND $column >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
    if($filter == 'month') return " AND $column >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    if($filter == 'year')  return " AND $column >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    if($filter == 'custom' && !empty($start) && !empty($end)){
        return " AND $column BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
    }
    return "";
}

// Generate two filters because the tables use different date column names
$module_filter = getFilterSQL($date_filter, $start_date, $end_date, 'p.date_completed');
$assessment_filter = getFilterSQL($date_filter, $start_date, $end_date, 's.date_taken');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity & Achievements Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f4f7f6; font-family: sans-serif; padding: 20px; }
        .report-wrapper { max-width: 1000px; margin: auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px; }
        th, td { padding: 10px; border: 1px solid #eee; text-align: left; }
        th { background: #2c3e50; color: white; }
        .section-header { background: #f8f9fa; padding: 10px; border-left: 4px solid #3498db; margin: 20px 0 10px 0; font-weight: bold; }
        .status-pass { color: #27ae60; font-weight: bold; }
        .status-fail { color: #c0392b; font-weight: bold; }
        @media print { .no-print { display: none; } .report-wrapper { box-shadow: none; margin: 0; width: 100%; } }
    </style>
</head>
<body>

<div class="report-wrapper">
    <div class="no-print" style="margin-bottom: 20px;">
        <a href="dashboard.php" style="color: #3498db; text-decoration: none;">← Back to Dashboard</a> | 
        <button onclick="window.print()" style="cursor:pointer; padding: 5px 15px;">Print to PDF</button>
    </div>

    <div style="text-align:center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px;">
        <h1>CodeQuest Learning Management</h1>
        <h2>Detailed Activity & Achievements Report</h2>
        <p>Filter: <strong><?= ucfirst($date_filter) ?></strong> | Generated: <?= date('M d, Y') ?></p>
    </div>

    <div class="section-header">Module Quiz Performance (Passing: 2/3)</div>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Module Title</th>
                <th>Score</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $m_sql = "SELECT u.username, m.title, p.quiz_score, p.date_completed 
                      FROM progress p 
                      JOIN users u ON p.student_id = u.id 
                      JOIN modules m ON p.module_id = m.id 
                      WHERE 1=1 $module_filter ORDER BY p.date_completed DESC";
            $m_res = mysqli_query($conn, $m_sql);

            if(mysqli_num_rows($m_res) > 0) {
                while($l = mysqli_fetch_assoc($m_res)): 
                    $is_passed = ($l['quiz_score'] >= 2);
                ?>
                    <tr>
                        <td><?= htmlspecialchars($l['username']) ?></td>
                        <td><?= htmlspecialchars($l['title']) ?></td>
                        <td><?= $l['quiz_score'] ?> / 3</td>
                        <td><?= date('M d, Y', strtotime($l['date_completed'])) ?></td>
                        <td class="<?= $is_passed ? 'status-pass' : 'status-fail' ?>">
                            <?= $is_passed ? "PASSED" : "FAILED" ?>
                        </td>
                    </tr>
                <?php endwhile;
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>No module activity found.</td></tr>";
            } ?>
        </tbody>
    </table>

    <div class="section-header">Chapter Assessment Performance (Passing: 3/5)</div>
<table>
    <thead>
        <tr>
            <th>Student</th>
            <th>Chapter Title</th>
            <th>Score</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $a_sql = "SELECT u.username, c.title, s.score, s.date_taken 
                  FROM assessment_scores s 
                  JOIN users u ON s.user_id = u.id 
                  JOIN assessment_chapters c ON s.chapter_id = c.id 
                  WHERE 1=1 $assessment_filter ORDER BY s.date_taken DESC";
        $a_res = mysqli_query($conn, $a_sql);

        if(mysqli_num_rows($a_res) > 0) {
            while($l = mysqli_fetch_assoc($a_res)): 
                // Updated logic: Pass if score is 3, 4, or 5
                $is_passed = ($l['score'] >= 3); 
            ?>
                <tr>
                    <td><?= htmlspecialchars($l['username']) ?></td>
                    <td><?= htmlspecialchars($l['title']) ?></td>
                    <td><?= $l['score'] ?> / 5</td>
                    <td><?= date('M d, Y', strtotime($l['date_taken'])) ?></td>
                    <td class="<?= $is_passed ? 'status-pass' : 'status-fail' ?>">
                        <?= $is_passed ? "PASSED" : "FAILED" ?>
                    </td>
                </tr>
            <?php endwhile;
        } else {
            echo "<tr><td colspan='5' style='text-align:center;'>No assessment activity found.</td></tr>";
        } ?>
    </tbody>
</table>

    <div style="margin-top: 50px; text-align: right; font-size: 12px; color: #7f8c8d;" class="only-print">
        <p>Report End - Confidential Student Data</p>
    </div>
</div>

</body>
</html>