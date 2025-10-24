<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Owner Model
 *
 * Handles owner data operations using stored procedures.
 *
 * Key Stored Procedures:
 * - spOwner_Get: Get owner by OwnerKey
 * - spOwner_Save: Save/update owner (18 parameters)
 * - spOwnerNameAddresses_Get: Get address NameKeys for owner
 *
 * Junction Tables:
 * - tOwner_tNameAddress: Links owners to addresses
 * - tOwner_tComment: Links owners to comments
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class OwnerModel extends BaseModel
{
    /**
     * Get owner by OwnerKey
     *
     * @param int $ownerKey Owner key
     * @return array|null Owner data or null if not found
     */
    public function getOwner(int $ownerKey): ?array
    {
        if ($ownerKey <= 0) {
            return null;
        }

        $results = $this->callStoredProcedure('spOwner_Get', [$ownerKey]);

        if (!empty($results) && is_array($results)) {
            $owner = $results[0];
            // Add the OwnerKey to the result for consistency
            $owner['OwnerKey'] = $ownerKey;
            return $owner;
        }

        return null;
    }

    /**
     * Save owner (create or update)
     *
     * @param array $ownerData Owner data array
     * @return bool True on success, false on failure
     */
    public function saveOwner(array $ownerData): bool
    {
        try {
            // Get OwnerKey or generate new one
            $ownerKey = $ownerData['OwnerKey'] ?? 0;

            if ($ownerKey == 0) {
                // New owner - get next key
                $ownerKey = $this->getNextKey('Owner');
                if ($ownerKey <= 0) {
                    log_message('error', 'Failed to get next owner key');
                    return false;
                }
            }

            // Convert empty dates to 1899-12-30 (database null date)
            $startDate = !empty($ownerData['StartDate']) ? $ownerData['StartDate'] : '1899-12-30';
            $endDate = !empty($ownerData['EndDate']) ? $ownerData['EndDate'] : '1899-12-30';

            // Prepare parameters for spOwner_Save (18 parameters)
            $params = [
                $ownerKey,                                          // @Key INT
                $ownerData['OwnerID'] ?? '',                        // @OwnerID CHAR(9)
                $ownerData['IDType'] ?? 'O',                        // @IDType CHAR(3)
                $ownerData['FirstName'] ?? '',                      // @FirstName VARCHAR(15)
                $ownerData['MiddleName'] ?? '',                     // @MiddleName VARCHAR(15)
                $ownerData['LastName'] ?? '',                       // @LastName VARCHAR(35)
                $ownerData['OtherName'] ?? '',                      // @OtherName VARCHAR(35)
                $startDate,                                         // @StartDate DATETIME
                $endDate,                                           // @EndDate DATETIME
                isset($ownerData['DirectDeposit']) ? 1 : 0,         // @DirectDeposit BIT
                intval($ownerData['DDId'] ?? 0),                    // @DDId INT
                $ownerData['Email'] ?? null,                        // @Email VARCHAR(50)
                !empty($ownerData['DriverKey']) ? intval($ownerData['DriverKey']) : null,  // @DriverKey INT
                floatval($ownerData['MinCheck'] ?? 0.00),           // @MinCheck DECIMAL(18,2)
                floatval($ownerData['MaxDebt'] ?? 0.00),            // @MaxDebt DECIMAL(18,2)
                floatval($ownerData['MinDeduction'] ?? 0.00),       // @MinDeduction DECIMAL(18,2)
                floatval($ownerData['VerifiedCheck'] ?? 0.00),      // @VerifiedCheck DECIMAL(18,2)
                intval($ownerData['CompanyID'] ?? 3),               // @CompanyID INT, default 3
                isset($ownerData['ContractSigned']) ? 1 : 0         // @ContractSigned BIT
            ];

            log_message('info', "spOwner_Save called with params: " . json_encode($params));

            $returnCode = $this->callStoredProcedureWithReturn('spOwner_Save', $params);

            log_message('info', "spOwner_Save returned: {$returnCode} ({$this->getReturnCodeMessage($returnCode)}) for OwnerKey: {$ownerKey}");

            if ($returnCode === self::SRV_NORMAL) {
                return true;
            } else {
                log_message('error', "spOwner_Save failed: {$this->getReturnCodeMessage($returnCode)}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saving owner: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Search for owner by name (exact or partial match)
     *
     * @param string $name Owner name to search (LastName, FirstName format)
     * @return array|null Owner data or null if not found
     */
    public function searchOwnerByName(string $name): ?array
    {
        if (empty($name)) {
            return null;
        }

        // Try exact match first (LastName, FirstName)
        $sql = "SELECT TOP 1 OwnerKey, LastName, FirstName, MiddleName, EndDate
                FROM dbo.tOwner
                WHERE CONCAT(LastName, ', ', FirstName) = ?";

        $results = $this->db->query($sql, [$name])->getResultArray();

        if (!empty($results)) {
            $ownerKey = $results[0]['OwnerKey'];
            return $this->getOwner($ownerKey);
        }

        // Try partial match on LastName if exact match fails
        $sql = "SELECT TOP 1 OwnerKey, LastName, FirstName, MiddleName, EndDate
                FROM dbo.tOwner
                WHERE LastName LIKE ? OR FirstName LIKE ?
                ORDER BY EndDate DESC, LastName, FirstName";

        $searchTerm = '%' . $name . '%';
        $results = $this->db->query($sql, [$searchTerm, $searchTerm])->getResultArray();

        if (!empty($results)) {
            $ownerKey = $results[0]['OwnerKey'];
            return $this->getOwner($ownerKey);
        }

        return null;
    }

    /**
     * Search owners for autocomplete
     *
     * @param string $term Search term
     * @param bool $includeInactive Include inactive owners
     * @return array Array of owners for autocomplete
     */
    public function searchOwnersForAutocomplete(string $term, bool $includeInactive = false): array
    {
        if (strlen($term) < 1) {
            return [];
        }

        // Build query to search by LastName, FirstName, or OwnerKey (case-insensitive)
        // Note: Active status is determined by EndDate
        // EndDate = '1899-12-30' means no end date (active)
        // EndDate != '1899-12-30' means has end date (inactive)
        $sql = "SELECT TOP 20 OwnerKey, LastName, FirstName, MiddleName, EndDate
                FROM dbo.tOwner
                WHERE (UPPER(LastName) LIKE UPPER(?)
                   OR UPPER(FirstName) LIKE UPPER(?)
                   OR CAST(OwnerKey AS VARCHAR) LIKE ?)";

        if (!$includeInactive) {
            $sql .= " AND (EndDate IS NULL OR EndDate = '1899-12-30')";
        }

        $sql .= " ORDER BY LastName, FirstName";

        $searchTerm = '%' . $term . '%';

        // Debug logging
        $currentDb = $this->db->getDatabase();
        log_message('info', "OwnerModel search - Current DB: {$currentDb}, Query: {$sql}, SearchTerm: {$searchTerm}");

        $results = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm])->getResultArray();

        log_message('info', "OwnerModel search - Query returned " . count($results) . " rows");

        $owners = [];
        foreach ($results as $row) {
            // Determine active status by EndDate
            $isActive = (empty($row['EndDate']) || $row['EndDate'] == '1899-12-30 00:00:00.000');

            $fullName = trim($row['LastName']) . ', ' . trim($row['FirstName']);
            if (!empty($row['MiddleName'])) {
                $fullName .= ' ' . trim($row['MiddleName']);
            }

            $owners[] = [
                'id' => $row['OwnerKey'],
                'label' => $fullName . ' (' . $row['OwnerKey'] . ')',
                'value' => $row['OwnerKey'],
                'active' => $isActive
            ];
        }

        return $owners;
    }

    /**
     * Get owner's address (returns first address from junction table)
     *
     * @param int $ownerKey Owner key
     * @return array|null Address data or null if not found
     */
    public function getOwnerAddress(int $ownerKey): ?array
    {
        if ($ownerKey <= 0) {
            return null;
        }

        // Get NameKeys linked to this owner
        $results = $this->callStoredProcedure('spOwnerNameAddresses_Get', [$ownerKey]);

        // Debug logging
        log_message('info', 'OwnerModel::getOwnerAddress - OwnerKey: ' . $ownerKey);
        log_message('info', 'OwnerModel::getOwnerAddress - spOwnerNameAddresses_Get results: ' . json_encode($results));

        if (!empty($results) && is_array($results)) {
            // The stored procedure returns NameKey column
            if (!isset($results[0]['NameKey'])) {
                log_message('error', 'NameKey column not found in results. Available columns: ' . json_encode(array_keys($results[0])));
                return null;
            }

            // Get the first NameKey (owners have exactly 1 address in practice)
            $nameKey = $results[0]['NameKey'];
            log_message('info', 'OwnerModel::getOwnerAddress - Found NameKey: ' . $nameKey);

            // Load the address details
            $addressModel = new \App\Models\AddressModel();
            // Ensure the model's database is set to the current customer database
            $addressModel->db = $this->db;

            return $addressModel->getAddress($nameKey);
        }

        log_message('info', 'OwnerModel::getOwnerAddress - No address found for owner');
        return null;
    }

    /**
     * Get owner's contacts using 3-level chain retrieval
     *
     * Chain: Owner → tOwner_tNameAddress → tNameAddress → tNameAddress_tContact → tContact
     *
     * @param int $ownerKey Owner key
     * @return array Array of contact data with proper column mapping
     */
    public function getOwnerContacts(int $ownerKey): array
    {
        if ($ownerKey <= 0) {
            return [];
        }

        $contacts = [];

        try {
            // Step 1: Get NameKeys linked to this owner
            $nameKeyResults = $this->callStoredProcedure('spOwnerNameAddresses_Get', [$ownerKey]);

            log_message('info', 'OwnerModel::getOwnerContacts - OwnerKey: ' . $ownerKey);
            log_message('info', 'OwnerModel::getOwnerContacts - spOwnerNameAddresses_Get results: ' . json_encode($nameKeyResults));

            if (!empty($nameKeyResults) && is_array($nameKeyResults)) {
                $contactModel = new \App\Models\ContactModel();
                // Ensure the model's database is set to the current customer database
                $contactModel->db = $this->db;

                // Step 2: For each NameKey, get ContactKeys
                foreach ($nameKeyResults as $nameKeyRow) {
                    $nameKey = $nameKeyRow['NameKey'] ?? 0;

                    if ($nameKey > 0) {
                        $contactKeys = $contactModel->getContactKeysForNameKey($nameKey);

                        log_message('info', 'OwnerModel::getOwnerContacts - NameKey: ' . $nameKey . ', ContactKeys: ' . json_encode($contactKeys));

                        // Step 3: For each ContactKey, get full contact details
                        foreach ($contactKeys as $contactKey) {
                            if ($contactKey > 0) {
                                $contact = $contactModel->getContact($contactKey);

                                if ($contact !== null) {
                                    $contacts[] = $contact;
                                }
                            }
                        }
                    }
                }
            }

            log_message('info', 'OwnerModel::getOwnerContacts - Total contacts found: ' . count($contacts));
        } catch (\Exception $e) {
            log_message('error', 'OwnerModel::getOwnerContacts - Error: ' . $e->getMessage());
        }

        return $contacts;
    }

    /**
     * Get all comments for an owner
     * Returns array of full comment data with user and date information
     *
     * @param int $ownerKey Owner key
     * @return array Array of comments
     */
    public function getOwnerComments(int $ownerKey): array
    {
        $comments = [];

        try {
            log_message('info', 'OwnerModel::getOwnerComments - OwnerKey: ' . $ownerKey);

            if ($ownerKey > 0) {
                $commentModel = new \App\Models\CommentModel();

                // Step 1: Get CommentKeys for this owner
                $commentKeys = $commentModel->getCommentKeysForOwner($ownerKey);

                log_message('info', 'OwnerModel::getOwnerComments - CommentKeys: ' . json_encode($commentKeys));

                // Step 2: For each CommentKey, get full comment details
                foreach ($commentKeys as $commentKey) {
                    if ($commentKey > 0) {
                        $comment = $commentModel->getComment($commentKey);

                        if ($comment !== null) {
                            $comments[] = $comment;
                        }
                    }
                }
            }

            log_message('info', 'OwnerModel::getOwnerComments - Total comments found: ' . count($comments));
        } catch (\Exception $e) {
            log_message('error', 'OwnerModel::getOwnerComments - Error: ' . $e->getMessage());
        }

        return $comments;
    }
}
