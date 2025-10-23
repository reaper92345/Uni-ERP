<?php
require_once '../includes/config.php';

if (!isset($_GET['state']) || !isset($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
    http_response_code(400);
    echo 'Invalid OAuth state';
    exit;
}

if (!isset($_GET['code'])) {
    http_response_code(400);
    echo 'Missing authorization code';
    exit;
}

$code = $_GET['code'];

$redirectUri = GOOGLE_REDIRECT_URI;
if ($redirectUri === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $redirectUri = $scheme . '://' . $host . $base . '/auth-google-callback.php';
}

// Exchange code for tokens
$tokenResponse = httpPost('https://oauth2.googleapis.com/token', [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code',
]);

if (!$tokenResponse || !isset($tokenResponse['id_token'])) {
    http_response_code(500);
    echo 'Failed to exchange token';
    exit;
}

$idToken = $tokenResponse['id_token'];
$parts = explode('.', $idToken);
$payload = json_decode(base64url_decode($parts[1] ?? ''), true);
if (!$payload || !isset($payload['email'])) {
    http_response_code(500);
    echo 'Failed to parse ID token';
    exit;
}

// Optional domain restriction
if (GOOGLE_ALLOWED_DOMAIN !== '') {
    $domain = substr(strrchr($payload['email'], '@'), 1);
    if (strtolower($domain) !== strtolower(GOOGLE_ALLOWED_DOMAIN)) {
        http_response_code(403);
        echo 'Email domain not allowed';
        exit;
    }
}

// Create session user
$_SESSION['user'] = [
    'email' => $payload['email'],
    'name' => $payload['name'] ?? ($payload['given_name'] ?? ''),
    'picture' => $payload['picture'] ?? '',
    'role' => (GOOGLE_ADMIN_EMAIL && strtolower(GOOGLE_ADMIN_EMAIL) === strtolower($payload['email'])) ? 'admin' : 'user',
    'provider' => 'google'
];

$redirect = $_SESSION['post_login_redirect'] ?? '/inventory/pages/erp.php';
unset($_SESSION['oauth2state'], $_SESSION['post_login_redirect']);
header('Location: ' . $redirect);
exit;

function httpPost($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $response = curl_exec($ch);
    if ($response === false) {
        return null;
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($status >= 200 && $status < 300) {
        return json_decode($response, true);
    }
    return null;
}

function base64url_decode($data) {
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $data .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}


