<?php
// Shared session bootstrap. Login/registration should store the signed-in user as
// $_SESSION['user'] = ['id' => ..., 'name' => ..., 'email' => ...];
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
        'id'    => (int) $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
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
        redirect('login.php');
    }
}

// Appends the file's modification time (?v=...) so browsers reload CSS/JS after every change
function asset(string $path): string
{
    $file = dirname(__DIR__) . '/' . ltrim($path, '/');
    $version = is_file($file) ? filemtime($file) : time();

    return e($path . '?v=' . $version);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
