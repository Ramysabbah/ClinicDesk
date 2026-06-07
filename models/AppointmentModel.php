<?php
// ============================================================
// models/AppointmentModel.php  — Fixed: countFiltered JOIN + _buildWhere
// ============================================================

require_once __DIR__ . '/BaseModel.php';

class AppointmentModel extends BaseModel
{
    // ----------------------------------------------------------
    // Book a new appointment
    // ----------------------------------------------------------
    public function book(array $data): bool
    {
        $result = $this->execute(
            'INSERT INTO appointments (patient_id, doctor_id, appt_date, appt_time, reason)
             VALUES (?, ?, ?, ?, ?)',
            'iisss',
            [$data['patient_id'], $data['doctor_id'], $data['appt_date'], $data['appt_time'], $data['reason'] ?? null]
        );
        return $result === true;
    }

    // ----------------------------------------------------------
    // Conflict check (before INSERT for friendly error)
    // ----------------------------------------------------------
    public function hasConflict(int $doctorId, string $date, string $time): bool
    {
        $count = $this->fetchScalar(
            "SELECT COUNT(*) FROM appointments
             WHERE doctor_id = ? AND appt_date = ? AND appt_time = ?
             AND status != 'cancelled'",
            'iss',
            [$doctorId, $date, $time]
        );
        return (int)$count > 0;
    }

    // ----------------------------------------------------------
    // Patient appointments (paginated)
    // ----------------------------------------------------------
    public function getByPatient(int $patientId, int $page, array $filters = []): array
    {
        $filters['_scope_patient'] = $patientId;
        return $this->_getPaginated($filters, $page);
    }

    // ----------------------------------------------------------
    // Doctor appointments (paginated)
    // ----------------------------------------------------------
    public function getByDoctor(int $doctorId, int $page, array $filters = []): array
    {
        $filters['_scope_doctor'] = $doctorId;
        return $this->_getPaginated($filters, $page);
    }

    // ----------------------------------------------------------
    // All appointments — admin (paginated)
    // ----------------------------------------------------------
    public function getAll(int $page, array $filters = []): array
    {
        return $this->_getPaginated($filters, $page);
    }

    // ----------------------------------------------------------
    // Internal paginated query builder
    // ----------------------------------------------------------
    private function _getPaginated(array $filters, int $page): array
    {
        require_once __DIR__ . '/../core/Paginator.php';
        require_once __DIR__ . '/../config/config.php';

        [$where, $types, $params] = $this->_buildWhere($filters);

        // Count
        $countSql = "SELECT COUNT(*)
                     FROM appointments a
                     JOIN users p    ON p.id = a.patient_id
                     JOIN doctors d  ON d.id = a.doctor_id
                     $where";
        $total = (int)$this->fetchScalar($countSql, $types, $params);
        $pager = new Paginator($total, ITEMS_PER_PAGE, $page);

        // Rows
        $rowParams   = $params;
        $rowParams[] = ITEMS_PER_PAGE;
        $rowParams[] = $pager->offset();
        $rowTypes    = $types . 'ii';

        $rows = $this->fetchAll(
            "SELECT a.*,
                    p.name   AS patient_name,
                    p.email  AS patient_email,
                    p.phone  AS patient_phone,
                    u.name   AS doctor_name,
                    s.name   AS specialization,
                    d.consultation_fee
             FROM appointments a
             JOIN users p           ON p.id = a.patient_id
             JOIN doctors d         ON d.id = a.doctor_id
             JOIN users u           ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             $where
             ORDER BY a.appt_date DESC, a.appt_time DESC
             LIMIT ? OFFSET ?",
            $rowTypes,
            $rowParams
        );

        return ['rows' => $rows, 'pager' => $pager];
    }

