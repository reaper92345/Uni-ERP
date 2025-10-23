<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <main class="card">
        <h1>Employee Registration</h1>
        <?php
        if (isset($_GET['error'])) {
            echo "<p style='color:red;font-size:14px'>" . htmlspecialchars($_GET['error']) . "</p>";
      }
      ?>

      <form action="process-register.php" method="POST">
        <div>
            <label for="fullName">Full Name</label>
            <input id="fullName" name="fullName" type="text" required>
        </div>

        <div>
            <label for="username">Employee ID</label>
            <input id="username"  name="username" type="text" required>
        </div>

        <div>
            <label for="department">Department</label>
            <select id="department" name="department" required>
                <option value="">Select Department</option>
                <option>HR</option>
                <option>Finance</option>
                <option>IT</option>
                <option>Administration</option>
                <option>Sales</option>
                <option>Production</option>
            </select>
        </div>

             <div>
        <label for="role">Role / Designation</label>
        <input id="role" name="role" type="text" required>
      </div>
      <div>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required>
      </div>
      <div>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>
      </div>
      <button type="submit" class="btn-primary">Register</button>
      <p class="register-row">
        Already have an account? <a href="login.php">Login</a>
      </p>

      </form>
    </main>
    
</body>
</html>