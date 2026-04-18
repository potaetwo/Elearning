<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php"); exit();
}

// --- 1. HANDLE DELETE INDIVIDUAL QUESTION ---
if(isset($_GET['delete_q_id'])){
    $q_id = mysqli_real_escape_string($conn, $_GET['delete_q_id']);
    mysqli_query($conn, "DELETE FROM assessments WHERE id='$q_id'");
    header("Location: manage_assessments.php?success=deleted");
    exit();
}

// --- 2. HANDLE DELETE CHAPTER ---
if(isset($_GET['delete_chapter'])){
    $id = mysqli_real_escape_string($conn, $_GET['delete_chapter']);
    $delete_query = "DELETE FROM assessment_chapters WHERE id='$id'";
    if(mysqli_query($conn, $delete_query)){
        header("Location: manage_assessments.php?success=chapter_deleted");
        exit();
    }
}

// --- 3. HANDLE UPDATE CHAPTER TITLE (NEW FEATURE) ---
if(isset($_POST['update_chapter'])){
    $c_id = mysqli_real_escape_string($conn, $_POST['chapter_id']);
    $new_title = mysqli_real_escape_string($conn, $_POST['new_title']);
    
    mysqli_query($conn, "UPDATE assessment_chapters SET title='$new_title' WHERE id='$c_id'");
    header("Location: manage_assessments.php?success=chapter_updated");
    exit();
}

// --- 4. HANDLE EDIT QUESTION SUBMISSION ---
if(isset($_POST['update_assessment'])){
    $id = mysqli_real_escape_string($conn, $_POST['edit_id']);
    $chapter_id = mysqli_real_escape_string($conn, $_POST['edit_chapter']);
    $question = mysqli_real_escape_string($conn, $_POST['edit_question']);
    $answer = mysqli_real_escape_string($conn, $_POST['edit_answer']);

    $update_query = "UPDATE assessments SET chapter_id='$chapter_id', question='$question', answer='$answer' WHERE id='$id'";
    
    if(mysqli_query($conn, $update_query)){
        header("Location: manage_assessments.php?success=updated");
        exit();
    }
}

// --- 5. HANDLE ADD CHAPTER ---
if(isset($_POST['add_chapter'])){
    $title = mysqli_real_escape_string($conn, $_POST['chapter_title']);
    mysqli_query($conn, "INSERT INTO assessment_chapters (title) VALUES ('$title')");
    header("Location: manage_assessments.php?success=chapter");
    exit();
}

// --- 6. HANDLE MULTI-ADD QUESTIONS ---
if(isset($_POST['add_assessment'])){
    $chapter_id = $_POST['a_module_id']; 
    $questions = $_POST['q_text'];
    $answers = $_POST['a_text'];
    
    foreach($questions as $key => $val) {
        if(!empty($val)) {
            $q = mysqli_real_escape_string($conn, $val);
            $a = mysqli_real_escape_string($conn, $answers[$key]);
            mysqli_query($conn, "INSERT INTO assessments (chapter_id, question, answer) VALUES ('$chapter_id', '$q', '$a')");
        }
    }
    header("Location: manage_assessments.php?success=questions");
    exit();
}

