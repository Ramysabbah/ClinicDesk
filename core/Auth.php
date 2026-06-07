<?php
// ============================================================
// core/Auth.php  — Authentication & role-based access control
// ============================================================

require_once __DIR__ . '/../config/config.php';

class Auth
{
    /**
     * Stores user data in session after successful login.
     * Regenerates session ID to prevent session fixation.
     */
    public static function login(array $user): void
    {
        $_SESSION['user'] = [
            'id'          => $user['id'],
            'name'        => $user['name'],
            'email'       => $user['email'],
            'role'        => $user['role'],
            'first_login' => $user['first_login'] ?? 0,
        ];
        session_regenerate_id(true);
    }

    /**
     * Destroys the session and redirects to login page.
     */
    public static function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
        exit;
    }

    /**
     * Returns true if a user session exists.
     */
    public static function check(): bool
    {
        return isset($_SESSION['user']['id']);
    }

    /**
     * Returns the current user array or null.
     */
    public static function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Returns the current user's role string or empty string.
     */
    public static function role(): string
    {
        return $_SESSION['user']['role'] ?? '';
    }

    /**
     * Returns the current user's ID or 0.
     */
    public static function id(): int
    {
        return (int) ($_SESSION['user']['id'] ?? 0);
    }

    /**
     * Gate: ensures user is logged in with one of the allowed roles.
     * Redirects to login if not authenticated, or 403 if wrong role.
     *
     * @param string ...$roles  Allowed roles e.g. requireRole('admin','doctor')
     */
    public static function requireRole(string ...$roles): void
    {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
            exit;
        }

        // First-login redirect (password change enforcement)
        if (
            (int)($_SESSION['user']['first_login'] ?? 0) === 1
            && !(isset($_GET['page']) && $_GET['page'] === 'auth'
                && isset($_GET['action']) && $_GET['action'] === 'change_password')
        ) {
            header('Location: ' . BASE_URL . '/index.php?page=auth&action=change_password');
            exit;
        }

        if (!in_array(self::role(), $roles, true)) {
            require_once __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }

    /**
     * Updates session data after profile edit (e.g. name change).
     */
    public static function refreshSession(array $user): void
    {
        if (self::check()) {
            $_SESSION['user']['name']  = $user['name'];
            $_SESSION['user']['email'] = $user['email'];
        }
    }
}
