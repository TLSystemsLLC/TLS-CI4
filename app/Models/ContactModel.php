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
    public function __construct()
    {
        parent::__construct();

        // Initialize database connection if not already set
        if (!isset($this->db)) {
            $this->db = \Config\Database::connect();
        }

        // Set customer database context from session
        $session = \Config\Services::session();
        $customerDb = $session->get('customer_db');
        if ($customerDb) {
            $this->db->setDatabase($customerDb);
            log_message('info', "ContactModel initialized with database: {$customerDb}");
        }
    }

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

            // Return actual database fields only
            return [
                'ContactKey' => $contactKey,
                'ContactName' => $contact['ContactName'] ?? '',
                'ContactFunction' => $contact['ContactFunction'] ?? '',
                'TelephoneNo' => $contact['TelephoneNo'] ?? ''
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
            $isNewContact = ($contactKey == 0);

            if ($isNewContact) {
                // New contact - get next key
                $contactKey = $this->getNextKey('tContact');
                if ($contactKey <= 0) {
                    log_message('error', 'Failed to get next contact key');
                    return 0;
                }
            }

            // Prepare parameters for spContact_Save (4 parameters only!)
            // @Key INT, @ContactName VARCHAR(35), @ContactFunction CHAR(3), @TelephoneNo VARCHAR(20)
            $params = [
                $contactKey,                                    // @Key INT
                $contactData['ContactName'] ?? '',              // @ContactName VARCHAR(35)
                $contactData['ContactFunction'] ?? '',          // @ContactFunction CHAR(3)
                $contactData['TelephoneNo'] ?? ''               // @TelephoneNo VARCHAR(20)
            ];

            // Call the stored procedure
            $result = $this->callStoredProcedure('spContact_Save', $params);

            log_message('info', "spContact_Save executed successfully for ContactKey: {$contactKey}");

            // Link contact to entity's NameKey via junction table (ONLY for new contacts)
            if ($isNewContact && $entityKey > 0) {
                $this->linkContactToEntity($contactKey, $entityKey);
            }

            return $contactKey;
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

    /**
     * Link contact to entity via NameKey junction table
     * Uses spAgentNameAddresses_Get to find NameKey, then spContacts_Save to link
     *
     * @param int $contactKey Contact key
     * @param int $entityKey Entity key (AgentKey, DriverKey, etc.)
     * @return bool True on success
     */
    private function linkContactToEntity(int $contactKey, int $entityKey): bool
    {
        try {
            // Get NameKey(s) for this entity
            $results = $this->callStoredProcedure('spAgentNameAddresses_Get', [$entityKey]);

            if (!empty($results) && is_array($results)) {
                $nameKey = $results[0]['NameKey'] ?? 0;

                if ($nameKey > 0) {
                    // Link contact to address using spContacts_Save
                    $this->callStoredProcedure('spContacts_Save', [$nameKey, $contactKey]);
                    log_message('info', "Linked ContactKey {$contactKey} to NameKey {$nameKey}");
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            log_message('error', 'Error linking contact to entity: ' . $e->getMessage());
            return false;
        }
    }
}
