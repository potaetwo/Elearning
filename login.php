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
<body style="display: flex; justify-content: center; align-items: center; height: 100vh;">
    <div class="card" style="width: 400px;">
        <h2 style="text-align: center; margin-bottom: 20px;">Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login" class="btn-block">Login</button>
        </form>
        <p style="margin-top:15px;">New here? <a href="index.php">Create account</a></p>
    </div>
</body>
</html>
