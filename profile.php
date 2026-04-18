<?php
include 'db.php';
session_start();

if(!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$uid = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$uid'");
$user = mysqli_fetch_assoc($query);

if(isset($_POST['update_profile'])){
    $new_user = mysqli_real_escape_string($conn, $_POST['username']);

    if(!empty($_FILES['image']['name'])){
        $img_name = time() . '_' . $_FILES['image']['name'];
        if(move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img_name)) {
            mysqli_query($conn, "UPDATE users SET profile_pic='$img_name' WHERE id='$uid'");
        }
    }

    mysqli_query($conn, "UPDATE users SET username='$new_user' WHERE id='$uid'");
    echo "<script>alert('Profile Updated!'); window.location='profile.php';</script>";
}

if(isset($_POST['change_pass'])){
    $old = $_POST['old_pass'];
    $new = $_POST['new_pass'];
    $confirm = $_POST['confirm_pass'];

    if($old !== $user['password']){
        echo "<script>alert('Old password incorrect!');</script>";
    } elseif($new !== $confirm){
        echo "<script>alert('New passwords do not match!');</script>";
    } else {
        mysqli_query($conn, "UPDATE users SET password='$new' WHERE id='$uid'");
        echo "<script>alert('Password changed successfully!');</script>";
    }
}

if(isset($_POST['delete_acc'])){
    mysqli_query($conn, "DELETE FROM users WHERE id='$uid'");
    session_destroy();
    echo "<script>alert('Account deleted.'); window.location='register.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - CodeQuest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar">
        <h2>CodeQuest</h2>
        <a href="dashboard.php" class="active">Back to Home</a>
        
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="container-small">
            
            <div class="card">
                <h3>Edit Profile</h3>
                <div style="text-align: center; margin-bottom: 20px;">
                <?php 
                $pic = (!empty($user['profile_pic']) && file_exists("uploads/".$user['profile_pic'])) 
                    ? $user['profile_pic'] 
                    : 'default.png'; 
                ?>
                <img src="uploads/<?php echo $pic; ?>" 
                style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #3498db;" 
                alt="Profile">
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    
                    <label>Profile Picture</label>
                    <input type="file" name="image" accept="image/*">
                    
                    <button type="submit" name="update_profile" class="btn-block">Save Changes</button>
                </form>
            </div>

            <div class="card">
                <h3>Security Settings</h3>
                <form method="POST">
                    <label>Old Password</label>
                    <input type="password" name="old_pass" placeholder="Enter current password" required>
                    
                    <label>New Password</label>
                    <input type="password" name="new_pass" placeholder="Enter new password" required>
                    
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_pass" placeholder="Confirm new password" required>
                    
                    <button type="submit" name="change_pass" class="btn-block" style="background:#2ecc71;">Update Password</button>
                </form>
            </div>

            <div class="card" style="border: 1px solid #fab1a0;">
                <h3 style="color: #e74c3c;">Danger Zone</h3>
                <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Warning: Deleting your account will remove all progress.</p>
                <form method="POST" onsubmit="return confirm('Are you absolutely sure? This cannot be undone.');">
                    <button type="submit" name="delete_acc" class="btn-block" style="background: #e74c3c;">Delete My Account</button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>