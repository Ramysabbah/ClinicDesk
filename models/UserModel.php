<?php
// ============================================================
// models/UserModel.php
// ============================================================

require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel
{
    /**
     * Find user by primary key.
     */
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM users WHERE id = ?',
            'i', [$id]
        );
    }

    /**
     * Find user by email address — used during login.
     */
    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM users WHERE email = ?',
            's', [$email]
        );
    }

    /**
     * Insert a new user and return the new ID.
     * Password must already be hashed before calling.
     */
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO users (name, email, password, role, phone, is_active, first_login)
             VALUES (?, ?, ?, ?, ?, 1, 1)',
            'sssss',
            [
                $data['name'],
                $data['email'],
                $data['password'],
                $data['role'],
                $data['phone'] ?? null,
            ]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Update name, phone, and avatar for a user.
     */
    public function update(int $id, array $data): bool
    {
        $result = $this->execute(
            'UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?',
            'sssi',
            [$data['name'], $data['phone'] ?? null, $data['avatar'] ?? null, $id]
        );
        return $result === true;
    }

    /**
     * Update a user's hashed password.
     */
    public function updatePassword(int $id, string $newHash): bool
    {
        $result = $this->execute(
            'UPDATE users SET password = ?, first_login = 0 WHERE id = ?',
            'si', [$newHash, $id]
        );
        return $result === true;
    }

    /**
     * Paginated user list, optionally filtered by role.
     * Joins doctor specialization for doctor rows.
     */
    public function getAllPaginated(int $page, string $role = '', string $search = ''): array
    {
        require_once __DIR__ . '/../core/Paginator.php';
        require_once __DIR__ . '/../config/config.php';

        $total = $this->countAll($role, $search);
        $pager = new Paginator($total, ITEMS_PER_PAGE, $page);

        $conditions = [];
        $params     = [];
        $types      = '';

        if ($role !== '') {
            $conditions[] = 'u.role = ?';
            $params[]     = $role;
            $types       .= 's';
        }

        if ($search !== '') {
            $conditions[] = '(u.name LIKE ? OR u.email LIKE ?)';
            $like          = '%' . $search . '%';
            $params[]      = $like;
            $params[]      = $like;
            $types        .= 'ss';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $params[] = ITEMS_PER_PAGE;
        $params[] = $pager->offset();
        $types   .= 'ii';

        $rows = $this->fetchAll(
            "SELECT u.*, d.id AS doctor_id, s.name AS specialization
             FROM users u
             LEFT JOIN doctors d ON d.user_id = u.id
             LEFT JOIN specializations s ON s.id = d.specialization_id
             $where
             ORDER BY u.created_at DESC
             LIMIT ? OFFSET ?",
            $types, $params
        );

        return ['rows' => $rows, 'pager' => $pager];
    }

    /**
     * Count users, optionally filtered by role and search.
     */
    public function countAll(string $role = '', string $search = ''): int
    {
        $conditions = [];
        $params     = [];
        $types      = '';

        if ($role !== '') {
            $conditions[] = 'role = ?';
            $params[]     = $role;
            $types       .= 's';
        }

        if ($search !== '') {
            $conditions[] = '(name LIKE ? OR email LIKE ?)';
            $like          = '%' . $search . '%';
            $params[]      = $like;
            $params[]      = $like;
            $types        .= 'ss';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return (int) $this->fetchScalar(
            "SELECT COUNT(*) FROM users $where",
            $types, $params
        );
    }

    /**
     * Toggle is_active between 0 and 1 for a user.
     */
    public function toggleActive(int $id): bool
    {
        $result = $this->execute(
            'UPDATE users SET is_active = 1 - is_active WHERE id = ?',
            'i', [$id]
        );
        return $result === true;
    }

    /**
     * Count users grouped by role — for admin dashboard.
     * Returns ['admin' => N, 'doctor' => N, 'patient' => N]
     */
    public function countByRole(): array
    {
        $rows = $this->fetchAll(
            "SELECT role, COUNT(*) AS total FROM users GROUP BY role"
        );
        $counts = ['admin' => 0, 'doctor' => 0, 'patient' => 0];
        foreach ($rows as $row) {
            $counts[$row['role']] = (int) $row['total'];
        }
        return $counts;
    }

    /**
     * Check if an email is already taken (excluding a given user ID).
     */
    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $count = $this->fetchScalar(
            'SELECT COUNT(*) FROM users WHERE email = ? AND id != ?',
            'si', [$email, $excludeId]
        );
        return (int)$count > 0;
    }

    /**
     * Update email address only.
     */
    public function updateEmail(int $id, string $email): bool
    {
        $result = $this->execute(
            'UPDATE users SET email = ? WHERE id = ?',
            'si', [$email, $id]
        );
        return $result === true;
    }


    /**
     * Set first_login flag.
     */
    public function setFirstLogin(int $id, int $value): bool
    {
        $result = $this->execute(
            'UPDATE users SET first_login = ? WHERE id = ?',
            'ii', [$value, $id]
        );
        return $result === true;
    }

}
