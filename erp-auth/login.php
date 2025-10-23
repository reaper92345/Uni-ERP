<?php
session_start();
?>
<!Doctype HTML>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="UTF-8" />
        <title>ERP Login</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <main class="card">
            <h1>ERP Login</h1>
            <?php 
            if (isset($_GET['error'])) {
                echo "<p style='color:red; font-size:14px'>" . htmlspecialchars($_GET['error']) . "</p>";
            }
            if (isset($_GET['success'])) {
                echo "<p style='color:green; font-size:14px'>" . htmlspecialchars($_GET['success']) . "</p>";
            }
            ?>
            <form action="process-login.php" method="POST">
                <div>
                    <label for="username">Employee ID</label>
                    <input id="username" name="username" type="text" placeholder="EMP001" required>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-primary">Sign In</button>
                <p class="register-row"> 
                    New Employee? <a href="register.php">Register</a>
                </p>
            </form>
        </main>
    </body>
    </html>