// Fetch Data
$chapters = mysqli_query($conn, "SELECT * FROM assessment_chapters ORDER BY id ASC");
$assessments = mysqli_query($conn, "SELECT assessments.*, assessment_chapters.title as chapter_name 
                                    FROM assessments 
                                    JOIN assessment_chapters ON assessments.chapter_id = assessment_chapters.id 
                                    ORDER BY assessments.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Assessments - CodeQuest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar">
        <h2>CodeQuest</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_modules.php">Manage Modules</a>
        <a href="manage_quizzes.php">Manage Quizzes</a>
        <a href="manage_assessments.php" class="active">Manage Assessments</a>
        <a href="generate_reports.php">Export PDF Reports</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h2>Assessment Panel (Exams)</h2>

        <?php if(isset($_GET['edit_q'])): 
            $edit_id = mysqli_real_escape_string($conn, $_GET['edit_q']);
            $edit_res = mysqli_query($conn, "SELECT * FROM assessments WHERE id='$edit_id'");
            $edit_data = mysqli_fetch_assoc($edit_res);
        ?>
            <div class="card" style="border-left: 5px solid #3498db; margin-bottom: 25px; background: #f0f7ff;">
                <h3>Edit Question</h3>
                <form method="POST">
                    <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
                    
                    <label>Chapter:</label>
                    <select name="edit_chapter" required style="width:100%; padding:10px; margin-bottom:10px;">
                        <?php mysqli_data_seek($chapters, 0); while($c = mysqli_fetch_assoc($chapters)): ?>
                            <option value="<?= $c['id'] ?>" <?= ($c['id'] == $edit_data['chapter_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['title']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label>Question Text:</label>
                    <input type="text" name="edit_question" value="<?= htmlspecialchars($edit_data['question']) ?>" required style="width:100%; margin-bottom:10px;">
                    
                    <label>Correct Answer:</label>
                    <input type="text" name="edit_answer" value="<?= htmlspecialchars($edit_data['answer']) ?>" required style="width:100%; margin-bottom:15px;">
                    
                    <button type="submit" name="update_assessment" class="btn-block" style="background:#3498db;">Update Question</button>
                    <a href="manage_assessments.php" style="display:block; text-align:center; margin-top:10px; color:gray; text-decoration:none;">Cancel Edit</a>
                </form>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
            
            <div class="card">
                <h3>1. Manage Chapters</h3>
                
                <?php if(isset($_GET['edit_chapter_id'])): 
                    $c_id = mysqli_real_escape_string($conn, $_GET['edit_chapter_id']);
                    $c_res = mysqli_query($conn, "SELECT * FROM assessment_chapters WHERE id='$c_id'");
                    $c_data = mysqli_fetch_assoc($c_res);
                ?>
                    <form method="POST" style="margin-bottom: 20px; padding: 10px; background: #fff9e6; border-radius: 5px; border: 1px solid #f1c40f;">
                        <p style="margin-top:0; font-size: 13px; color: #7f8c8d;">Editing Chapter Title:</p>
                        <input type="hidden" name="chapter_id" value="<?= $c_data['id'] ?>">
                        <input type="text" name="new_title" value="<?= htmlspecialchars($c_data['title']) ?>" required>
                        <button type="submit" name="update_chapter" class="btn-block" style="background:#f39c12; margin-bottom: 5px;">Update Title</button>
                        <a href="manage_assessments.php" style="display:block; text-align:center; font-size:12px; color:gray; text-decoration:none;">Cancel</a>
                    </form>
                <?php else: ?>
                    <form method="POST" style="margin-bottom: 20px;">
                        <input type="text" name="chapter_title" placeholder="e.g. Midterm Exam" required>
                        <button type="submit" name="add_chapter" class="btn-block" style="background:#8e44ad;">Create Chapter</button>
                    </form>
                <?php endif; ?>

                <h4>Existing Chapters</h4>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #eee; padding: 10px; border-radius: 5px;">
                    <?php mysqli_data_seek($chapters, 0); while($c = mysqli_fetch_assoc($chapters)): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding-bottom: 5px; border-bottom: 1px solid #f9f9f9;">
                            <span style="font-size: 14px;"><?= htmlspecialchars($c['title']) ?></span>
                            <div>
                                <a href="manage_assessments.php?edit_chapter_id=<?= $c['id'] ?>" 
                                   style="color: #3498db; text-decoration: none; font-size: 11px; font-weight: bold; margin-right: 10px;">[Edit]</a>
                                
                                <a href="manage_assessments.php?delete_chapter=<?= $c['id'] ?>" 
                                   onclick="return confirm('Deleting this chapter will also delete all questions inside it. Continue?')" 
                                   style="color: #e74c3c; text-decoration: none; font-size: 11px; font-weight: bold;">[Delete]</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="card">
                <h3>2. Add Questions to Chapter</h3>
                <form method="POST">
                    <select name="a_module_id" required style="width:100%; padding:10px; margin-bottom:15px;">
                        <option value="">-- Select Assessment Chapter --</option>
                        <?php mysqli_data_seek($chapters, 0); while($m = mysqli_fetch_assoc($chapters)): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <?php for($i=1; $i<=2; $i++): ?>
                        <div style="background:#f9f9f9; padding:10px; margin-bottom:10px; border-radius:5px;">
                            <input type="text" name="q_text[]" placeholder="Question <?= $i ?>">
                            <input type="text" name="a_text[]" placeholder="Answer <?= $i ?>">
                        </div>
                    <?php endfor; ?>
                    <button type="submit" name="add_assessment" class="btn-block" style="background:#2ecc71;">Save Questions</button>
                </form>
            </div>
        </div>

        <div class="card" style="margin-top:20px;">
            <h3>Exam Question Bank</h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f1f2f6; text-align:left;">
                        <th style="padding:10px;">Exam Chapter</th>
                        <th style="padding:10px;">Question</th>
                        <th style="padding:10px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($assessments)): ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding:10px;"><strong><?= htmlspecialchars($row['chapter_name']) ?></strong></td>
                        <td style="padding:10px;"><?= htmlspecialchars($row['question']) ?></td>
                        <td style="padding:10px;">
                            <a href="?edit_q=<?= $row['id'] ?>" style="color:#3498db; margin-right:10px; font-weight:bold; text-decoration:none;">Edit</a>
                            
                            <a href="?delete_q_id=<?= $row['id'] ?>" style="color:#e74c3c; font-weight:bold; text-decoration:none;" 
                               onclick="return confirm('Delete question?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>