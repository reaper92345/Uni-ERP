<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-xl mx-auto">
    <div class="card">
        <h1 class="text-2xl font-semibold mb-4 text-text-primary">Employee Registration</h1>
        <?php
        if (isset($_GET['error'])) {
            echo "<p class=\"text-sm mb-3\" style='color:red'>" . htmlspecialchars($_GET['error']) . "</p>";
        }
        ?>

        <form action="process-register.php" method="POST" class="space-y-4">
            <div>
                <label for="fullName" class="block mb-1 text-sm text-text-secondary">Full Name</label>
                <input id="fullName" name="fullName" type="text" required class="form-control w-full" />
            </div>

            <div>
                <label for="username" class="block mb-1 text-sm text-text-secondary">Employee ID</label>
                <input id="username"  name="username" type="text" required class="form-control w-full" />
            </div>

            <div>
                <label for="department" class="block mb-1 text-sm text-text-secondary">Department</label>
                <select id="department" name="department" required class="form-select w-full">
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
                <label for="role" class="block mb-1 text-sm text-text-secondary">Role / Designation</label>
                <input id="role" name="role" type="text" required class="form-control w-full" />
            </div>

            <div>
                <label for="email" class="block mb-1 text-sm text-text-secondary">Email</label>
                <input id="email" name="email" type="email" required class="form-control w-full" />
            </div>

            <div>
                <label for="password" class="block mb-1 text-sm text-text-secondary">Password</label>
                <input id="password" name="password" type="password" required class="form-control w-full" />
            </div>

            <button type="submit" class="btn btn-primary w-full">Register</button>
            <p class="text-center text-sm text-text-secondary mt-2">
                Already have an account? <a href="login.php" class="text-primary">Login</a>
            </p>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>