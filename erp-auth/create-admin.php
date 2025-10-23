<?php
require_once __DIR__ . '/../includes/config.php';

// Simple form to create admin user
if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        try {
            $conn = getDBConnection();
            
            // Check if username already exists
            $checkSql = "SELECT id FROM users WHERE username = ?";
            $stmt = $conn->prepare($checkSql);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo "<p style='color: red;'>Username already exists!</p>";
            } else {
                // Create admin user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertSql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bind_param('ss', $username, $hashedPassword);
                
                if ($insertStmt->execute()) {
                    echo "<p style='color: green;'>Admin user created successfully!</p>";
                    echo "<p><a href='login.php'>Go to Login</a></p>";
                } else {
                    echo "<p style='color: red;'>Error creating user: " . $conn->error . "</p>";
                }
                $insertStmt->close();
            }
            
            $stmt->close();
            $conn->close();
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Please fill in both fields.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="card">
        <h1>Create Admin User</h1>
        
        <form method="POST">
            <div>
                <label for="username">Username</label>
                <input id="username" name="username" type="text" required>
            </div>
            
            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            
            <button type="submit">Create Admin User</button>
        </form>
        
        <p class="register-row">
            <a href="check-users.php">Check Existing Users</a> | 
            <a href="login.php">Back to Login</a>
        </p>
    </main>
</body>
</html>
