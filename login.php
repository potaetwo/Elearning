<?php 
include 'db.php'; 
session_start(); 

if(isset($_POST['login'])){
    $u = $_POST['username']; 
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
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="notion-page-container">
        <div class="page-header">
            <h1 class="main-title">CodeQuest: A journey for coder!</h1>
        </div>

        <div class="notion-card">
            <div class="card-header">
                <span class="icon">🚀</span>
                <h2>Login</h2>
            </div>
            
            <form method="POST">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Enter your username..." required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" name="login" class="btn-block">Continue</button>
            </form>
            
            <div class="card-footer">
                <p>New here? <a href="register.php">Create account</a></p>
            </div>
        </div>
    </div>
</body>
</html>
