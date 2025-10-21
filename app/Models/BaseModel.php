<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Base Model for TLS Operations
 *
 * Provides helper methods for calling SQL Server stored procedures
 * All TLS models should extend this class
 *
 * Standard Stored Procedure Return Codes:
 * - srvNormal = 0                  (Success)
 * - srvBatchAlreadyPosted = 30     (Batch already posted)
 * - srvBatchNotPrinted = 31        (Batch not printed)
 * - srvJEOutOfBalance = 32         (Journal entry out of balance)
 * - srvJEBatchOutOfBalance = 33    (Journal entry batch out of balance)
 * - srvPeriodClosed = 34           (Period closed)
 * - srvRecordNotDeleted = 96       (Record not deleted - DELETE failed)
 * - srvRecordNotAdded = 97         (Record not added/updated - INSERT or UPDATE failed)
 * - srvRecordLocked = 98           (Record locked)
 * - srvRecordNotFound = 99         (Record not found)
 */
class BaseModel extends Model
{
    // Stored procedure return code constants
    const SRV_NORMAL = 0;
    const SRV_BATCH_ALREADY_POSTED = 30;
    const SRV_BATCH_NOT_PRINTED = 31;
    const SRV_JE_OUT_OF_BALANCE = 32;
    const SRV_JE_BATCH_OUT_OF_BALANCE = 33;
    const SRV_PERIOD_CLOSED = 34;
    const SRV_RECORD_NOT_DELETED = 96;
    const SRV_RECORD_NOT_ADDED = 97;
    const SRV_RECORD_LOCKED = 98;
    const SRV_RECORD_NOT_FOUND = 99;
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

        // For SQL Server SQLSRV driver, we need to use sqlsrv functions directly
        // to handle multiple result sets with sqlsrv_next_result()
        $conn = $this->db->connID; // Get the underlying sqlsrv connection resource

        try {
            // Execute the query using sqlsrv_query
            $stmt = sqlsrv_query($conn, $sql, $parameters);

            if ($stmt === false) {
                $errors = sqlsrv_errors();
                log_message('error', "callStoredProcedureWithReturn - sqlsrv_query failed: " . json_encode($errors));
                return -1;
            }

            // Move through any result sets to reach our SELECT @ReturnValue statement
            // Even though the stored procedure doesn't return result sets,
            // we need to iterate to get to our final SELECT
            $result = null;
            do {
                // Fetch all rows from this result set
                $rows = [];
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $rows[] = $row;
                }

                if (!empty($rows)) {
                    $result = $rows[0]; // Keep the last non-empty result
                }
            } while (sqlsrv_next_result($stmt));

            // Free the statement
            sqlsrv_free_stmt($stmt);

            if ($result === null || !isset($result['ReturnValue'])) {
                log_message('error', "callStoredProcedureWithReturn - No ReturnValue found for: {$procedureName}");
                return -1;
            }

            return (int)$result['ReturnValue'];

        } catch (\Exception $e) {
            log_message('error', "callStoredProcedureWithReturn exception for {$procedureName}: " . $e->getMessage());
            return -1;
        }
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

        log_message('info', "getNextKey called for table: {$tableName}");

        // Use sqlsrv functions directly to handle OUTPUT parameter correctly
        $conn = $this->db->connID;

        try {
            $stmt = sqlsrv_query($conn, $sql, [$tableName]);

            if ($stmt === false) {
                $errors = sqlsrv_errors();
                log_message('error', "getNextKey - sqlsrv_query failed for table {$tableName}: " . json_encode($errors));
                return 0;
            }

            // Move through result sets to reach our SELECT @NextKey statement
            $result = null;
            do {
                $rows = [];
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $rows[] = $row;
                }

                if (!empty($rows)) {
                    $result = $rows[0];
                }
            } while (sqlsrv_next_result($stmt));

            sqlsrv_free_stmt($stmt);

            if ($result === null || !isset($result['NextKey'])) {
                log_message('error', "getNextKey - No NextKey found for table: {$tableName}");
                return 0;
            }

            $nextKey = (int)$result['NextKey'];
            log_message('info', "getNextKey returned: {$nextKey} for table: {$tableName}");

            return $nextKey;

        } catch (\Exception $e) {
            log_message('error', "getNextKey exception for {$tableName}: " . $e->getMessage());
            return 0;
        }
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

    /**
     * Get human-readable message for stored procedure return code
     *
     * @param int $returnCode Return code from stored procedure
     * @return string Human-readable message
     */
    protected function getReturnCodeMessage(int $returnCode): string
    {
        switch ($returnCode) {
            case self::SRV_NORMAL:
                return 'Success';
            case self::SRV_BATCH_ALREADY_POSTED:
                return 'Batch already posted';
            case self::SRV_BATCH_NOT_PRINTED:
                return 'Batch not printed';
            case self::SRV_JE_OUT_OF_BALANCE:
                return 'Journal entry out of balance';
            case self::SRV_JE_BATCH_OUT_OF_BALANCE:
                return 'Journal entry batch out of balance';
            case self::SRV_PERIOD_CLOSED:
                return 'Period closed';
            case self::SRV_RECORD_NOT_DELETED:
                return 'Record not deleted (DELETE failed)';
            case self::SRV_RECORD_NOT_ADDED:
                return 'Record not added/updated (INSERT or UPDATE failed)';
            case self::SRV_RECORD_LOCKED:
                return 'Record locked';
            case self::SRV_RECORD_NOT_FOUND:
                return 'Record not found';
            default:
                return "Unknown return code: {$returnCode}";
        }
    }

    /**
     * Get validation table entries for a specific column
     * Uses validation table cached at login for performance
     *
     * @param string $columnName Column name to filter by (e.g., 'ContactFunction')
     * @return array Array of validation entries with Code and Description
     */
    public function getValidationOptions(string $columnName): array
    {
        $session = \Config\Services::session();

        // Get validation table from session (loaded at login)
        $validationTable = $session->get('validation_table');

        if ($validationTable === null) {
            log_message('warning', 'Validation table not found in session - should have been loaded at login');
            return [];
        }

        // Filter by column name
        $filtered = [];
        foreach ($validationTable as $row) {
            if ($row['ColumnName'] === $columnName) {
                $filtered[] = [
                    'Code' => $row['Code'],
                    'Description' => $row['Description']
                ];
            }
        }

        return $filtered;
    }
}
