<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Security: Only allow Teachers
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

// --- HANDLE DELETE QUIZ ---
if(isset($_GET['delete_id'])){
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM quizzes WHERE id='$id'");
    header("Location: manage_quizzes.php?deleted=1");
    exit();
}

// --- HANDLE EDIT (UPDATE) ---
if(isset($_POST['update_quiz'])){
    $id = mysqli_real_escape_string($conn, $_POST['quiz_id']);
    $module_id = mysqli_real_escape_string($conn, $_POST['module_id']);
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $answer = mysqli_real_escape_string($conn, $_POST['answer']);

    mysqli_query($conn, "UPDATE quizzes SET module_id='$module_id', question='$question', answer='$answer' WHERE id='$id'");
    echo "<script>alert('Quiz updated successfully!'); window.location='manage_quizzes.php';</script>";
}

// --- HANDLE MULTI-ADD ---
if(isset($_POST['add_quiz'])){
    $module_id = mysqli_real_escape_string($conn, $_POST['module_id']);
    $questions = $_POST['question_text']; 
    $answers = $_POST['answer_text']; 
    $count = 0;

    for ($i = 0; $i < 3; $i++) {
        if (!empty($questions[$i])) {
            $q = mysqli_real_escape_string($conn, $questions[$i]);
            $a = mysqli_real_escape_string($conn, $answers[$i]);
            mysqli_query($conn, "INSERT INTO quizzes (module_id, question, answer) VALUES ('$module_id', '$q', '$a')");
            $count++;
        }
    }
    echo "<script>alert('$count questions added!'); window.location='manage_quizzes.php';</script>";
}

// Fetch data
$modules = mysqli_query($conn, "SELECT * FROM modules");
$quizzes = mysqli_query($conn, "SELECT quizzes.*, modules.title as module_name FROM quizzes 
                                JOIN modules ON quizzes.module_id = modules.id ORDER BY quizzes.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Quizzes - CodeQuest</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .quiz-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        .quiz-table th { background: #f8f9fa; padding: 12px; text-align: left; font-size: 14px; border-bottom: 2px solid #eee; }
        .quiz-table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; vertical-align: middle; }
        
        .btn-edit { background: #3498db; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-delete { background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 13px; margin-left: 5px; display: inline-block; }
        
        .modal { display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 25px; border-radius: 10px; width: 500px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .modal-content label { display: block; margin: 10px 0 5px; font-weight: bold; }
        .modal-content input, .modal-content select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>CodeQuest</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_modules.php">Manage Modules</a>
        <a href="manage_quizzes.php" class="active">Manage Quizzes</a>
        <a href="manage_assessments.php">Manage Assessments</a>
        <a href="generate_reports.php">Export PDF Reports</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h2>Quiz Management</h2>

        <div class="card">
            <h3>Add 3 New Questions</h3>
            <form method="POST">
                <select name="module_id" required style="width: 100%; padding: 10px; margin-bottom: 20px;">
                    <option value="">-- Select Module --</option>
                    <?php mysqli_data_seek($modules, 0); while($m = mysqli_fetch_assoc($modules)): ?>
                        <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['title']); ?></option>
                    <?php endwhile; ?>
                </select>

                <?php for($i=1; $i<=3; $i++): ?>
                <div style="border-bottom: 1px solid #eee; margin-bottom: 15px; padding-bottom: 10px; display: flex; gap: 10px;">
                    <input type="text" name="question_text[]" placeholder="Question <?php echo $i; ?>" style="flex: 2; padding: 8px;" <?php echo ($i==1)?'required':''; ?>>
                    <input type="text" name="answer_text[]" placeholder="Answer" style="flex: 1; padding: 8px;" <?php echo ($i==1)?'required':''; ?>>
                </div>
                <?php endfor; ?>

                <button type="submit" name="add_quiz" class="btn-block" style="background: #2ecc71; color: white; padding: 12px; border: none; cursor: pointer; width: 100%; border-radius: 5px;">Save Questions</button>
            </form>
        </div>

        <div class="card" style="margin-top: 25px;">
            <h3>Existing Quizzes</h3>
            <table class="quiz-table">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Question</th>
                        <th style="width: 180px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($quizzes)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['module_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['question']); ?></td>
                        <td style="text-align: center;">
                            <button class="btn-edit" onclick='openEdit(<?php echo $row["id"]; ?>, <?php echo $row["module_id"]; ?>, <?php echo json_encode($row["question"]); ?>, <?php echo json_encode($row["answer"]); ?>)'>
                                Edit
                            </button>
                            
                            <a href="manage_quizzes.php?delete_id=<?php echo $row['id']; ?>" 
                               class="btn-delete"
                               onclick="return confirm('Delete this question?')">
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
            <h3>Edit Question</h3>
            <form method="POST">
                <input type="hidden" name="quiz_id" id="edit_quiz_id">
                
                <label>Module</label>
                <select name="module_id" id="edit_module_id" required>
                    <?php mysqli_data_seek($modules, 0); while($m = mysqli_fetch_assoc($modules)): ?>
                        <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['title']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label>Question</label>
                <input type="text" name="question" id="edit_question" required>
                
                <label>Answer</label>
                <input type="text" name="answer" id="edit_answer" required>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="update_quiz" style="flex:1; background:#3498db; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">Update</button>
                    <button type="button" onclick="closeModal()" style="flex:1; background:#95a5a6; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEdit(id, moduleId, question, answer) {
            document.getElementById('edit_quiz_id').value = id;
            document.getElementById('edit_module_id').value = moduleId;
            document.getElementById('edit_question').value = question;
            document.getElementById('edit_answer').value = answer;
            document.getElementById('editModal').style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) closeModal();
        }
    </script>
</body>
</html>