# Google SSO Setup for Inventory

This guide explains how to enable Google Sign-In (OAuth2) for the Inventory app.

## 1) Prerequisites
- Google account
- Google Cloud Console access
- App base URL (example: `http://localhost/inventory`)

## 2) Create OAuth credentials (Google Cloud)
1. Open Google Cloud Console -> APIs & Services -> Credentials.
2. Configure OAuth consent screen (External or Internal).
3. Create Credentials -> OAuth client ID -> Web application.
4. Authorized redirect URI examples:
   - Local: `http://localhost/inventory/api/auth-google-callback.php`
   - Prod: `https://your-domain.com/inventory/api/auth-google-callback.php`
5. Copy Client ID and Client Secret.

## 3) Configure the app
Edit `includes/config.php` and set:

```php
// Required
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');

// Optional (derive if empty)
// define('GOOGLE_REDIRECT_URI', 'http://localhost/inventory/api/auth-google-callback.php');

// Optional restrictions
// define('GOOGLE_ALLOWED_DOMAIN', 'example.com');
// define('GOOGLE_ADMIN_EMAIL', 'admin@example.com');
```

## 4) Flow overview
- Header shows Sign in/Logout based on session.
- `/api/auth-google.php` -> Google OAuth authorize.
- `/api/auth-google-callback.php` -> token exchange + session user.
- `/api/logout.php` -> clear session.

Session example:
```php
$_SESSION['user'] = [
  'email' => 'user@example.com',
  'name' => 'User Name',
  'picture' => 'https://lh3.googleusercontent.com/...',
  'role' => 'admin', // or 'user'
  'provider' => 'google'
];
```

Role helpers in `includes/config.php`:
```php
isLoggedIn();
userHasRole(['admin','user']);
requireRole(['admin']);
```

## 5) Local test
1. Set Client ID/Secret.
2. Ensure local host/path matches Google Cloud config.
3. Visit `/inventory/pages/erp.php` and click Sign in (or navbar Sign in).
4. On success, header shows your avatar/name and Logout.

## 6) Troubleshooting
- Invalid redirect URI: must exactly match Google Cloud setting.
- Invalid state: start from Sign in URL, don’t open callback directly.
- 403 domain: adjust `GOOGLE_ALLOWED_DOMAIN` or leave blank.
- Not admin: set `GOOGLE_ADMIN_EMAIL` to your address.
- Blank/500: ensure PHP cURL enabled and outbound internet access.

## 7) Security notes
- Keep secrets private; don’t commit real secrets to public repos.
- Use HTTPS in production; harden session cookies (secure, HttpOnly, SameSite).
- Consider verifying ID token signature against Google public keys in production.

## 8) Production checklist
- HTTPS on
- OAuth credentials with production domain
- Callback URL updated
- Session hardening configured

---
Need help setting the redirect URI for your environment? Share your exact base URL.
