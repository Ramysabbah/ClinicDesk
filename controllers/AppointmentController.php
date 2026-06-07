<?php
// ============================================================
// controllers/AppointmentController.php
// ============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';

class AppointmentController
{
    private AppointmentModel $appts;
    private DoctorModel      $doctors;

    public function __construct()
    {
        $this->appts   = new AppointmentModel();
        $this->doctors = new DoctorModel();
    }

    // ----------------------------------------------------------
    // GET: Book appointment form (patient)
    // ----------------------------------------------------------
    public function book(): void
    {
        Auth::requireRole('patient');

        $allDoctors = $this->doctors->getAll();
        $pageTitle  = 'Book Appointment — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/book.php';
    }

    // ----------------------------------------------------------
    // POST: Store new appointment (patient)
    // ----------------------------------------------------------
    public function store(): void
    {
        Auth::requireRole('patient');
        CSRF::verifyOrFail();

        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        $date     = sanitize($_POST['appt_date'] ?? '');
        $time     = sanitize($_POST['appt_time'] ?? '');
        $reason   = sanitize($_POST['reason'] ?? '');

        // Validate doctor exists
        $doctor = $this->doctors->findById($doctorId);
        if (!$doctor) {
            flash('danger', 'Please select a valid doctor.');
            redirect_to('appointments', 'book');
        }

        // Validate date is not in the past
        if ($date < date('Y-m-d')) {
            flash('danger', 'Appointment date cannot be in the past.');
            redirect_to('appointments', 'book');
        }

        // Validate day-of-week against available_days
        $dayName   = date('D', strtotime($date)); // e.g. "Mon"
        $availDays = $this->doctors->getAvailableDays($doctorId);
        if (!in_array($dayName, $availDays)) {
            flash('danger', "Dr. {$doctor['name']} is not available on " . date('l', strtotime($date)) . ".");
            redirect_to('appointments', 'book');
        }

        // Validate time slot
        if (!in_array($time, APPOINTMENT_SLOTS)) {
            flash('danger', 'Invalid time slot.');
            redirect_to('appointments', 'book');
        }

        // Conflict check
        if ($this->appts->hasConflict($doctorId, $date, $time)) {
            flash('warning', 'This slot is already booked. Please choose another time.');
            redirect_to('appointments', 'book');
        }

        $this->appts->book([
            'patient_id' => Auth::id(),
            'doctor_id'  => $doctorId,
            'appt_date'  => $date,
            'appt_time'  => $time,
            'reason'     => $reason,
        ]);

        flash('success', 'Appointment booked successfully!');
        redirect_to('appointments', 'my');
    }

    // ----------------------------------------------------------
    // GET: Patient's own appointments
    // ----------------------------------------------------------
    public function myAppointments(): void
    {
        Auth::requireRole('patient');

        $page    = max(1, (int)($_GET['page_num'] ?? 1));
        $filters = $this->_getFilters();

        $data      = $this->appts->getByPatient(Auth::id(), $page, $filters);
        $pageTitle = 'My Appointments — ' . APP_NAME;

        require_once __DIR__ . '/../views/appointments/my.php';
    }

    // ----------------------------------------------------------
    // GET: Doctor's schedule
    // ----------------------------------------------------------
    public function schedule(): void
    {
        Auth::requireRole('doctor');

        $doctor = $this->doctors->findByUserId(Auth::id());
        if (!$doctor) { flash('danger', 'Doctor profile not found.'); redirect_to('dashboard'); }

        $page       = max(1, (int)($_GET['page_num'] ?? 1));
        $filters    = $this->_getFilters();
        $todayAppts = $this->appts->getTodayByDoctor($doctor['id']);
        $data       = $this->appts->getByDoctor($doctor['id'], $page, $filters);

        $pageTitle = 'My Schedule — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/schedule.php';
    }

