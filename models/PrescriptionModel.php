<?php
// ============================================================
// models/PrescriptionModel.php
// ============================================================

require_once __DIR__ . '/BaseModel.php';

class PrescriptionModel extends BaseModel
{
    public function findByAppointmentId(int $apptId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM prescriptions WHERE appointment_id = ?',
            'i', [$apptId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT pr.*, a.patient_id, a.doctor_id, a.appt_date,
                    p.name AS patient_name,
                    u.name AS doctor_name
             FROM prescriptions pr
             JOIN appointments a ON a.id = pr.appointment_id
             JOIN users p        ON p.id = a.patient_id
             JOIN doctors d      ON d.id = a.doctor_id
             JOIN users u        ON u.id = d.user_id
             WHERE pr.id = ?",
            'i', [$id]
        );
    }

    /**
     * Insert prescription — returns new ID.
     */
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO prescriptions (appointment_id, diagnosis, medications, notes, file_path)
             VALUES (?, ?, ?, ?, ?)',
            'issss',
            [
                $data['appointment_id'],
                $data['diagnosis'],
                $data['medications'],
                $data['notes']     ?? null,
                $data['file_path'] ?? null,
            ]
        );
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $result = $this->execute(
            'UPDATE prescriptions
             SET diagnosis = ?, medications = ?, notes = ?, file_path = ?
             WHERE id = ?',
            'ssssi',
            [
                $data['diagnosis'],
                $data['medications'],
                $data['notes']     ?? null,
                $data['file_path'] ?? null,
                $id,
            ]
        );
        return $result === true;
    }

    /**
     * All prescriptions for a patient — JOIN appointments to verify ownership.
     */
    public function getByPatient(int $patientId): array
    {
        return $this->fetchAll(
            "SELECT pr.*, a.appt_date, a.appt_time,
                    u.name AS doctor_name, s.name AS specialization
             FROM prescriptions pr
             JOIN appointments a    ON a.id = pr.appointment_id
             JOIN doctors d         ON d.id = a.doctor_id
             JOIN users u           ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE a.patient_id = ?
             ORDER BY a.appt_date DESC",
            'i', [$patientId]
        );
    }

    /**
     * Count patient prescriptions (for dashboard stats).
     */
    public function countByPatient(int $patientId): int
    {
        return (int) $this->fetchScalar(
            "SELECT COUNT(*) FROM prescriptions pr
             JOIN appointments a ON a.id = pr.appointment_id
             WHERE a.patient_id = ?",
            'i', [$patientId]
        );
    }
}
