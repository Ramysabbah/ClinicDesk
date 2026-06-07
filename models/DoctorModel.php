<?php
// ============================================================
// models/DoctorModel.php
// ============================================================

require_once __DIR__ . '/BaseModel.php';

class DoctorModel extends BaseModel
{
    /**
     * Find doctor record by user_id — JOIN users + specializations.
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->fetchOne(
            "SELECT d.*, u.name, u.email, u.phone, u.avatar, u.is_active,
                    s.name AS specialization_name
             FROM doctors d
             JOIN users u          ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE d.user_id = ?",
            'i', [$userId]
        );
    }

    /**
     * Find doctor record by doctor primary key.
     */
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT d.*, u.name, u.email, u.phone, u.avatar, u.is_active,
                    s.name AS specialization_name
             FROM doctors d
             JOIN users u          ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE d.id = ?",
            'i', [$id]
        );
    }

    /**
     * All active doctors — for dropdown lists (book appointment form).
     */
    public function getAll(): array
    {
        return $this->fetchAll(
            "SELECT d.id, d.user_id, d.consultation_fee, d.available_days,
                    u.name, s.name AS specialization_name
             FROM doctors d
             JOIN users u          ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE u.is_active = 1
             ORDER BY u.name ASC"
        );
    }

    /**
     * Paginated doctor list for admin panel.
     */
    public function getAllPaginated(int $page): array
    {
        require_once __DIR__ . '/../core/Paginator.php';
        require_once __DIR__ . '/../config/config.php';

        $total = (int) $this->fetchScalar('SELECT COUNT(*) FROM doctors');
        $pager = new Paginator($total, ITEMS_PER_PAGE, $page);

        $rows = $this->fetchAll(
            "SELECT d.*, u.name, u.email, u.is_active, s.name AS specialization_name
             FROM doctors d
             JOIN users u          ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             ORDER BY u.name ASC
             LIMIT ? OFFSET ?",
            'ii', [ITEMS_PER_PAGE, $pager->offset()]
        );

        return ['rows' => $rows, 'pager' => $pager];
    }

    /**
     * Insert a new doctor record (user already created).
     */
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, available_days, photo)
             VALUES (?, ?, ?, ?, ?, ?)',
            'iisdss',
            [
                $data['user_id'],
                $data['specialization_id'],
                $data['bio']              ?? null,
                $data['consultation_fee'] ?? 0.00,
                $data['available_days']   ?? 'Sun,Mon,Tue,Wed,Thu',
                $data['photo']            ?? null,
            ]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Update doctor-specific fields.
     */
    public function update(int $doctorId, array $data): bool
    {
        $result = $this->execute(
            'UPDATE doctors
             SET specialization_id = ?, bio = ?, consultation_fee = ?,
                 available_days = ?, photo = ?
             WHERE id = ?',
            'isdssi',
            [
                $data['specialization_id'],
                $data['bio']              ?? null,
                $data['consultation_fee'] ?? 0.00,
                $data['available_days']   ?? 'Sun,Mon,Tue,Wed,Thu',
                $data['photo']            ?? null,
                $doctorId,
            ]
        );
        return $result === true;
    }

    /**
     * Returns the available days as an array.
     */
    public function getAvailableDays(int $doctorId): array
    {
        $row = $this->fetchOne(
            'SELECT available_days FROM doctors WHERE id = ?',
            'i', [$doctorId]
        );
        if (!$row) return [];
        return array_map('trim', explode(',', $row['available_days']));
    }
}
