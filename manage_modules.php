<?php
include 'db.php';
session_start();

// Security: Only allow Teachers
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

// --- HANDLE DELETE ---
if(isset($_GET['delete_id'])){
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // Delete linked quizzes first to prevent foreign key errors
    mysqli_query($conn, "DELETE FROM quizzes WHERE module_id='$id'");
    mysqli_query($conn, "DELETE FROM modules WHERE id='$id'");
    
    header("Location: manage_modules.php?deleted=1");
    exit();
}

// --- HANDLE EDIT (UPDATE) ---
if(isset($_POST['update_module'])){
    $id = mysqli_real_escape_string($conn, $_POST['module_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);

    mysqli_query($conn, "UPDATE modules SET title='$title', content='$content' WHERE id='$id'");
    echo "<script>alert('Module updated successfully!'); window.location='manage_modules.php';</script>";
}

// --- HANDLE ADD (NEW MODULE) ---
if(isset($_POST['add_module'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    
    mysqli_query($conn, "INSERT INTO modules (title, content) VALUES ('$title', '$content')");
    echo "<script>alert('New module added!'); window.location='manage_modules.php';</script>";
}

$modules = mysqli_query($conn, "SELECT * FROM modules ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Modules - CodeQuest</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .module-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; margin-top: 15px; }
        .module-table th { background: #f8f9fa; padding: 15px; text-align: left; color: #333; font-size: 14px; border-bottom: 2px solid #eee; }
        .module-table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        .btn-edit { background: #3498db; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px; transition: 0.3s; }
        .btn-edit:hover { background: #2980b9; }
        .btn-delete { background: #e74c3c; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 13px; margin-left: 5px; transition: 0.3s; }
        .btn-delete:hover { background: #c0392b; }
        
        /* Modal Styling */
        .modal { display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 700px; max-width: 90%; box-shadow: 0 5px 20px rgba(0,0,0,0.3); }
        .modal-content label { display: block; margin: 15px 0 5px; font-weight: bold; color: #444; }
        .modal-content input, .modal-content textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-family: inherit; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>CodeQuest</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_modules.php" class="active">Manage Modules</a>
        <a href="manage_quizzes.php">Manage Quizzes</a>
        <a href="manage_assessments.php">Manage Assessments</a>
        <a href="generate_reports.php">Export PDF Reports</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h2 style="border-left: 5px solid #3498db; padding-left: 15px;">Module Management</h2>

        <div class="card">
            <h3>Create New Module</h3>
            <form method="POST">
                <input type="text" name="title" placeholder="Module Title (e.g., Chapter 2 Lesson 1)" required style="width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #ddd;">
                <textarea name="content" placeholder="Enter module lesson content or HTML here..." rows="5" required style="width: 100%; padding: 12px; border-radius: 6px; border: 1px solid #ddd;"></textarea>
                <button type="submit" name="add_module" style="background: #2ecc71; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; margin-top: 15px; width: 100%; font-weight: bold;">Add Module</button>
            </form>
        </div>

        <div class="card" style="margin-top: 30px;">
            <h3>Existing Modules</h3>
            <table class="module-table">
                <thead>
                    <tr>
                        <th>Module Title</th>
                        <th style="width: 220px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($modules)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                        <td style="text-align: center;">
                            <button class="btn-edit" onclick='editModule(<?php echo $row["id"]; ?>, <?php echo json_encode($row["title"]); ?>, <?php echo json_encode($row["content"]); ?>)'>
                                Edit
                            </button>
                            
                            <a href="manage_modules.php?delete_id=<?php echo $row['id']; ?>" 
                               class="btn-delete"
                               onclick="return confirm('Confirm deletion? This will permanently remove all quizzes for this module.')">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-top: 0;">Update Module</h3>
            <form method="POST">
                <input type="hidden" name="module_id" id="edit_id">
                
                <label>Title</label>
                <input type="text" name="title" id="edit_title" required>
                
                <label>Content</label>
                <textarea name="content" id="edit_content" rows="12" required></textarea>
                
                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <button type="submit" name="update_module" style="background: #3498db; color: white; border: none; padding: 12px; border-radius: 6px; flex: 1; cursor: pointer; font-weight: bold;">Save Changes</button>
                    <button type="button" onclick="closeModal()" style="background: #95a5a6; color: white; border: none; padding: 12px; border-radius: 6px; flex: 1; cursor: pointer; font-weight: bold;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editModule(id, title, content) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_content').value = content;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal if user clicks the dark background
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>