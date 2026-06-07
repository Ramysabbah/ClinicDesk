<?php
// ============================================================
// controllers/UserController.php  — Admin: CRUD users
// ============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/SpecializationModel.php';

class UserController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    // ----------------------------------------------------------
    // GET: Paginated user list
    // ----------------------------------------------------------
    public function index(): void
    {
        Auth::requireRole('admin');

        $page   = max(1, (int)($_GET['page_num'] ?? 1));
        $role   = sanitize($_GET['role']   ?? '');
        $search = sanitize($_GET['search'] ?? '');

        $data      = $this->users->getAllPaginated($page, $role, $search);
        $pageTitle = 'User Management — ' . APP_NAME;

        require_once __DIR__ . '/../views/users/list.php';
    }

    // ----------------------------------------------------------
    // GET: Create user form
    // ----------------------------------------------------------
    public function create(): void
    {
        Auth::requireRole('admin');

        $specs     = (new SpecializationModel())->getAll();
        $pageTitle = 'Create User — ' . APP_NAME;
        require_once __DIR__ . '/../views/users/create.php';
    }

    // ----------------------------------------------------------
    // POST: Store new user
    // ----------------------------------------------------------
    public function store(): void
    {
        Auth::requireRole('admin');
        CSRF::verifyOrFail();

        $name     = sanitize($_POST['name']     ?? '');
        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $role     = sanitize($_POST['role']     ?? 'patient');
        $phone    = sanitize($_POST['phone']    ?? '');

        // Validations
        if (!$name || !$email || !$password) {
            flash('danger', 'Name, email, and password are required.');
            redirect_to('users', 'create');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('danger', 'Invalid email address.');
            redirect_to('users', 'create');
        }

        if ($this->users->emailExists($email)) {
            flash('danger', 'Email is already registered.');
            redirect_to('users', 'create');
        }

        if (strlen($password) < 6) {
            flash('danger', 'Password must be at least 6 characters.');
            redirect_to('users', 'create');
        }

        if (!in_array($role, ['admin', 'doctor', 'patient'])) {
            flash('danger', 'Invalid role.');
            redirect_to('users', 'create');
        }

        // Handle avatar upload
        
         $avatar = $this->_handleAvatarUpload();

        $userId = $this->users->create([
            'name'     => $name,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role'     => $role,
            'phone'    => $phone,
             'avatar'   => $avatar,
        ]);

        // If doctor, create the doctors record too
        if ($role === 'doctor' && $userId) {
            $days = isset($_POST['available_days']) && is_array($_POST['available_days'])
                    ? implode(',', $_POST['available_days'])
                    : 'Sun,Mon,Tue,Wed,Thu';

            (new DoctorModel())->create([
                'user_id'           => $userId,
                'specialization_id' => (int)($_POST['specialization_id'] ?? 1),
                'bio'               => sanitize($_POST['bio'] ?? ''),
                'consultation_fee'  => (float)($_POST['consultation_fee'] ?? 0),
                'available_days'    => $days,
            ]);
        }

        flash('success', 'User created successfully.');
        redirect_to('users');
    }

    // ----------------------------------------------------------
    // GET: Edit user form
    // ----------------------------------------------------------
    public function edit(): void
    {
        Auth::requireRole('admin');

        $id   = (int)($_GET['id'] ?? 0);
        $editUser = $this->users->findById($id);
       
        if (!$editUser) {
            flash('danger', 'User not found.');
            redirect_to('users');
        }

        $specs     = (new SpecializationModel())->getAll();
        $doctor    = ($editUser['role'] === 'doctor')
                     ? (new DoctorModel())->findByUserId($id)
                     : null;
        $pageTitle = 'Edit User — ' . APP_NAME;

        require_once __DIR__ . '/../views/users/edit.php';
    }

    // ----------------------------------------------------------
    // POST: Update user
    // ----------------------------------------------------------
    public function update(): void
    {
        Auth::requireRole('admin');
        CSRF::verifyOrFail();

        $id    = (int)($_POST['user_id'] ?? 0);
        $user  = $this->users->findById($id);

        if (!$user) {
            flash('danger', 'User not found.');
            redirect_to('users');
        }

        $name  = sanitize($_POST['name']  ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

        if (!$name || !$email) {
            flash('danger', 'Name and email are required.');
            redirect_to('users', 'edit', ['id' => $id]);
        }

        if ($this->users->emailExists($email, $id)) {
            flash('danger', 'Email is already taken.');
            redirect_to('users', 'edit', ['id' => $id]);
        }

        // Handle avatar
        $avatar = $user['avatar'];
        $newAvatar = $this->_handleAvatarUpload();
        if ($newAvatar) {
            // Delete old avatar
            if ($avatar && file_exists(UPLOAD_AVATARS . $avatar)) {
                unlink(UPLOAD_AVATARS . $avatar);
            }
            $avatar = $newAvatar;
        }

        $this->users->update($id, ['name' => $name, 'phone' => $phone, 'avatar' => $avatar]);

        // Update email via model
        $this->users->updateEmail($id, $email);

        // Update doctor fields if applicable
        if ($user['role'] === 'doctor') {
            $doctors = new DoctorModel();
            $doctor  = $doctors->findByUserId($id);
            if ($doctor) {
                $days = isset($_POST['available_days']) && is_array($_POST['available_days'])
                        ? implode(',', $_POST['available_days'])
                        : $doctor['available_days'];
                $doctors->update($doctor['id'], [
                    'specialization_id' => (int)($_POST['specialization_id'] ?? $doctor['specialization_id']),
                    'bio'               => sanitize($_POST['bio'] ?? $doctor['bio']),
                    'consultation_fee'  => (float)($_POST['consultation_fee'] ?? $doctor['consultation_fee']),
                    'available_days'    => $days,
                    'photo'             => $doctor['photo'],
                ]);
            }
        }

        flash('success', 'User updated successfully.');
        redirect_to('users');
    }

    // ----------------------------------------------------------
    // POST: Toggle active/inactive
    // ----------------------------------------------------------
    public function toggleActive(): void
    {
        Auth::requireRole('admin');
        CSRF::verifyOrFail();

        $id = (int)($_POST['user_id'] ?? 0);

        // Cannot deactivate own account
        if ($id === Auth::id()) {
            flash('danger', 'You cannot deactivate your own account.');
            redirect_to('users');
        }

        $this->users->toggleActive($id);
        flash('success', 'Account status updated.');
        redirect_to('users');
    }

    // ----------------------------------------------------------
    // POST: Reset password (admin sets new password)
    // ----------------------------------------------------------
    public function resetPassword(): void
    {
        Auth::requireRole('admin');
        CSRF::verifyOrFail();

        $id          = (int)($_POST['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';

        if (strlen($newPassword) < 6) {
            flash('danger', 'Password must be at least 6 characters.');
            redirect_to('users', 'edit', ['id' => $id]);
        }

        $this->users->updatePassword($id, password_hash($newPassword, PASSWORD_BCRYPT));
        // Force first_login so user must change password on next login
        $this->users->setFirstLogin($id, 1);

        flash('success', 'Password reset successfully.');
        redirect_to('users');
    }

    // ----------------------------------------------------------
    // Serve avatar image through PHP (secure)
    // ----------------------------------------------------------
    public function serveAvatar(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');

        $file = basename($_GET['file'] ?? '');
        $path = UPLOAD_AVATARS . $file;

        if (!$file || !file_exists($path)) {
            http_response_code(404);
            exit;
        }

        $mime = mime_content_type($path);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif'])) {
            http_response_code(403);
            exit;
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    // ----------------------------------------------------------
    // Private: handle avatar file upload, returns filename or null
    // ----------------------------------------------------------
    private function _handleAvatarUpload(): ?string
    {
        if (empty($_FILES['avatar']['tmp_name'])) return null;

        $file = $_FILES['avatar'];

        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > MAX_AVATAR_SIZE)  return null;

        // Validate it is really an image
        $info = getimagesize($file['tmp_name']);
        if (!$info) return null;

        $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
        if (!in_array($info[2], $allowed)) return null;

        $ext      = image_type_to_extension($info[2], false);
        $filename = 'avatar_' . uniqid('', true) . '.' . $ext;
        $dest     = UPLOAD_AVATARS . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return null;

        return $filename;
    }
}