    // ----------------------------------------------------------
    // GET: Admin — all appointments
    // ----------------------------------------------------------
    public function adminList(): void
    {
        Auth::requireRole('admin');

        $page       = max(1, (int)($_GET['page_num'] ?? 1));
        $filters    = $this->_getFilters();
        $allDoctors = $this->doctors->getAll();
        $data       = $this->appts->getAll($page, $filters);

        $pageTitle = 'All Appointments — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/list.php';
    }

    // ----------------------------------------------------------
    // GET: Single appointment detail
    // ----------------------------------------------------------
    public function detail(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');

        $id   = (int)($_GET['id'] ?? 0);
        $appt = $this->appts->findById($id);

        if (!$appt) {
            flash('danger', 'Appointment not found.');
            redirect_to('dashboard');
        }

        // Ownership check
        $this->_verifyOwnership($appt);

        require_once __DIR__ . '/../models/PrescriptionModel.php';
        $prescription = (new PrescriptionModel())->findByAppointmentId($id);

        $pageTitle = 'Appointment Detail — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/detail.php';
    }

    // ----------------------------------------------------------
    // POST: Update appointment status (doctor/admin)
    // ----------------------------------------------------------
    public function updateStatus(): void
    {
        Auth::requireRole('doctor', 'admin');
        CSRF::verifyOrFail();

        $id     = (int)($_POST['appt_id']      ?? 0);
        $status = sanitize($_POST['status']    ?? '');
        $notes  = sanitize($_POST['notes']     ?? '');

        $appt = $this->appts->findById($id);
        if (!$appt) {
            flash('danger', 'Appointment not found.');
            redirect_to('dashboard');
        }

        $this->_verifyOwnership($appt);

        $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($status, $allowed)) {
            flash('danger', 'Invalid status.');
            redirect_to('appointments', 'detail', ['id' => $id]);
        }

        $this->appts->updateStatus($id, $status, $notes);
        flash('success', 'Appointment status updated.');

        $ref = Auth::role() === 'doctor'
               ? BASE_URL . '/index.php?page=appointments&action=schedule'
               : BASE_URL . '/index.php?page=appointments&action=list';
        redirect($ref);
    }

    // ----------------------------------------------------------
    // POST: Patient cancels pending appointment
    // ----------------------------------------------------------
    public function cancel(): void
    {
        Auth::requireRole('patient');
        CSRF::verifyOrFail();

        $id   = (int)($_POST['appt_id'] ?? 0);
        $appt = $this->appts->findById($id);

        if (!$appt || (int)$appt['patient_id'] !== Auth::id()) {
            flash('danger', 'Appointment not found.');
            redirect_to('appointments', 'my');
        }

        if ($appt['status'] !== 'pending') {
            flash('danger', 'Only pending appointments can be cancelled.');
            redirect_to('appointments', 'my');
        }

        $this->appts->updateStatus($id, 'cancelled');
        flash('success', 'Appointment cancelled.');
        redirect_to('appointments', 'my');
    }

    // ----------------------------------------------------------
    private function _getFilters(): array
    {
        return [
            'status'       => sanitize($_GET['status']       ?? ''),
            'doctor_id'    => (int)($_GET['doctor_id']       ?? 0) ?: '',
            'patient_name' => sanitize($_GET['patient_name'] ?? ''),
            'date_from'    => sanitize($_GET['date_from']    ?? ''),
            'date_to'      => sanitize($_GET['date_to']      ?? ''),
        ];
    }

    // ----------------------------------------------------------
    private function _verifyOwnership(array $appt): void
    {
        $role = Auth::role();
        $uid  = Auth::id();

        if ($role === 'patient' && (int)$appt['patient_id'] !== $uid) {
            require_once __DIR__ . '/../views/errors/403.php';
            exit;
        }

        if ($role === 'doctor') {
            $doctor = $this->doctors->findByUserId($uid);
            if (!$doctor || (int)$appt['doctor_id'] !== (int)$doctor['id']) {
                require_once __DIR__ . '/../views/errors/403.php';
                exit;
            }
        }
    }
}
