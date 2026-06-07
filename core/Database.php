<?php
// ============================================================
// core/Database.php  — Singleton mysqli wrapper
// ============================================================

require_once __DIR__ . '/../config/database.php';

class Database
{
    /** @var Database|null Single instance */
    private static ?Database $instance = null;

    /** @var mysqli Active connection */
    private mysqli $conn;

    /**
     * Private constructor — opens the DB connection once.
     * Throws RuntimeException with a safe message on failure.
     */
    private function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $this->conn->set_charset(DB_CHARSET);
        } catch (mysqli_sql_exception $e) {
            // Never expose real DB error to the browser
            error_log('DB connection failed: ' . $e->getMessage());
            throw new RuntimeException('Database connection failed. Please try again later.');
        }
    }

    /**
     * Returns the single Database instance (creates it on first call).
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Executes a prepared statement.
     *
     * @param string $sql    SQL with ? placeholders
     * @param string $types  mysqli bind_param type string e.g. "ssi"
     * @param array  $params Values matching the placeholders
     * @return mysqli_result|bool  get_result() for SELECT, true/false otherwise
     */
    public function query(string $sql, string $types = '', array $params = []): mysqli_result|bool
    {
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            error_log('Prepare failed: ' . $this->conn->error . ' | SQL: ' . $sql);
            return false;
        }

        if ($types !== '' && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        // SELECT returns a result set; INSERT/UPDATE/DELETE returns true
        $result = $stmt->get_result();
        if ($result === false && $stmt->errno === 0) {
            // Non-SELECT statement executed successfully
            return true;
        }

        return $result;
    }

    /**
     * Returns the auto-incremented ID of the last INSERT.
     */
    public function lastInsertId(): int
    {
        return (int) $this->conn->insert_id;
    }

    /**
     * Returns the number of rows affected by the last write query.
     */
    public function affectedRows(): int
    {
        return (int) $this->conn->affected_rows;
    }

    /** Prevent cloning of the singleton */
    private function __clone() {}
}
