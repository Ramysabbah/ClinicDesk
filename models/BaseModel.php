<?php
// ============================================================
// models/BaseModel.php  — Abstract base for all models
// ============================================================

require_once __DIR__ . '/../core/Database.php';

abstract class BaseModel
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Central query executor — all models use this, never $this->db->query() directly.
     * This lets us swap error handling in one place.
     *
     * @return mysqli_result|bool
     */
    protected function execute(string $sql, string $types = '', array $params = []): mixed
    {
        return $this->db->query($sql, $types, $params);
    }

    /**
     * Fetch a single row as associative array.
     */
    protected function fetchOne(string $sql, string $types = '', array $params = []): ?array
    {
        $result = $this->execute($sql, $types, $params);
        if (!$result || !($result instanceof mysqli_result)) return null;
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Fetch all rows as array of associative arrays.
     */
    protected function fetchAll(string $sql, string $types = '', array $params = []): array
    {
        $result = $this->execute($sql, $types, $params);
        if (!$result || !($result instanceof mysqli_result)) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Fetch a single scalar value (first column of first row).
     */
    protected function fetchScalar(string $sql, string $types = '', array $params = []): mixed
    {
        $result = $this->execute($sql, $types, $params);
        if (!$result || !($result instanceof mysqli_result)) return null;
        $row = $result->fetch_row();
        return $row ? $row[0] : null;
    }
}
