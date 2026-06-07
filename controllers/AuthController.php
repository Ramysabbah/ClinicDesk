<?php
// ============================================================
// controllers/AuthController.php
// ============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    // ----------------------------------------------------------
    // GET: Login page
    // ----------------------------------------------------------
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect_to('dashboard');
        }
        $pageTitle = 'Login — ' . APP_NAME;
        require_once __DIR__ . '/../views/auth/login.php';
    }

    // ----------------------------------------------------------
    // POST: Login handler
    // ----------------------------------------------------------
    public function handleLogin(): void
    {
        // 1. CSRF
        CSRF::verifyOrFail();

        // 2. Sanitize inputs
        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        // 3. Look up user
        $user = $this->users->findByEmail($email);
        if (!$user) {
            flash('danger', 'Invalid credentials.');
            redirect_to('auth', 'login');
        }

        // 4. Check active
        if ((int)$user['is_active'] !== 1) {
            flash('danger', 'Account suspended. Contact admin.');
            redirect_to('auth', 'login');
        }

        // 5. Verify password
        if (!password_verify($password, $user['password'])) {
            flash('danger', 'Invalid credentials.');
            redirect_to('auth', 'login');
        }

        // 6. All good — create session
        Auth::login($user);
        redirect_to('dashboard');
    }

    // ----------------------------------------------------------
    // POST: Logout handler (must be POST + CSRF)
    // ----------------------------------------------------------
    public function handleLogout(): void
    {
        CSRF::verifyOrFail();
        Auth::logout();
    }

    // ----------------------------------------------------------
    // GET: Change password form (first-login enforcement)
    // ----------------------------------------------------------
    public function showChangePassword(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');
        $pageTitle = 'Change Password — ' . APP_NAME;
        require_once __DIR__ . '/../views/auth/change_password.php';
    }

    // ----------------------------------------------------------
    // POST: Change password handler
    // ----------------------------------------------------------
    public function handleChangePassword(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');
        CSRF::verifyOrFail();

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $user = $this->users->findById(Auth::id());

        if (!password_verify($currentPassword, $user['password'])) {
            flash('danger', 'Current password is incorrect.');
            redirect_to('auth', 'change_password');
        }

        if (strlen($newPassword) < 8) {
            flash('danger', 'New password must be at least 8 characters.');
            redirect_to('auth', 'change_password');
        }

        if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            flash('danger', 'Password must contain uppercase, lowercase, and a number.');
            redirect_to('auth', 'change_password');
        }

        if ($newPassword !== $confirmPassword) {
            flash('danger', 'Passwords do not match.');
            redirect_to('auth', 'change_password');
        }

        $this->users->updatePassword(Auth::id(), password_hash($newPassword, PASSWORD_BCRYPT));
        // Clear first_login from session
        $_SESSION['user']['first_login'] = 0;

        flash('success', 'Password changed successfully.');
        redirect_to('dashboard');
    }
}
