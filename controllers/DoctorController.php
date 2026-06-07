<?php
// ============================================================
// controllers/DoctorController.php — Doctor self-profile edit
// ============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/SpecializationModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class DoctorController
{
    // ----------------------------------------------------------
    // GET: Doctor admin list (admin)
    // ----------------------------------------------------------
    public function index(): void
    {
        Auth::requireRole('admin');

        $data      = (new DoctorModel())->getAllPaginated(max(1,(int)($_GET['page_num']??1)));
        $pageTitle = 'Doctors — ' . APP_NAME;
        require_once __DIR__ . '/../views/doctors/list.php';
    }

    // ----------------------------------------------------------
    // GET: Edit doctor profile (admin or self)
    // ----------------------------------------------------------
    public function edit(): void
    {
        Auth::requireRole('admin', 'doctor');

        $doctorId = (int)($_GET['id'] ?? 0);
        $doctor   = (new DoctorModel())->findById($doctorId);

        if (!$doctor) {
            flash('danger', 'Doctor not found.');
            redirect_to(Auth::role() === 'admin' ? 'doctors' : 'dashboard');
        }

        // Doctor can only edit own profile
        if (Auth::role() === 'doctor' && (int)$doctor['user_id'] !== Auth::id()) {
            require_once __DIR__ . '/../views/errors/403.php'; exit;
        }

        $specs     = (new SpecializationModel())->getAll();
        $pageTitle = 'Edit Doctor Profile — ' . APP_NAME;
        require_once __DIR__ . '/../views/doctors/edit.php';
    }

    // ----------------------------------------------------------
    // POST: Update doctor profile
    // ----------------------------------------------------------
    public function update(): void
    {
        Auth::requireRole('admin', 'doctor');
        CSRF::verifyOrFail();

        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        $doctors  = new DoctorModel();
        $doctor   = $doctors->findById($doctorId);

        if (!$doctor) { flash('danger','Doctor not found.'); redirect_to('dashboard'); }
        if (Auth::role() === 'doctor' && (int)$doctor['user_id'] !== Auth::id()) {
            require_once __DIR__ . '/../views/errors/403.php'; exit;
        }

        $days = isset($_POST['available_days']) && is_array($_POST['available_days'])
                ? implode(',', $_POST['available_days'])
                : $doctor['available_days'];

        // Handle photo upload
        $photo = $doctor['photo'];
        if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photo'];
            if ($file['size'] <= MAX_DOCTOR_PHOTO_SIZE) {
                $info = getimagesize($file['tmp_name']);
                if ($info && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
                    $ext  = image_type_to_extension($info[2], false);
                    $name = 'doctor_' . uniqid('', true) . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], UPLOAD_DOCTOR_PHOTOS . $name)) {
                        if ($photo && file_exists(UPLOAD_DOCTOR_PHOTOS . $photo)) unlink(UPLOAD_DOCTOR_PHOTOS . $photo);
                        $photo = $name;
                    }
                }
            }
        }

        $doctors->update($doctorId, [
            'specialization_id' => (int)($_POST['specialization_id'] ?? $doctor['specialization_id']),
            'bio'               => sanitize($_POST['bio'] ?? ''),
            'consultation_fee'  => (float)($_POST['consultation_fee'] ?? 0),
            'available_days'    => $days,
            'photo'             => $photo,
        ]);

        // Update user name & phone too
        $users = new UserModel();
        $name  = sanitize($_POST['name'] ?? $doctor['name']);
        $phone = sanitize($_POST['phone'] ?? '');
        $users->update($doctor['user_id'], ['name' => $name, 'phone' => $phone, 'avatar' => $doctor['avatar']]);

        if (Auth::id() === (int)$doctor['user_id']) {
            Auth::refreshSession(['name' => $name, 'email' => $doctor['email']]);
        }

        flash('success', 'Profile updated successfully.');
        $back = Auth::role() === 'admin'
                ? BASE_URL . '/index.php?page=doctors'
                : BASE_URL . '/index.php?page=dashboard';
        redirect($back);
    }

    // ----------------------------------------------------------
    // Serve doctor photo (secure)
    // ----------------------------------------------------------
    public function servePhoto(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');
        $file = basename($_GET['file'] ?? '');
        $path = UPLOAD_DOCTOR_PHOTOS . $file;
        if (!$file || !file_exists($path)) { http_response_code(404); exit; }
        $mime = mime_content_type($path);
        if (!in_array($mime, ['image/jpeg', 'image/png'])) { http_response_code(403); exit; }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
