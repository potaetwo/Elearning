<?php
include 'db.php';
session_start();

if(isset($_POST['answer'])){
    $uid = $_SESSION['user_id'];
    $mid = $_POST['module_id'];
    $answers = $_POST['answer'];
    $score = 0;
    $total = count($answers);

    if($total > 0) {
        foreach($answers as $quiz_id => $user_ans){
            $stmt = $conn->prepare("SELECT answer FROM quizzes WHERE id = ?");
            $stmt->bind_param("i", $quiz_id);
            $stmt->execute();
            $correct = $stmt->get_result()->fetch_assoc();
           
            if(strtolower(trim($user_ans)) == strtolower(trim($correct['answer']))){
                $score++;
            }
        }
        $final_percent = ($score / $total) * 100;
    } else {
        $final_percent = 0;
    }

    $stmt = $conn->prepare("INSERT INTO progress (student_id, module_id, quiz_score, status) VALUES (?, ?, ?, 'completed')");
    $stmt->bind_param("iid", $uid, $mid, $final_percent);
    $stmt->execute();

    echo "<script>alert('You scored $final_percent%!'); window.location='dashboard.php';</script>";
}
?>