<?php
// ============================================================
// models/SpecializationModel.php
// ============================================================

require_once __DIR__ . '/BaseModel.php';

class SpecializationModel extends BaseModel
{
    public function getAll(): array
    {
        return $this->fetchAll(
            'SELECT * FROM specializations ORDER BY name ASC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM specializations WHERE id = ?',
            'i', [$id]
        );
    }

    public function create(string $name): int
    {
        $this->execute(
            'INSERT INTO specializations (name) VALUES (?)',
            's', [$name]
        );
        return $this->db->lastInsertId();
    }

    public function update(int $id, string $name): bool
    {
        $result = $this->execute(
            'UPDATE specializations SET name = ? WHERE id = ?',
            'si', [$name, $id]
        );
        return $result === true;
    }

    public function delete(int $id): bool
    {
        $result = $this->execute(
            'DELETE FROM specializations WHERE id = ?',
            'i', [$id]
        );
        return $result === true;
    }

    /**
     * Returns false if any doctors use this specialization.
     */
    public function isSafeToDelete(int $id): bool
    {
        $count = $this->fetchScalar(
            'SELECT COUNT(*) FROM doctors WHERE specialization_id = ?',
            'i', [$id]
        );
        return (int)$count === 0;
    }

    public function nameExists(string $name, int $excludeId = 0): bool
    {
        $count = $this->fetchScalar(
            'SELECT COUNT(*) FROM specializations WHERE name = ? AND id != ?',
            'si', [$name, $excludeId]
        );
        return (int)$count > 0;
    }
}
