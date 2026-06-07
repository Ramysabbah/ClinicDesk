<?php
// ============================================================
// controllers/ReportController.php  — Admin: reports + CSV export
// ============================================================

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';

class ReportController
{
    public function index(): void
    {
        Auth::requireRole('admin');

        $filters = [
            'date_from' => sanitize($_GET['date_from'] ?? ''),
            'date_to'   => sanitize($_GET['date_to']   ?? ''),
            'doctor_id' => sanitize($_GET['doctor_id'] ?? ''),
            'status'    => sanitize($_GET['status']    ?? ''),
        ];

        $rows    = [];
        $error   = '';
        $appts   = new AppointmentModel();
        $doctors = (new DoctorModel())->getAll();

        // Only run query if dates are provided
        if ($filters['date_from'] && $filters['date_to']) {
            if ($filters['date_from'] > $filters['date_to']) {
                $error = 'Start date must be before end date.';
            } else {
                $rows = $appts->getForReport($filters);

                // CSV export
                if (($filters['date_from'] || $filters['date_to']) && ($_GET['export'] ?? '') === 'csv') {
                    $this->exportCsv($rows);
                }
            }
        }

        $pageTitle = 'Reports — ' . APP_NAME;
        require_once __DIR__ . '/../views/reports/index.php';
    }

    private function exportCsv(array $rows): never
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="appointments_report_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // BOM for Excel UTF-8 compatibility
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Patient Name', 'Doctor Name', 'Specialization', 'Date', 'Time', 'Status', 'Reason']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['patient_name'],
                $row['doctor_name'],
                $row['specialization'],
                $row['appt_date'],
                $row['appt_time'],
                $row['status'],
                $row['reason'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }
}
