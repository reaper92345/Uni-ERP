<?php
require_once '../includes/config.php';

if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET)) {
    http_response_code(500);
    echo 'Google OAuth is not configured. Set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in config.php';
    exit;
}

$redirectUri = GOOGLE_REDIRECT_URI;
if ($redirectUri === '') {
    // Derive callback from current host
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $redirectUri = $scheme . '://' . $host . $base . '/auth-google-callback.php';
}

$state = bin2hex(random_bytes(16));
$_SESSION['oauth2state'] = $state;
$_SESSION['post_login_redirect'] = isset($_GET['redirect']) ? $_GET['redirect'] : '/inventory/pages/erp.php';

$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'access_type' => 'offline',
    'include_granted_scopes' => 'true',
    'prompt' => 'select_account'
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;