    // ----------------------------------------------------------
    // Build WHERE clause from filter array
    // ----------------------------------------------------------
    private function _buildWhere(array $filters): array
    {
        $conds  = [];
        $params = [];
        $types  = '';

        if (!empty($filters['_scope_patient'])) {
            $conds[]  = 'a.patient_id = ?';
            $params[] = (int)$filters['_scope_patient'];
            $types   .= 'i';
        }
        if (!empty($filters['_scope_doctor'])) {
            $conds[]  = 'a.doctor_id = ?';
            $params[] = (int)$filters['_scope_doctor'];
            $types   .= 'i';
        }
        if (!empty($filters['status'])) {
            $conds[]  = 'a.status = ?';
            $params[] = $filters['status'];
            $types   .= 's';
        }
        if (!empty($filters['doctor_id'])) {
            $conds[]  = 'a.doctor_id = ?';
            $params[] = (int)$filters['doctor_id'];
            $types   .= 'i';
        }
        if (!empty($filters['patient_name'])) {
            $conds[]  = 'p.name LIKE ?';
            $params[] = '%' . $filters['patient_name'] . '%';
            $types   .= 's';
        }
        if (!empty($filters['date_from'])) {
            $conds[]  = 'a.appt_date >= ?';
            $params[] = $filters['date_from'];
            $types   .= 's';
        }
        if (!empty($filters['date_to'])) {
            $conds[]  = 'a.appt_date <= ?';
            $params[] = $filters['date_to'];
            $types   .= 's';
        }

        $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';
        return [$where, $types, $params];
    }

