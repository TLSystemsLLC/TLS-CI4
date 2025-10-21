<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Contact Model
 *
 * Handles contact data operations using stored procedures.
 * Contacts are linked to entities via a 3-level chain:
 *   Agent → tAgents_tNameAddress → tNameAddress → tNameAddress_tContact → tContact
 *
 * Key Stored Procedures:
 * - spContact_Get: Get contact by ContactKey
 * - spContact_Save: Save/update contact (9 parameters)
 * - spContact_Delete: Delete contact
 * - spContacts_Get: Get ContactKeys for a NameKey
 *
 * CRITICAL: Database columns use different names than UI:
 *   TelephoneNo → Phone
 *   CellNo → Mobile
 *   ContactFunction → Relationship
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class ContactModel extends BaseModel
{
    /**
     * Get contact by ContactKey
     *
     * @param int $contactKey Contact key
     * @return array|null Contact data or null if not found
     */
    public function getContact(int $contactKey): ?array
    {
        if ($contactKey <= 0) {
            return null;
        }

        $results = $this->callStoredProcedure('spContact_Get', [$contactKey]);

        if (!empty($results) && is_array($results)) {
            $contact = $results[0];

            // CRITICAL: Map database column names to UI-friendly names
            return [
                'ContactKey' => $contactKey,
                'FirstName' => $contact['FirstName'] ?? '',
                'LastName' => $contact['LastName'] ?? '',
                'ContactName' => trim(($contact['FirstName'] ?? '') . ' ' . ($contact['LastName'] ?? '')),
                'Phone' => $contact['TelephoneNo'] ?? '',
                'Mobile' => $contact['CellNo'] ?? '',
                'Email' => $contact['Email'] ?? '',
                'Relationship' => $contact['ContactFunction'] ?? '',
                'IsPrimary' => $contact['IsPrimary'] ?? 0
            ];
        }

        return null;
    }

    /**
     * Get all ContactKeys for a given NameKey
     *
     * @param int $nameKey NameKey from tNameAddress
     * @return array Array of ContactKey values
     */
    public function getContactKeysForNameKey(int $nameKey): array
    {
        if ($nameKey <= 0) {
            return [];
        }

        $results = $this->callStoredProcedure('spContacts_Get', [$nameKey]);

        if (!empty($results) && is_array($results)) {
            // Extract ContactKey values from result set
            return array_column($results, 'ContactKey');
        }

        return [];
    }

    /**
     * Save contact (create or update)
     *
     * @param array $contactData Contact data array
     * @param int $entityKey Entity key (AgentKey, DriverKey, etc.)
     * @return int ContactKey of saved contact, or 0 on failure
     */
    public function saveContact(array $contactData, int $entityKey): int
    {
        try {
            // Get ContactKey or generate new one
            $contactKey = $contactData['ContactKey'] ?? 0;

            if ($contactKey == 0) {
                // New contact - get next key
                $contactKey = $this->getNextKey('tContact');
                if ($contactKey <= 0) {
                    log_message('error', 'Failed to get next contact key');
                    return 0;
                }
            }

            // Prepare parameters for spContact_Save (9 parameters)
            $params = [
                $contactKey,                                    // @ContactKey INT
                $entityKey,                                     // @EntityKey INT (AgentKey, DriverKey, etc.)
                $contactData['FirstName'] ?? '',                // @FirstName VARCHAR(30)
                $contactData['LastName'] ?? '',                 // @LastName VARCHAR(30)
                $contactData['Phone'] ?? '',                    // @TelephoneNo VARCHAR(20)
                $contactData['Mobile'] ?? '',                   // @CellNo VARCHAR(20)
                $contactData['Email'] ?? '',                    // @Email VARCHAR(50)
                $contactData['Relationship'] ?? '',             // @ContactFunction VARCHAR(30)
                isset($contactData['IsPrimary']) ? 1 : 0        // @IsPrimary BIT
            ];

            // Call the stored procedure
            $returnCode = $this->callStoredProcedureWithReturn('spContact_Save', $params);

            log_message('info', "spContact_Save returned: {$returnCode} ({$this->getReturnCodeMessage($returnCode)}) for ContactKey: {$contactKey}");

            // Check for success (srvNormal = 0)
            if ($returnCode === self::SRV_NORMAL) {
                return $contactKey;
            } else {
                log_message('error', "spContact_Save failed: {$this->getReturnCodeMessage($returnCode)}");
                return 0;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saving contact: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete contact
     *
     * @param int $contactKey Contact key to delete
     * @return bool True on success, false on failure
     */
    public function deleteContact(int $contactKey): bool
    {
        try {
            if ($contactKey <= 0) {
                return false;
            }

            $returnCode = $this->callStoredProcedureWithReturn('spContact_Delete', [$contactKey]);

            log_message('info', "spContact_Delete returned: {$returnCode} ({$this->getReturnCodeMessage($returnCode)}) for ContactKey: {$contactKey}");

            return ($returnCode === self::SRV_NORMAL);
        } catch (\Exception $e) {
            log_message('error', 'Error deleting contact: ' . $e->getMessage());
            return false;
        }
    }
}
