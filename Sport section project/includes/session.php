<?php
// Shared session bootstrap. Login/registration should store the signed-in user as
// $_SESSION['user'] = ['id' => ..., 'name' => ..., 'email' => ...];
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Web path prefix of the app's root folder, computed from the real server document root.
// '' when the app IS the document root (e.g. XAMPP vhost, `php -S` from the project folder).
// '/sub-folder' when it's installed under htdocs/sub-folder — so links keep working either way.
if (!defined('APP_BASE')) {
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
    $appRoot = str_replace('\\', '/', rtrim(dirname(__DIR__), '/'));
    define('APP_BASE', ($docRoot !== '' && str_starts_with($appRoot, $docRoot)) ? substr($appRoot, strlen($docRoot)) : '');
}

// Builds an app-root-relative URL that works regardless of where the app is installed
function url(string $path): string
{
    return APP_BASE . '/' . ltrim($path, '/');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return (bool) (current_user()['is_admin'] ?? false);
}

function csrf_token(): string
{
    return $_SESSION['csrf_token'];
}

function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? $value : '';
}

function verify_csrf(): bool
{
    $submittedToken = $_POST['csrf_token'] ?? null;

    return is_string($submittedToken) && hash_equals(csrf_token(), $submittedToken);
}

function login_user(array $user): void
{
    // New session id on login prevents session fixation
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'       => (int) $user['id'],
        'name'     => $user['name'],
        'email'    => $user['email'],
        'is_admin' => (bool) ($user['is_admin'] ?? false),
    ];
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect(url('login.php'));
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        redirect(url('mp.php'));
    }
}

// Reads a positive integer from $_POST[$key] (e.g. an id), or null if missing/invalid
function post_positive_int(string $key): ?int
{
    $value = $_POST[$key] ?? null;
    if (!is_string($value) && !is_int($value)) {
        return null;
    }

    $validated = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    return $validated === false ? null : (int) $validated;
}

// Appends the file's modification time (?v=...) so browsers reload CSS/JS after every change
function asset(string $path): string
{
    $file = dirname(__DIR__) . '/' . ltrim($path, '/');
    $version = is_file($file) ? filemtime($file) : time();

    return e(url($path) . '?v=' . $version);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
