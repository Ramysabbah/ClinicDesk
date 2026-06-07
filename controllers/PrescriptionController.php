<?php
// ============================================================
// controllers/PrescriptionController.php
// ============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';

class PrescriptionController
{
    private PrescriptionModel $prescriptions;
    private AppointmentModel  $appointments;
    private DoctorModel       $doctors;

    public function __construct()
    {
        $this->prescriptions = new PrescriptionModel();
        $this->appointments  = new AppointmentModel();
        $this->doctors       = new DoctorModel();
    }

    // ----------------------------------------------------------
    // GET: Add prescription form (doctor)
    // ----------------------------------------------------------
    public function add(): void
    {
        Auth::requireRole('doctor');

        $apptId = (int)($_GET['appt_id'] ?? 0);
        $appt   = $this->appointments->findById($apptId);
        $doctor = $this->doctors->findByUserId(Auth::id());

        if (!$appt || !$doctor) {
            flash('danger', 'Appointment not found.');
            redirect_to('appointments', 'schedule');
        }

        // Ownership + status check
        if ((int)$appt['doctor_id'] !== (int)$doctor['id']) {
            require_once __DIR__ . '/../views/errors/403.php'; exit;
        }
        if ($appt['status'] !== 'completed') {
            flash('danger', 'Prescription can only be added for completed appointments.');
            redirect_to('appointments', 'schedule');
        }
        if ($this->prescriptions->findByAppointmentId($apptId)) {
            flash('warning', 'A prescription already exists for this appointment.');
            redirect_to('appointments', 'detail', ['id' => $apptId]);
        }

        $pageTitle = 'Add Prescription — ' . APP_NAME;
        require_once __DIR__ . '/../views/prescriptions/add.php';
    }

    // ----------------------------------------------------------
    // POST: Store prescription (doctor)
    // ----------------------------------------------------------
    public function store(): void
    {
        Auth::requireRole('doctor');
        CSRF::verifyOrFail();

        $apptId     = (int)($_POST['appt_id']    ?? 0);
        $diagnosis  = sanitize($_POST['diagnosis']   ?? '');
        $medications = sanitize($_POST['medications'] ?? '');
        $notes      = sanitize($_POST['notes']       ?? '');

        $appt   = $this->appointments->findById($apptId);
        $doctor = $this->doctors->findByUserId(Auth::id());

        if (!$appt || !$doctor || (int)$appt['doctor_id'] !== (int)$doctor['id']) {
            require_once __DIR__ . '/../views/errors/403.php'; exit;
        }

        if (!$diagnosis || !$medications) {
            flash('danger', 'Diagnosis and medications are required.');
            redirect_to('prescriptions', 'add', ['appt_id' => $apptId]);
        }

        // Handle PDF upload
        $filePath = $this->_handlePdfUpload($apptId);

        $this->prescriptions->create([
            'appointment_id' => $apptId,
            'diagnosis'      => $diagnosis,
            'medications'    => $medications,
            'notes'          => $notes,
            'file_path'      => $filePath,
        ]);

        flash('success', 'Prescription added successfully.');
        redirect_to('appointments', 'detail', ['id' => $apptId]);
    }

    // ----------------------------------------------------------
    // GET: Patient views own prescriptions list
    // ----------------------------------------------------------
    public function myPrescriptions(): void
    {
        Auth::requireRole('doctor','patient');

        $prescriptions = $this->prescriptions->getByPatient(Auth::id());
        $pageTitle     = 'My Prescriptions — ' . APP_NAME;
        require_once __DIR__ . '/../views/prescriptions/view.php';
    }

    // ----------------------------------------------------------
    // GET: Secure PDF download (ownership-verified)
    // ----------------------------------------------------------
    public function download(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');

        $apptId = (int)($_GET['appt_id'] ?? 0);
        $rx     = $this->prescriptions->findByAppointmentId($apptId);
        $appt   = $this->appointments->findById($apptId);

        if (!$rx || !$appt) {
            flash('danger', 'Prescription not found.'); redirect_to('dashboard');
        }

        // Ownership check
        $role = Auth::role();
        $uid  = Auth::id();

        if ($role === 'patient' && (int)$appt['patient_id'] !== $uid) {
            require_once __DIR__ . '/../views/errors/403.php'; exit;
        }
        if ($role === 'doctor') {
            $doctor = $this->doctors->findByUserId($uid);
            if (!$doctor || (int)$appt['doctor_id'] !== (int)$doctor['id']) {
                require_once __DIR__ . '/../views/errors/403.php'; exit;
            }
        }

        if (!$rx['file_path']) {
            flash('danger', 'No file attached to this prescription.');
            redirect_to('dashboard');
        }

        $fullPath = UPLOAD_PRESCRIPTIONS . $rx['file_path'];
        if (!file_exists($fullPath)) {
            flash('danger', 'Prescription file not found on server.');
            redirect_to('dashboard');
        }

        // Stream file
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="prescription_' . $apptId . '.pdf"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    // ----------------------------------------------------------
    private function _handlePdfUpload(int $apptId): ?string
    {
        if (empty($_FILES['prescription_file']['tmp_name'])) return null;

        $file = $_FILES['prescription_file'];
        if ($file['error'] !== UPLOAD_ERR_OK)          return null;
        if ($file['size'] > MAX_PRESCRIPTION_SIZE)      return null;

        // Validate MIME via finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') return null;

        $filename = 'prescription_' . $apptId . '_' . time() . '.pdf';
        $dest     = UPLOAD_PRESCRIPTIONS . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return null;

        return $filename;
    }
}
