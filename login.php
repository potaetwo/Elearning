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
            <p class="sub-title">Create your account to start the quest</p>
        </div>

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

        <div class="card-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
