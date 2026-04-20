<?php 
include 'db.php'; 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(isset($_POST['login'])){
    $u = mysqli_real_escape_string($conn, $_POST['username']); 
    $p = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $u, $p);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit();
    } else { 
        echo "<script>alert('Invalid credentials');</script>"; 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CodeQuest - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="bg-visuals">
        <div class="bg-grid"></div>
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="notion-card">
        <div class="header-section">
            <h1 class="main-title">CodeQuest</h1>
            <p class="sub-title">Welcome back! Please login to your account.</p>
        </div>
        
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter your username" required>
            
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
            
            <button type="submit" name="login" class="btn-block">Login to Quest</button>
        </form>

        <div class="card-footer">
            New here? <a href="index.php">Create an account</a>
        </div>
    </div>
</body>
</html>
