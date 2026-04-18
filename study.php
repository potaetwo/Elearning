<?php
include 'db.php';
session_start();

if(!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    header("Location: view_modules.php");
    exit();
}

$mid = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM modules WHERE id = ?");
$stmt->bind_param("i", $mid);
$stmt->execute();
$module = $stmt->get_result()->fetch_assoc();

if(!$module) {
    echo "Module not found.";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="sidebar">
        <h2>CodeQuest</h2>
        <a href="view_modules.php">Back to Modules</a>
    </div>
    <div class="main-content">
        <div class="container-small">
            <h1><?php echo htmlspecialchars($module['title']); ?></h1>
            <div class="card" style="white-space: pre-wrap; line-height: 1.6;">
                <?php echo htmlspecialchars($module['content']); ?>
            </div>
            <a href="take_quiz.php?id=<?php echo $mid; ?>" class="btn-block" style="text-decoration:none; display:inline-block; text-align:center;">Take Quiz</a>
        </div>
    </div>
</body>
</html>