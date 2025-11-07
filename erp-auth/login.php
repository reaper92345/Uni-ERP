<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-md mx-auto">
    <div class="card">
        <h1 class="text-2xl font-semibold mb-4 text-text-primary">ERP Login</h1>
        <?php 
        if (isset($_GET['error'])) {
            echo "<p class=\"text-sm mb-3\" style='color:red'>" . htmlspecialchars($_GET['error']) . "</p>";
        }
        if (isset($_GET['success'])) {
            echo "<p class=\"text-sm mb-3\" style='color:green'>" . htmlspecialchars($_GET['success']) . "</p>";
        }
        ?>
        <form action="process-login.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block mb-1 text-sm text-text-secondary">Employee ID</label>
                <input id="username" name="username" type="text" placeholder="EMP001" required class="form-control w-full" />
            </div>
            <div>
                <label for="password" class="block mb-1 text-sm text-text-secondary">Password</label>
                <input id="password" name="password" type="password" placeholder="••••••••" required class="form-control w-full" />
            </div>

            <button type="submit" class="btn btn-primary w-full">Sign In</button>
            <p class="text-center text-sm text-text-secondary mt-2">
                New Employee? <a href="register.php" class="text-primary">Register</a>
            </p>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>