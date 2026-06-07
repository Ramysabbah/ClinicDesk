<?php
// ============================================================
// controllers/DashboardController.php
// ============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';

class DashboardController
{
    public function show(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');

        match (Auth::role()) {
            'admin'   => $this->adminDashboard(),
            'doctor'  => $this->doctorDashboard(),
            default   => $this->patientDashboard(),
        };
    }

    // ----------------------------------------------------------
    private function adminDashboard(): void
    {
        $users  = new UserModel();
        $appts  = new AppointmentModel();

        $roleCounts   = $users->countByRole();
        $todayCount   = $appts->countToday();
        $weekByStatus = $appts->countThisWeekByStatus();
        $recentAppts  = $appts->getRecent(5);
        $chartData    = $appts->getLast14Days();

        $pageTitle = 'Admin Dashboard — ' . APP_NAME;
        require_once __DIR__ . '/../views/dashboard/admin.php';
    }

    // ----------------------------------------------------------
    private function doctorDashboard(): void
    {
        $appts   = new AppointmentModel();
        $doctors = new DoctorModel();

        $doctor = $doctors->findByUserId(Auth::id());
        if (!$doctor) {
            flash('danger', 'Doctor profile not found.');
            Auth::logout();
        }

        $doctorId   = (int)$doctor['id'];
        $todayAppts = $appts->getTodayByDoctor($doctorId);
        $stats      = $appts->getDoctorStats($doctorId);
        $upcoming   = $appts->getUpcomingByDoctor($doctorId, 5);

        $pageTitle = 'Doctor Dashboard — ' . APP_NAME;
        require_once __DIR__ . '/../views/dashboard/doctor.php';
    }

    // ----------------------------------------------------------
    private function patientDashboard(): void
    {
        $appts  = new AppointmentModel();
        $prescr = new PrescriptionModel();

        $patientId   = Auth::id();
        $stats       = $appts->getPatientStats($patientId);
        $rxCount     = $prescr->countByPatient($patientId);
        $activeAppts = $appts->getActiveByPatient($patientId, 5);

        // Next upcoming appointment
        $nextAppt = !empty($activeAppts) ? $activeAppts[0] : null;

        $pageTitle = 'My Dashboard — ' . APP_NAME;
        require_once __DIR__ . '/../views/dashboard/patient.php';
    }
}
