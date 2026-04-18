<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Redirect to dashboard if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if(isset($_POST['register'])){
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $p = $_POST['password']; // Note: In a real app, use password_hash()
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to CodeQuest</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            justify-content: center;
            align-items: center;
            height: 100vh;
            display: flex;
            background-color: #f4f7f6;
            margin: 0;
        }
        .register-footer {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }
        .card {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; /* Ensures padding doesn't break width */
        }
        label {
            font-weight: bold;
            color: #333;
        }
        .btn-block {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-block:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 10px; color: #2c3e50;">CodeQuest</h2>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 25px;">Create your account to start learning</p>
        
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" placeholder="Choose a username" required>
            
            <label>Password</label>
            <input type="password" name="password" placeholder="Create a password" required>
            
            <label>Register as</label>
            <select name="role">
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>
            
            <button type="submit" name="register" class="btn-block">Sign Up</button>
        </form>

        <div class="register-footer">
            <p>Already have an account? <a href="login.php" style="color: #3498db; text-decoration: none; font-weight: bold;">Login here</a></p>
        </div>
    </div>
</body>
</html>