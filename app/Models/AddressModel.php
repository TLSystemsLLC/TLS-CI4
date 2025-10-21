<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Address Model
 *
 * Handles address data operations using stored procedures.
 * Addresses are stored in tNameAddress and linked to entities via junction tables.
 *
 * Key Stored Procedures:
 * - spNameAddress_Get: Get address by NameKey
 * - spNameAddress_Save: Save/update address (15 parameters)
 * - spAgentNameAddresses_Get: Get all NameKeys for an agent
 * - spAgentNameAddresses_Save: Link address to agent
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class AddressModel extends BaseModel
{
    /**
     * Get address by NameKey
     *
     * @param int $nameKey Address key
     * @return array|null Address data or null if not found
     */
    public function getAddress(int $nameKey): ?array
    {
        if ($nameKey <= 0) {
            return null;
        }

        $results = $this->callStoredProcedure('spNameAddress_Get', [$nameKey]);

        if (!empty($results) && is_array($results)) {
            $address = $results[0];
            // Add the NameKey to the result for consistency
            $address['NameKey'] = $nameKey;
            return $address;
        }

        return null;
    }

    /**
     * Save address (create or update)
     *
     * @param array $addressData Address data array
     * @return int NameKey of saved address, or 0 on failure
     */
    public function saveAddress(array $addressData): int
    {
        try {
            // Get NameKey or generate new one
            $nameKey = $addressData['NameKey'] ?? 0;

            if ($nameKey == 0) {
                // New address - get next key
                $nameKey = $this->getNextKey('tNameAddress');
                if ($nameKey <= 0) {
                    log_message('error', 'Failed to get next address key');
                    return 0;
                }
            }

            // Prepare parameters for spNameAddress_Save (15 parameters)
            $params = [
                $nameKey,                                    // @Key INT
                $addressData['Name1'] ?? '',                 // @Name1 VARCHAR(35)
                $addressData['NameQual'] ?? 'AG',            // @NameQual CHAR(3) - 'AG' for Agent
                $addressData['Name2'] ?? '',                 // @Name2 VARCHAR(35)
                $addressData['Address1'] ?? '',              // @Address1 VARCHAR(35)
                $addressData['Address2'] ?? '',              // @Address2 VARCHAR(35)
                $addressData['Address3'] ?? '',              // @Address3 VARCHAR(35)
                $addressData['Address4'] ?? '',              // @Address4 VARCHAR(35)
                $addressData['City'] ?? '',                  // @City CHAR(18)
                $addressData['County'] ?? '',                // @County CHAR(2)
                $addressData['Zip'] ?? '',                   // @Zip CHAR(9)
                $addressData['State'] ?? '',                 // @State CHAR(2)
                $addressData['QuickKey'] ?? '',              // @QuickKey CHAR(6)
                $addressData['Phone'] ?? '',                 // @Phone CHAR(15)
                $addressData['FreightCoordinator'] ?? ''     // @FreightCoordinator VARCHAR(50)
            ];

            // Call the stored procedure
            $returnCode = $this->callStoredProcedureWithReturn('spNameAddress_Save', $params);

            log_message('info', "spNameAddress_Save returned: {$returnCode} ({$this->getReturnCodeMessage($returnCode)}) for NameKey: {$nameKey}");

            // Check for success (srvNormal = 0)
            if ($returnCode === self::SRV_NORMAL) {
                return $nameKey;
            } else {
                log_message('error', "spNameAddress_Save failed: {$this->getReturnCodeMessage($returnCode)}");
                return 0;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saving address: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all addresses for an agent
     *
     * @param int $agentKey Agent key
     * @return array Array of NameKeys linked to this agent
     */
    public function getAgentAddresses(int $agentKey): array
    {
        if ($agentKey <= 0) {
            return [];
        }

        $results = $this->callStoredProcedure('spAgentNameAddresses_Get', [$agentKey]);

        if (!empty($results) && is_array($results)) {
            // Extract NameKey values from result set
            return array_column($results, 'NameKey');
        }

        return [];
    }

    /**
     * Link address to agent via junction table
     *
     * @param int $agentKey Agent key
     * @param int $nameKey Address key
     * @return bool True on success, false on failure
     */
    public function linkAgentAddress(int $agentKey, int $nameKey): bool
    {
        try {
            if ($agentKey <= 0 || $nameKey <= 0) {
                return false;
            }

            $returnCode = $this->callStoredProcedureWithReturn(
                'spAgentNameAddresses_Save',
                [$agentKey, $nameKey]
            );

            return ($returnCode === 0);
        } catch (\Exception $e) {
            log_message('error', 'Error linking address to agent: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create blank address for new entity
     *
     * @param string $nameQual Name qualifier (AG for Agent, DR for Driver, etc.)
     * @return int NameKey of created address, or 0 on failure
     */
    public function createBlankAddress(string $nameQual = 'AG'): int
    {
        $blankAddress = [
            'NameKey' => 0,
            'Name1' => '',
            'Name2' => '',
            'NameQual' => $nameQual,
            'Address1' => '',
            'Address2' => '',
            'City' => '',
            'State' => '',
            'Zip' => '',
            'Phone' => ''
        ];

        return $this->saveAddress($blankAddress);
    }
}
