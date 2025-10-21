<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Agent Model
 *
 * Handles agent data operations using stored procedures.
 *
 * Key Stored Procedures:
 * - spAgent_Get: Get agent by AgentKey
 * - spAgent_Save: Save/update agent (17 parameters)
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class AgentModel extends BaseModel
{
    /**
     * Get agent by AgentKey
     *
     * @param int $agentKey Agent key
     * @return array|null Agent data or null if not found
     */
    public function getAgent(int $agentKey): ?array
    {
        if ($agentKey <= 0) {
            return null;
        }

        $results = $this->callStoredProcedure('spAgent_Get', [$agentKey]);

        if (!empty($results) && is_array($results)) {
            $agent = $results[0];
            // Add the AgentKey to the result for consistency
            $agent['AgentKey'] = $agentKey;
            return $agent;
        }

        return null;
    }

    /**
     * Save agent (create or update)
     *
     * @param array $agentData Agent data array
     * @return bool True on success, false on failure
     */
    public function saveAgent(array $agentData): bool
    {
        try {
            // Get AgentKey or generate new one
            $agentKey = $agentData['AgentKey'] ?? 0;

            if ($agentKey == 0) {
                // New agent - get next key
                $agentKey = $this->getNextKey('tAgents');
                if ($agentKey <= 0) {
                    log_message('error', 'Failed to get next agent key');
                    return false;
                }
            }

            // Convert empty dates to 1899-12-30 (database null date)
            $startDate = !empty($agentData['StartDate']) ? $agentData['StartDate'] : '1899-12-30';
            $endDate = !empty($agentData['EndDate']) ? $agentData['EndDate'] : '1899-12-30';

            // Use Active flag from form data (validated in controller to match EndDate)
            $isActive = isset($agentData['Active']) ? 1 : 0;

            // Prepare parameters for spAgent_Save (17 parameters)
            $params = [
                $agentKey,                                      // @Key INT
                $agentData['Name'] ?? '',                       // @Name VARCHAR(35)
                $isActive,                                      // @Active BIT
                $agentData['TaxID'] ?? '',                      // @TaxID CHAR(9)
                $agentData['IDType'] ?? '',                     // @IDType CHAR(3)
                $startDate,                                     // @StartDate DATETIME
                $endDate,                                       // @EndDate DATETIME
                intval($agentData['PayPct'] ?? 0),              // @PayPct TINYINT
                isset($agentData['FullFreightPay']) ? 1 : 0,    // @FullFreightPay BIT
                null,                                           // @TeamKey INT (not used)
                null,                                           // @TeamPay DECIMAL (not used)
                $agentData['Email'] ?? '',                      // @Email VARCHAR(50)
                intval($agentData['Division'] ?? 1),            // @Division INT
                2,                                              // @CompanyID INT (fixed to 2)
                floatval($agentData['BrokerPct'] ?? 0),         // @BrokerPct DECIMAL
                floatval($agentData['FleetPct'] ?? 0),          // @FleetPct DECIMAL
                floatval($agentData['CompanyPct'] ?? 0)         // @CompanyPct DECIMAL
            ];

            $returnCode = $this->callStoredProcedureWithReturn('spAgent_Save', $params);

            log_message('info', "spAgent_Save returned: {$returnCode} ({$this->getReturnCodeMessage($returnCode)}) for AgentKey: {$agentKey}");

            if ($returnCode === self::SRV_NORMAL) {
                return true;
            } else {
                log_message('error', "spAgent_Save failed: {$this->getReturnCodeMessage($returnCode)}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saving agent: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Search for agent by name (exact or partial match)
     *
     * @param string $name Agent name to search
     * @return array|null Agent data or null if not found
     */
    public function searchAgentByName(string $name): ?array
    {
        if (empty($name)) {
            return null;
        }

        // Try exact match first
        $sql = "SELECT TOP 1 AgentKey, Name, ACTIVE
                FROM dbo.tAgents
                WHERE Name = ?";

        $results = $this->db->query($sql, [$name])->getResultArray();

        if (!empty($results)) {
            $agentKey = $results[0]['AgentKey'];
            return $this->getAgent($agentKey);
        }

        // Try partial match if exact match fails
        $sql = "SELECT TOP 1 AgentKey, Name, ACTIVE
                FROM dbo.tAgents
                WHERE Name LIKE ?
                ORDER BY ACTIVE DESC, Name";

        $searchTerm = '%' . $name . '%';
        $results = $this->db->query($sql, [$searchTerm])->getResultArray();

        if (!empty($results)) {
            $agentKey = $results[0]['AgentKey'];
            return $this->getAgent($agentKey);
        }

        return null;
    }

    /**
     * Search agents for autocomplete
     *
     * @param string $term Search term
     * @param bool $includeInactive Include inactive agents
     * @return array Array of agents for autocomplete
     */
    public function searchAgentsForAutocomplete(string $term, bool $includeInactive = false): array
    {
        if (strlen($term) < 1) {
            return [];
        }

        // Build query to search by Name or AgentKey (case-insensitive)
        // Note: Active status is determined by EndDate, not ACTIVE column
        // EndDate = '1899-12-30' means no end date (active)
        // EndDate != '1899-12-30' means has end date (inactive)
        $sql = "SELECT TOP 20 AgentKey, Name, ACTIVE, EndDate
                FROM dbo.tAgents
                WHERE (UPPER(Name) LIKE UPPER(?) OR CAST(AgentKey AS VARCHAR) LIKE ?)";

        if (!$includeInactive) {
            $sql .= " AND (EndDate IS NULL OR EndDate = '1899-12-30')";
        }

        $sql .= " ORDER BY Name";

        $searchTerm = '%' . $term . '%';

        // Debug logging
        $currentDb = $this->db->getDatabase();
        log_message('info', "AgentModel search - Current DB: {$currentDb}, Query: {$sql}, SearchTerm: {$searchTerm}");

        // First, let's see what's actually in the table
        $debugSql = "SELECT TOP 5 AgentKey, Name, ACTIVE, LEN(Name) as NameLength FROM dbo.tAgents ORDER BY AgentKey";
        $debugResults = $this->db->query($debugSql)->getResultArray();
        log_message('info', "AgentModel search - Sample agents in table: " . json_encode($debugResults));

        $results = $this->db->query($sql, [$searchTerm, $searchTerm])->getResultArray();

        log_message('info', "AgentModel search - Query returned " . count($results) . " rows");

        $agents = [];
        foreach ($results as $row) {
            // Determine active status by EndDate, not ACTIVE column
            $isActive = (empty($row['EndDate']) || $row['EndDate'] == '1899-12-30 00:00:00.000');

            $agents[] = [
                'id' => $row['AgentKey'],
                'label' => trim($row['Name']) . ' (' . $row['AgentKey'] . ')',
                'value' => $row['AgentKey'],
                'active' => $isActive
            ];
        }

        return $agents;
    }

    /**
     * Get agent's address (returns first address from junction table)
     *
     * @param int $agentKey Agent key
     * @return array|null Address data or null if not found
     */
    public function getAgentAddress(int $agentKey): ?array
    {
        if ($agentKey <= 0) {
            return null;
        }

        // Get NameKeys linked to this agent
        $results = $this->callStoredProcedure('spAgentNameAddresses_Get', [$agentKey]);

        // Debug logging
        log_message('info', 'AgentModel::getAgentAddress - AgentKey: ' . $agentKey);
        log_message('info', 'AgentModel::getAgentAddress - spAgentNameAddresses_Get results: ' . json_encode($results));

        if (!empty($results) && is_array($results)) {
            // The stored procedure returns NameKey column
            // Check if the column exists in the result
            if (!isset($results[0]['NameKey'])) {
                log_message('error', 'NameKey column not found in results. Available columns: ' . json_encode(array_keys($results[0])));
                return null;
            }

            // Get the first NameKey (agents have exactly 1 address in practice)
            $nameKey = $results[0]['NameKey'];
            log_message('info', 'AgentModel::getAgentAddress - Found NameKey: ' . $nameKey);

            // Load the address details
            $addressModel = new \App\Models\AddressModel();
            // Ensure the model's database is set to the current customer database
            $addressModel->db = $this->db;

            return $addressModel->getAddress($nameKey);
        }

        log_message('info', 'AgentModel::getAgentAddress - No address found for agent');
        return null;
    }
}
