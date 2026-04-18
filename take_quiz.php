<?php
include 'db.php';
session_start();

// Security: Only allow logged-in students
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$module_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;

// 1. Fetch Module Details
$module_query = mysqli_query($conn, "SELECT title FROM modules WHERE id = '$module_id'");
$module = mysqli_fetch_assoc($module_query);

if(!$module) {
    die("Module not found.");
}

// 2. Fetch 3 random questions from the pool
$quiz_query = mysqli_query($conn, "SELECT * FROM quizzes WHERE module_id = '$module_id' ORDER BY RAND() LIMIT 3");
$count = mysqli_num_rows($quiz_query);

// Handle Quiz Submission
if(isset($_POST['submit_quiz'])) {
    $score = 0;
    $total = 3;
    $answers = isset($_POST['answers']) ? $_POST['answers'] : [];

    foreach($answers as $quiz_id => $user_answer) {
        $quiz_id = mysqli_real_escape_string($conn, $quiz_id);
        $check_query = mysqli_query($conn, "SELECT answer FROM quizzes WHERE id = '$quiz_id'");
        $result = mysqli_fetch_assoc($check_query);
        $correct_answer = $result['answer'];

        if(trim(strtolower($user_answer)) == trim(strtolower($correct_answer))) {
            $score++;
        }
    }

    // Save Progress - This is where your previous error occurred. 
    // Ensure 'user_id' exists in your 'progress' table.
    // --- FIXED SAVE PROGRESS ---
    // We use student_id and quiz_score to match your dashboard's expectations
    $save_query = "INSERT INTO progress (student_id, module_id, quiz_score, date_completed) 
                   VALUES ('$user_id', '$module_id', '$score', NOW()) 
                   ON DUPLICATE KEY UPDATE 
                   quiz_score = IF(quiz_score < '$score', '$score', quiz_score),
                   date_completed = NOW()";
    
    if(mysqli_query($conn, $save_query)) {
        echo "<script>alert('You scored $score/$total!'); window.location='dashboard.php';</script>";
    } else {
        // This will tell you exactly if a column name is still wrong
        echo "Error updating progress: " . mysqli_error($conn);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz: <?php echo htmlspecialchars($module['title']); ?> - CodeQuest</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .quiz-container { max-width: 800px; margin: 40px auto; padding: 20px; }
        .question-card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .question-text { font-size: 1.2rem; margin-bottom: 15px; color: #2c3e50; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        
        .action-buttons { display: flex; gap: 15px; margin-top: 20px; }
        .btn-submit { background: #2ecc71; flex: 2; padding: 15px; border: none; color: white; border-radius: 5px; cursor: pointer; font-size: 1.1rem; transition: 0.3s; }
        .btn-back { background: #e74c3c; flex: 1; padding: 15px; text-decoration: none; color: white; border-radius: 5px; text-align: center; font-size: 1.1rem; transition: 0.3s; }
        .btn-submit:hover { background: #27ae60; }
        .btn-back:hover { background: #c0392b; }
    </style>
</head>
<body>
    <div class="quiz-container">
        <h2>Quiz: <?php echo htmlspecialchars($module['title']); ?></h2>
        <hr>

        <?php if($count < 3): ?>
            <div class="card" style="text-align: center; color: #e74c3c; padding: 20px; background: #fff; border-radius: 8px;">
                <h3>⚠️ Quiz Unavailable</h3>
                <p>This module requires at least 3 questions to start.</p>
                <br>
                <a href="dashboard.php" class="btn-back" style="display:inline-block; width:auto; background:#3498db; padding: 10px 20px;">Back to Dashboard</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <?php $i = 1; while($q = mysqli_fetch_assoc($quiz_query)): ?>
                    <div class="question-card">
                        <p class="question-text"><strong>Question <?php echo $i++; ?>:</strong></p>
                        <p><?php echo htmlspecialchars($q['question']); ?></p>
                        <input type="text" name="answers[<?php echo $q['id']; ?>]" placeholder="Type your answer here..." required autocomplete="off">
                    </div>
                <?php endwhile; ?>

                <div class="action-buttons">
                    <button type="submit" name="submit_quiz" class="btn-submit">Submit My Answers</button>
                    
                    <a href="dashboard.php" class="btn-back" 
                       onclick="return confirm('Are you sure you want to go back? Your current progress in this quiz session will be lost.')">
                        Go Back
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>