<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if(isset($_POST['register'])){
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $p = $_POST['password']; 
    $r = $_POST['role'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        echo "<script>alert('Username already taken!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $u, $p, $r);
        if($stmt->execute()) {
            echo "<script>alert('Registration Successful!'); window.location='login.php';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CodeQuest - Register</title>
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
            <p class="sub-title">Create your account to start learning</p>
        </div>
        
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter username" required>
            
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
            
            <label>Register as</label>
            <select name="role">
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>
            
            <button type="submit" name="register" class="btn-block">Sign Up</button>
        </form>

        <div class="card-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>
