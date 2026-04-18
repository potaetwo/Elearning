<?php
session_start();

if (isset($_POST['confirm_logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh;">
    <div class="card" style="width: 400px; text-align: center;">
        <h2>Logging Out?</h2>
        <p style="margin-bottom: 20px; color: #7f8c8d;">Are you sure you want to end your session?</p>
        
        <form method="POST">
            <button type="submit" name="confirm_logout" class="btn-block" style="background: #e74c3c; margin-bottom: 10px;">Confirm</button>
            <a href="dashboard.php" style="text-decoration: none; color: #3498db; font-weight: bold;">No, stay logged in</a>
        </form>
    </div>
</body>
</html>