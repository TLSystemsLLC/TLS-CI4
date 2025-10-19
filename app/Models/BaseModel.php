<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Base Model for TLS Operations
 *
 * Provides helper methods for calling SQL Server stored procedures
 * All TLS models should extend this class
 */
class BaseModel extends Model
{
    /**
     * Call a stored procedure and return result set
     *
     * @param string $procedureName Stored procedure name
     * @param array $parameters Parameters to pass to stored procedure
     * @param string|null $database Optional database name to switch to
     * @return array Result set from stored procedure
     */
    protected function callStoredProcedure(string $procedureName, array $parameters = [], ?string $database = null): array
    {
        // Switch database if specified
        if ($database !== null) {
            $this->db->setDatabase($database);
        }

        // Build parameter placeholders
        $placeholders = str_repeat('?,', count($parameters));
        $placeholders = rtrim($placeholders, ',');

        // Execute stored procedure
        $sql = "EXEC {$procedureName}";
        if (!empty($parameters)) {
            $sql .= " {$placeholders}";
        }

        $query = $this->db->query($sql, $parameters);

        if ($query === false) {
            return [];
        }

        return $query->getResultArray();
    }

    /**
     * Call a stored procedure that returns a status code
     *
     * @param string $procedureName Stored procedure name
     * @param array $parameters Parameters to pass to stored procedure
     * @param string|null $database Optional database name to switch to
     * @return int Return code from stored procedure
     */
    protected function callStoredProcedureWithReturn(string $procedureName, array $parameters = [], ?string $database = null): int
    {
        // Switch database if specified
        if ($database !== null) {
            $this->db->setDatabase($database);
        }

        // Build parameter placeholders
        $placeholders = str_repeat('?,', count($parameters));
        $placeholders = rtrim($placeholders, ',');

        // Execute stored procedure with return value
        $sql = "DECLARE @ReturnValue INT; EXEC @ReturnValue = {$procedureName}";
        if (!empty($parameters)) {
            $sql .= " {$placeholders}";
        }
        $sql .= "; SELECT @ReturnValue as ReturnValue";

        $query = $this->db->query($sql, $parameters);

        if ($query === false) {
            return -1; // Error indicator
        }

        $result = $query->getRowArray();
        return (int)($result['ReturnValue'] ?? -1);
    }

    /**
     * Get next surrogate key from tSurrogateKey table
     *
     * @param string $tableName Table name to get next key for
     * @param string|null $database Optional database name
     * @return int Next key value
     */
    protected function getNextKey(string $tableName, ?string $database = null): int
    {
        // Switch database if specified
        if ($database !== null) {
            $this->db->setDatabase($database);
        }

        // spGetNextKey uses OUTPUT parameter
        $sql = "DECLARE @NextKey INT; EXEC spGetNextKey @TableName = ?, @NextKey = @NextKey OUTPUT; SELECT @NextKey as NextKey";

        $query = $this->db->query($sql, [$tableName]);

        if ($query === false) {
            return 0;
        }

        $result = $query->getRowArray();
        return (int)($result['NextKey'] ?? 0);
    }

    /**
     * Switch to a different database
     *
     * @param string $database Database name
     * @return void
     */
    protected function switchDatabase(string $database): void
    {
        $this->db->setDatabase($database);
    }

    /**
     * Execute raw SQL query with parameters
     * Useful for complex queries that don't fit stored procedure pattern
     *
     * @param string $sql SQL query
     * @param array $parameters Query parameters
     * @return array Result set
     */
    protected function rawQuery(string $sql, array $parameters = []): array
    {
        $query = $this->db->query($sql, $parameters);

        if ($query === false) {
            return [];
        }

        return $query->getResultArray();
    }
}
