<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: login.php?error=' . urlencode('Please enter both fields.'));
    exit();
}

$conn = getDBConnection();

// Fetch user by username
$sql = 'SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $conn->close();
    header('Location: login.php?error=' . urlencode('Server error.'));
    exit();
}
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user || !password_verify($password, $user['password'])) {
    header('Location: login.php?error=' . urlencode('Invalid credentials.'));
    exit();
}

// Regenerate session ID to prevent fixation
session_regenerate_id(true);

$_SESSION['user'] = [
    'id' => (int)$user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
];

// Redirect to dashboard/home
header('Location: ../index.php');
exit();