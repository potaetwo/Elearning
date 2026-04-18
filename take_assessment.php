<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$chapter_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;

// Fetch Chapter Title
$chapter_q = mysqli_query($conn, "SELECT title FROM assessment_chapters WHERE id = '$chapter_id'");
$chapter = mysqli_fetch_assoc($chapter_q);

if(!$chapter) {
    header("Location: student_assessments.php");
    exit();
}

// Fetch 5 random questions from the pool
$query = "SELECT * FROM assessments WHERE chapter_id = '$chapter_id' ORDER BY RAND() LIMIT 5";
$questions = mysqli_query($conn, $query);
$count = mysqli_num_rows($questions);

// Handle Submission
if(isset($_POST['submit_exam'])) {
    $score = 0;
    $answers = isset($_POST['answers']) ? $_POST['answers'] : [];

    foreach($answers as $q_id => $user_ans) {
        $q_id = mysqli_real_escape_string($conn, $q_id);
        $check = mysqli_query($conn, "SELECT answer FROM assessments WHERE id = '$q_id'");
        $correct_row = mysqli_fetch_assoc($check);
        
        if($correct_row) {
            $correct = $correct_row['answer'];
            if(trim(strtolower($user_ans)) == trim(strtolower($correct))) {
                $score++;
            }
        }
    }

    // Save Score to Database
    $save_score = "INSERT INTO assessment_scores (user_id, chapter_id, score, date_taken) VALUES ('$user_id', '$chapter_id', '$score', NOW())";
    mysqli_query($conn, $save_score);
    
    echo "<script>alert('Assessment Finished! Your score: $score/5'); window.location='student_assessments.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Taking Exam: <?php echo htmlspecialchars($chapter['title']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .btn-container { display: flex; gap: 15px; margin-top: 20px; }
        .btn-submit { background: #2ecc71; color: white; padding: 15px; border: none; cursor: pointer; font-size: 1.1em; flex: 2; border-radius: 5px; }
        .btn-back { background: #e74c3c; color: white; padding: 15px; text-decoration: none; text-align: center; font-size: 1.1em; flex: 1; border-radius: 5px; }
        .btn-submit:hover { background: #27ae60; }
        .btn-back:hover { background: #c0392b; }
    </style>
</head>
<body>
    <div class="main-content" style="max-width: 800px; margin: auto; padding: 20px;">
        <h2>Exam: <?php echo htmlspecialchars($chapter['title']); ?></h2>
        <hr>
        
        <?php if($count < 5): ?>
            <div class="card" style="border-left: 5px solid red; padding: 20px; background: #fff;">
                <p style="color: red; font-weight: bold;">
                    This assessment is currently unavailable. 
                    (Requires at least 5 questions in the database, only found <?php echo $count; ?>).
                </p>
                <a href="student_assessments.php" style="color: #3498db; text-decoration: underline;">Back to Assessments</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <?php $i = 1; while($q = mysqli_fetch_assoc($questions)): ?>
                    <div class="card" style="margin-bottom: 20px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <p><strong>Question <?php echo $i++; ?>:</strong></p>
                        <p style="font-size: 1.1em; margin-bottom: 15px;"><?php echo htmlspecialchars($q['question']); ?></p>
                        <input type="text" name="answers[<?php echo $q['id']; ?>]" required 
                               placeholder="Type your answer here..." 
                               style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>
                <?php endwhile; ?>
                
                <div class="btn-container">
                    <button type="submit" name="submit_exam" class="btn-submit" 
                            onclick="return confirm('Are you sure you want to submit your exam?')">
                        Submit Exam
                    </button>

                    <a href="student_assessments.php" class="btn-back" 
                       onclick="return confirm('Are you sure? Your progress on this exam will be lost.')">
                        Go Back
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>