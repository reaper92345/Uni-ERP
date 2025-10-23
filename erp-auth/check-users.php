<?php
require_once __DIR__ . '/../includes/config.php';

echo "<h2>Database Users Check</h2>";

try {
    $conn = getDBConnection();
    
    // Check if users table exists and has data
    $sql = "SELECT username, role, created_at FROM users ORDER BY created_at";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<h3>Existing Users:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Username</th><th>Role</th><th>Created</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found in the database.</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h3>Options:</h3>";
echo "<p><a href='register.php'>Register a new user</a></p>";
echo "<p><a href='create-admin.php'>Create default admin user</a></p>";
echo "<p><a href='login.php'>Back to Login</a></p>";
?>
