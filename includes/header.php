<?php require_once 'config.php'; ?>
<?php
// Redirect unauthenticated users to login page except for allowed paths
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$base = getBasePath();
// Paths allowed without auth
$publicPaths = [
    $base . '/erp-auth/login.php',
    $base . '/erp-auth/register.php',
    $base . '/erp-auth/process-login.php',
    $base . '/erp-auth/process-register.php',
    $base . '/api/auth-google.php',
    $base . '/api/auth-google-callback.php',
    $base . '/assets/css/style.css',
    $base . '/assets/js/main.js',
];

// Also allow root to redirect to login if not logged in
$isPublic = false;
foreach ($publicPaths as $p) {
    if (strpos($requestUri, $p) !== false) { $isPublic = true; break; }
}

if (!isLoggedIn() && !$isPublic) {
    // Avoid redirect loops for index.php routed directly
    if (basename($_SERVER['SCRIPT_NAME']) !== 'login.php') {
        header('Location: ' . $base . '/erp-auth/login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo getBasePath(); ?>/assets/css/style.css">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        // Initialize theme immediately
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body class="min-h-screen">
    <nav class="navbar">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a class="text-xl font-bold text-primary" href="<?php echo getBasePath(); ?>/">ERP System</a>
                <div class="hidden lg:flex items-center gap-1">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-text-secondary">Online</span>
                </div>
            </div>
            <button class="lg:hidden text-text-primary focus:outline-none" id="navbar-toggle">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <div class="hidden lg:flex space-x-1 items-center" id="navbar-menu">
                <a class="nav-link" href="<?php echo getBasePath(); ?>/">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a class="nav-link" href="<?php echo getBasePath(); ?>/pages/products.php">
                    <i class="fas fa-boxes"></i>
                    <span>Products</span>
                </a>
                <a class="nav-link" href="<?php echo getBasePath(); ?>/pages/sales.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sales</span>
                </a>
                <a class="nav-link" href="<?php echo getBasePath(); ?>/pages/expenses.php">
                    <i class="fas fa-money-bill"></i>
                    <span>Expenses</span>
                </a>
                <a class="nav-link" href="<?php echo getBasePath(); ?>/pages/reports.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
                </a>
                <a class="nav-link" href="<?php echo getBasePath(); ?>/pages/purchases.php">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Purchases</span>
                </a>
                <a class="nav-link" href="<?php echo getBasePath(); ?>/landing.php">
                    <i class="fas fa-rocket"></i>
                    <span>ERP Launcher</span>
                </a>
                <!--
                <a href="<?php echo getBasePath(); ?>/pages/erp.php" class="p-2 rounded hover:bg-gray-100" title="Apps" aria-label="Open apps">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" class="text-text-primary">
                        <circle cx="5" cy="5" r="1.5"></circle>
                        <circle cx="12" cy="5" r="1.5"></circle>
                        <circle cx="19" cy="5" r="1.5"></circle>
                        <circle cx="5" cy="12" r="1.5"></circle>
                        <circle cx="12" cy="12" r="1.5"></circle>
                        <circle cx="19" cy="12" r="1.5"></circle>
                        <circle cx="5" cy="19" r="1.5"></circle>
                        <circle cx="12" cy="19" r="1.5"></circle>
                        <circle cx="19" cy="19" r="1.5"></circle>
                    </svg>
                </a> -->
                <?php if (!isLoggedIn()): ?>
                    <a href="<?php echo getBasePath(); ?>/api/auth-google.php" class="btn btn-primary">Sign in</a>
                <?php else: ?>
                    <div class="flex items-center gap-2">
                        <?php if (!empty($_SESSION['user']['picture'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['user']['picture']); ?>" alt="avatar" class="w-7 h-7 rounded-full" />
                        <?php endif; ?>
                        <span class="text-sm text-text-primary"><?php echo htmlspecialchars($_SESSION['user']['name'] ?? $_SESSION['user']['email'] ?? $_SESSION['user']['username'] ?? 'User'); ?></span>
                        <a href="<?php echo getBasePath(); ?>/api/logout.php" class="btn">Logout</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-6">
    
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="theme-toggle" title="Toggle Dark Mode">
        <i class="fas fa-moon text-xl"></i>
    </button>

    <script>
        // Theme switching functionality
        function initTheme() {
            const themeToggle = document.getElementById('theme-toggle');
            const html = document.documentElement;
            const themeIcon = themeToggle.querySelector('i');
            
            // Check for saved theme preference
            const savedTheme = localStorage.getItem('theme') || 'dark';
            html.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
            
            // Theme toggle click handler
            themeToggle.addEventListener('click', () => {
                const currentTheme = html.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcon(newTheme);
                
                // Force a repaint
                document.body.style.display = 'none';
                document.body.offsetHeight; // Force reflow
                document.body.style.display = '';
                
                // Log theme change for debugging
                console.log('Theme changed to:', newTheme);
            });
            
            // Update theme icon
            function updateThemeIcon(theme) {
                themeIcon.className = theme === 'light' ? 'fas fa-moon text-xl' : 'fas fa-sun text-xl';
            }
            
            // Log initial theme for debugging
            console.log('Initial theme:', savedTheme);
        }

        // Initialize theme when DOM is loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTheme);
        } else {
            initTheme();
        }
    </script>
</body>
</html> 