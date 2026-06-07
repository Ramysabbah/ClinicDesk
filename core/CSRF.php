<?php
// ============================================================
// core/CSRF.php  — CSRF token generation & validation
// ============================================================

require_once __DIR__ . '/../config/config.php';

class CSRF
{
    private const SESSION_KEY = 'csrf_token';

    /**
     * Generates a CSRF token, stores it in session, and returns it.
     * Reuses the existing token if already set (valid per session).
     */
    public static function generateToken(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Validates a submitted token against the session token.
     * Uses hash_equals() to prevent timing attacks.
     *
     * @param string $token  The token from $_POST['csrf_token']
     * @return bool
     */
    public static function validateToken(string $token): bool
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }
        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * Returns an HTML hidden input field with the current CSRF token.
     * Usage: <?= CSRF::field() ?>
     */
    public static function field(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verifies CSRF from $_POST. On failure: sets flash error and redirects back.
     */
    public static function verifyOrFail(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!self::validateToken($token)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid request. Please try again.'];
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/index.php')));
            exit;
        }
    }
}
