<?php
require_once __DIR__ . '/../includes/config.php';

$fullName   = trim($_POST['fullName'] ?? '');
$username   = trim($_POST['username'] ?? '');
$department = trim($_POST['department'] ?? '');
$role       = trim($_POST['role'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';

if ($fullName === '' || $username === '' || $department === '' || $role === '' || $email === '' || $password === '') {
    header('Location: register.php?error=' . urlencode('All fields are required.'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.php?error=' . urlencode('Invalid email address.'));
    exit;
}

$conn = getDBConnection();

// Ensure users table exists with expected columns
// (Assumes `database.sql` already created table.)

// Check duplicate username
$sql = 'SELECT id FROM users WHERE username = ? LIMIT 1';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $conn->close();
    header('Location: register.php?error=' . urlencode('Server error.'));
    exit;
}
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    header('Location: register.php?error=' . urlencode('Employee ID already exists.'));
    exit;
}
$stmt->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user (store username and password + role; other fields not persisted in current schema)
$insertSql = 'INSERT INTO users (username, password, role) VALUES (?, ?, ?)';
$insert = $conn->prepare($insertSql);
if (!$insert) {
    $conn->close();
    header('Location: register.php?error=' . urlencode('Server error.'));
    exit;
}
// Default to 'user' if role not valid
$normalizedRole = in_array($role, ['admin','user'], true) ? $role : 'user';
$insert->bind_param('sss', $username, $hashedPassword, $normalizedRole);
if (!$insert->execute()) {
    $insert->close();
    $conn->close();
    header('Location: register.php?error=' . urlencode('Could not create user.'));
    exit;
}
$insert->close();
$conn->close();

header('Location: login.php?success=' . urlencode('Registration successful. Please log in.'));
exit;