    // ----------------------------------------------------------
    // Single appointment by ID (full JOINs)
    // ----------------------------------------------------------
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT a.*,
                    p.name   AS patient_name,
                    p.email  AS patient_email,
                    p.phone  AS patient_phone,
                    u.name   AS doctor_name,
                    s.name   AS specialization,
                    d.id     AS doctor_record_id,
                    d.consultation_fee
             FROM appointments a
             JOIN users p           ON p.id = a.patient_id
             JOIN doctors d         ON d.id = a.doctor_id
             JOIN users u           ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE a.id = ?",
            'i',
            [$id]
        );
    }

    // ----------------------------------------------------------
    // Update status + notes
    // ----------------------------------------------------------
    public function updateStatus(int $id, string $status, string $notes = ''): bool
    {
        $result = $this->execute(
            'UPDATE appointments SET status = ?, doctor_notes = ? WHERE id = ?',
            'ssi',
            [$status, $notes ?: null, $id]
        );
        return $result === true;
    }

    // ----------------------------------------------------------
    // Today's appointments for a doctor
    // ----------------------------------------------------------
    public function getTodayByDoctor(int $doctorId): array
    {
        return $this->fetchAll(
            "SELECT a.*, p.name AS patient_name
             FROM appointments a
             JOIN users p ON p.id = a.patient_id
             WHERE a.doctor_id = ? AND a.appt_date = CURDATE()
             ORDER BY a.appt_time ASC",
            'i',
            [$doctorId]
        );
    }

    // ----------------------------------------------------------
    // Upcoming appointments for a doctor
    // ----------------------------------------------------------
    public function getUpcomingByDoctor(int $doctorId, int $limit = 5): array
    {
        return $this->fetchAll(
            "SELECT a.*, p.name AS patient_name
             FROM appointments a
             JOIN users p ON p.id = a.patient_id
             WHERE a.doctor_id = ?
               AND (a.appt_date > CURDATE()
                    OR (a.appt_date = CURDATE() AND a.appt_time > CURTIME()))
               AND a.status IN ('pending','confirmed')
             ORDER BY a.appt_date ASC, a.appt_time ASC
             LIMIT ?",
            'ii',
            [$doctorId, $limit]
        );
    }

    // ----------------------------------------------------------
    // Dashboard stats
    // ----------------------------------------------------------
    public function getDoctorStats(int $doctorId): array
    {
        $row = $this->fetchOne(
            "SELECT
                SUM(MONTH(appt_date)=MONTH(NOW()) AND YEAR(appt_date)=YEAR(NOW())) AS this_month,
                SUM(status='pending')   AS pending,
                SUM(status='completed') AS completed,
                SUM(status='confirmed') AS confirmed
             FROM appointments WHERE doctor_id = ?",
            'i',
            [$doctorId]
        );
        return $row ?? ['this_month' => 0, 'pending' => 0, 'completed' => 0, 'confirmed' => 0];
    }

    public function getPatientStats(int $patientId): array
    {
        $row = $this->fetchOne(
            "SELECT
                SUM(status IN ('pending','confirmed')) AS active,
                SUM(status='completed')                AS completed
             FROM appointments WHERE patient_id = ?",
            'i',
            [$patientId]
        );
        return $row ?? ['active' => 0, 'completed' => 0];
    }

    public function getRecent(int $limit = 5): array
    {
        return $this->fetchAll(
            "SELECT a.*, p.name AS patient_name, u.name AS doctor_name, s.name AS specialization
             FROM appointments a
             JOIN users p           ON p.id = a.patient_id
             JOIN doctors d         ON d.id = a.doctor_id
             JOIN users u           ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             ORDER BY a.created_at DESC LIMIT ?",
            'i',
            [$limit]
        );
    }

    public function countToday(): int
    {
        return (int)$this->fetchScalar(
            "SELECT COUNT(*) FROM appointments WHERE appt_date = CURDATE()"
        );
    }

    public function countThisWeekByStatus(): array
    {
         $rows = $this->fetchAll(
        "SELECT LOWER(status) AS status, COUNT(*) AS total
         FROM appointments
         WHERE YEARWEEK(appt_date, 0) = YEARWEEK(CURDATE(), 0)
         GROUP BY LOWER(status)"
    );

        $c = [
            'pending' => 0,
            'confirmed' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];

        foreach ($rows as $r) {
            $c[$r['status']] = (int)$r['total'];
        }

        return $c;
    }

    public function getForReport(array $filters): array
    {
        $conds  = [];
        $params = [];
        $types  = '';

        if (!empty($filters['date_from'])) {
            $conds[] = 'a.appt_date >= ?';
            $params[] = $filters['date_from'];
            $types .= 's';
        }
        if (!empty($filters['date_to'])) {
            $conds[] = 'a.appt_date <= ?';
            $params[] = $filters['date_to'];
            $types .= 's';
        }
        if (!empty($filters['doctor_id'])) {
            $conds[] = 'a.doctor_id = ?';
            $params[] = (int)$filters['doctor_id'];
            $types .= 'i';
        }
        if (!empty($filters['status'])) {
            $conds[] = 'a.status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

        return $this->fetchAll(
            "SELECT a.appt_date, a.appt_time, a.status, a.reason,
                    p.name AS patient_name, u.name AS doctor_name, s.name AS specialization
             FROM appointments a
             JOIN users p           ON p.id = a.patient_id
             JOIN doctors d         ON d.id = a.doctor_id
             JOIN users u           ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             $where
             ORDER BY a.appt_date ASC, a.appt_time ASC",
            $types,
            $params
        );
    }

    public function getLast14Days(): array
    {
        return $this->fetchAll(
            "SELECT appt_date, COUNT(*) AS total
             FROM appointments
             WHERE appt_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
             GROUP BY appt_date ORDER BY appt_date ASC"
        );
    }

    // Active upcoming appointments for patient dashboard
    public function getActiveByPatient(int $patientId, int $limit = 5): array
    {
        return $this->fetchAll(
            "SELECT a.*, u.name AS doctor_name, s.name AS specialization
             FROM appointments a
             JOIN doctors d         ON d.id = a.doctor_id
             JOIN users u           ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE a.patient_id = ?
               AND a.status IN ('pending','confirmed')
             ORDER BY a.appt_date ASC, a.appt_time ASC
             LIMIT ?",
            'ii',
            [$patientId, $limit]
        );
    }
}
