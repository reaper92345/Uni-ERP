<?php
// Database configuration - use environment variables for Docker
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'inventory_system');

// Create connection
function getDBConnection() {
	$host = DB_HOST;
	$user = DB_USER;
	$pass = DB_PASS;
	$name = DB_NAME;

	$maxAttempts = 30; // ~30s
	$attempt = 0;
	$lastError = '';

	while ($attempt < $maxAttempts) {
		$conn = @new mysqli($host, $user, $pass, $name);
		if (!$conn->connect_errno) {
			return $conn;
		}
		$lastError = '(' . $conn->connect_errno . ') ' . $conn->connect_error;
		// Common transient errors while MySQL is booting: 2002 (connection refused), 2003 (can't connect)
		sleep(1);
		$attempt++;
	}

	die("Connection failed after retries: " . $lastError);
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Format date
function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}

// Format currency
function formatCurrency($amount) {
    return number_format($amount, 2);
}

// Handle database errors
function handleDBError($conn, $sql) {
    return "Error: " . $sql . "<br>" . $conn->error;
}

// Get current stock for a product
function getCurrentStock($productId) {
    $conn = getDBConnection();
    
    // Get initial stock
    $sql = "SELECT stock FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $currentStock = $row['stock'];
    
    // Subtract sold quantities
    $sql = "SELECT COALESCE(SUM(quantity), 0) as total_sold FROM sales WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalSold = $row['total_sold'];
    
    $conn->close();
    return $currentStock - $totalSold;
}

// ----------------------
// Sessions and Roles
// ----------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function currentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function isLoggedIn() {
    return currentUser() !== null;
}

function userHasRole($roles) {
    if (!isLoggedIn()) return false;
    $userRole = $_SESSION['user']['role'] ?? 'user';
    if (is_array($roles)) {
        return in_array($userRole, $roles, true);
    }
    return $userRole === $roles;
}

function requireRole($roles = ['admin']) {
    if (!userHasRole($roles)) {
        http_response_code(403);
        die('<div style="padding:16px;font-family:system-ui">Access denied</div>');
    }
}

// ----------------------
// Google OAuth (placeholders)
// ----------------------
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', '');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', '');
}
// Optional: set explicit redirect URI; if empty, we will derive it at runtime
if (!defined('GOOGLE_REDIRECT_URI')) {
    define('GOOGLE_REDIRECT_URI', 'http://localhost/inventory/api/auth-google-callback.php');
}
// Optional: restrict to a GSuite domain, e.g., example.com (leave blank to allow all)
if (!defined('GOOGLE_ALLOWED_DOMAIN')) {
    define('GOOGLE_ALLOWED_DOMAIN', '');
}
// Optional: automatically grant admin role for this email
if (!defined('GOOGLE_ADMIN_EMAIL')) {
    define('GOOGLE_ADMIN_EMAIL', '');
}

// ----------------------
// Base path helper
// ----------------------
function getBasePath() {
	// Allow override via environment variable
	$envBase = $_ENV['APP_BASE_PATH'] ?? '';
	if ($envBase !== '') {
		$envBase = '/' . trim($envBase, '/');
		return $envBase === '/' ? '' : $envBase;
	}

	// Derive from filesystem paths (works in Docker: /var/www/html)
	$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') : '';
	$appRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
	if ($documentRoot !== '' && strpos($appRoot, $documentRoot) === 0) {
		$base = substr($appRoot, strlen($documentRoot));
		return $base === '/' ? '' : rtrim($base, '/');
	}

	return '';
}
