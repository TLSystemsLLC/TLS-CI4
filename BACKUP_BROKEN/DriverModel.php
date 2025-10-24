<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Driver Model
 *
 * Handles driver data operations using stored procedures.
 *
 * Key Stored Procedures:
 * - spDriver_Get: Get driver by DriverKey
 * - spDriver_Save: Save/update driver (33 parameters)
 * - spDriverNameAddresses_Get: Get address NameKeys for driver
 *
 * Junction Tables:
 * - tDriver_tNameAddress: Links drivers to addresses
 * - tDriver_tComment: Links drivers to comments
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class DriverModel extends BaseModel
{
    /**
     * Get driver by DriverKey
     *
     * @param int $driverKey Driver key
     * @return array|null Driver data or null if not found
     */
    public function getDriver(int $driverKey): ?array
    {
        if ($driverKey <= 0) {
            return null;
        }

        $results = $this->callStoredProcedure('spDriver_Get', [$driverKey]);

        if (!empty($results) && is_array($results)) {
            $driver = $results[0];
            // Add the DriverKey to the result for consistency
            $driver['DriverKey'] = $driverKey;
            return $driver;
        }

        return null;
    }

    /**
     * Save driver (create or update)
     *
     * @param array $driverData Driver data array
     * @return bool True on success, false on failure
     */
    public function saveDriver(array $driverData): bool
    {
        try {
            // Get DriverKey or generate new one
            $driverKey = $driverData['DriverKey'] ?? 0;

            if ($driverKey == 0) {
                // New driver - get next key
                $driverKey = $this->getNextKey('Driver');
                if ($driverKey <= 0) {
                    log_message('error', 'Failed to get next driver key');
                    return false;
                }
            }

            // Convert empty dates to 1899-12-30 (database null date)
            $birthDate = !empty($driverData['BirthDate']) ? $driverData['BirthDate'] : '1899-12-30';
            $licenseExpires = !empty($driverData['LicenseExpires']) ? $driverData['LicenseExpires'] : '1899-12-30';
            $physicalDate = !empty($driverData['PhysicalDate']) ? $driverData['PhysicalDate'] : '1899-12-30';
            $physicalExpires = !empty($driverData['PhysicalExpires']) ? $driverData['PhysicalExpires'] : '1899-12-30';
            $startDate = !empty($driverData['StartDate']) ? $driverData['StartDate'] : '1899-12-30';
            $endDate = !empty($driverData['EndDate']) ? $driverData['EndDate'] : '1899-12-30';
            $arcnc = !empty($driverData['ARCNC']) ? $driverData['ARCNC'] : null;
            $txcnc = !empty($driverData['TXCNC']) ? $driverData['TXCNC'] : null;
            $eobrStart = !empty($driverData['EOBRStart']) ? $driverData['EOBRStart'] : null;
            $mvrDue = !empty($driverData['MVRDue']) ? $driverData['MVRDue'] : null;

            // Use Active flag from form data
            $isActive = isset($driverData['Active']) ? 1 : 0;

            // Prepare parameters for spDriver_Save (33 parameters)
            $params = [
                $driverKey,                                          // @Key INT
                $driverData['DriverID'] ?? '',                       // @DriverID CHAR(9)
                $driverData['FirstName'] ?? '',                      // @FirstName VARCHAR(15)
                $driverData['MiddleName'] ?? '',                     // @MiddleName VARCHAR(15)
                $driverData['LastName'] ?? '',                       // @LastName VARCHAR(15)
                $birthDate,                                          // @BirthDate DATETIME
                $driverData['LicenseNumber'] ?? '',                  // @LicenseNumber VARCHAR(15)
                $driverData['LicenseState'] ?? '',                   // @LicenseState CHAR(2)
                $licenseExpires,                                     // @LicenseExpires DATETIME
                $physicalDate,                                       // @PhysicalDate DATETIME
                $physicalExpires,                                    // @PhysicalExpires DATETIME
                $startDate,                                          // @StartDate DATETIME
                $endDate,                                            // @EndDate DATETIME
                $isActive,                                           // @Active BIT
                $driverData['FavoriteRoute'] ?? '',                  // @FavoriteRoute VARCHAR(50)
                $driverData['DriverType'] ?? 'F',                    // @DriverType CHAR(1), default 'F'
                $driverData['Email'] ?? null,                        // @Email VARCHAR(50)
                isset($driverData['TWIC']) ? 1 : 0,                  // @TWIC BIT
                isset($driverData['CoilCert']) ? 1 : 0,              // @CoilCert BIT
                intval($driverData['CompanyID'] ?? 3),               // @CompanyID INT, default 3
                $arcnc,                                              // @ARCNC DATETIME
                $txcnc,                                              // @TXCNC DATETIME
                isset($driverData['CompanyDriver']) ? 1 : 0,         // @CompanyDriver BIT
                isset($driverData['EOBR']) ? 1 : 0,                  // @EOBR BIT
                $eobrStart,                                          // @EOBRStart DATETIME
                floatval($driverData['WeeklyCash'] ?? 0.00),         // @WeeklyCash DECIMAL(6,2)
                isset($driverData['CardException']) ? 1 : 0,         // @CardException BIT
                $driverData['DriverSpec'] ?? 'OTH',                  // @DriverSpec CHAR(3), default 'OTH'
                isset($driverData['MedicalVerification']) ? 1 : 0,   // @MedicalVerification BIT
                $mvrDue,                                             // @MVRDue DATETIME
                floatval($driverData['CompanyLoadedPay'] ?? 0.00),   // @CompanyLoadedPay DECIMAL(9,3)
                floatval($driverData['CompanyEmptyPay'] ?? 0.00),    // @CompanyEmptyPay DECIMAL(9,3)
                $driverData['PayType'] ?? 'P',                       // @PayType CHAR(1), default 'P'
                floatval($driverData['CompanyTarpPay'] ?? 0.00),     // @CompanyTarpPay DECIMAL(9,3)
                floatval($driverData['CompanyStopPay'] ?? 0.00)      // @CompanyStopPay DECIMAL(9,3)
            ];

            log_message('info', "spDriver_Save called with params: " . json_encode($params));

            $returnCode = $this->callStoredProcedureWithReturn('spDriver_Save', $params);

            log_message('info', "spDriver_Save returned: {$returnCode} ({$this->getReturnCodeMessage($returnCode)}) for DriverKey: {$driverKey}");

            if ($returnCode === self::SRV_NORMAL) {
                return true;
            } else {
                log_message('error', "spDriver_Save failed: {$this->getReturnCodeMessage($returnCode)}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saving driver: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Search for driver by name (exact or partial match)
     *
     * @param string $name Driver name to search (LastName, FirstName format)
     * @return array|null Driver data or null if not found
     */
    public function searchDriverByName(string $name): ?array
    {
        if (empty($name)) {
            return null;
        }

        // Try exact match first (LastName, FirstName)
        $sql = "SELECT TOP 1 DriverKey, LastName, FirstName, MiddleName, ACTIVE, EndDate
                FROM dbo.tDriver
                WHERE CONCAT(LastName, ', ', FirstName) = ?";

        $results = $this->db->query($sql, [$name])->getResultArray();

        if (!empty($results)) {
            $driverKey = $results[0]['DriverKey'];
            return $this->getDriver($driverKey);
        }

        // Try partial match on LastName if exact match fails
        $sql = "SELECT TOP 1 DriverKey, LastName, FirstName, MiddleName, ACTIVE, EndDate
                FROM dbo.tDriver
                WHERE LastName LIKE ? OR FirstName LIKE ?
                ORDER BY EndDate DESC, LastName, FirstName";

        $searchTerm = '%' . $name . '%';
        $results = $this->db->query($sql, [$searchTerm, $searchTerm])->getResultArray();

        if (!empty($results)) {
            $driverKey = $results[0]['DriverKey'];
            return $this->getDriver($driverKey);
        }

        return null;
    }

    /**
     * Search drivers for autocomplete
     *
     * @param string $term Search term
     * @param bool $includeInactive Include inactive drivers
     * @return array Array of drivers for autocomplete
     */
    public function searchDriversForAutocomplete(string $term, bool $includeInactive = false): array
    {
        if (strlen($term) < 1) {
            return [];
        }

        // Build query to search by LastName, FirstName, or DriverKey (case-insensitive)
        // Note: Active status is determined by EndDate, not ACTIVE column
        // EndDate = '1899-12-30' means no end date (active)
        // EndDate != '1899-12-30' means has end date (inactive)
        $sql = "SELECT TOP 20 DriverKey, LastName, FirstName, MiddleName, ACTIVE, EndDate
                FROM dbo.tDriver
                WHERE (UPPER(LastName) LIKE UPPER(?)
                   OR UPPER(FirstName) LIKE UPPER(?)
                   OR CAST(DriverKey AS VARCHAR) LIKE ?)";

        if (!$includeInactive) {
            $sql .= " AND (EndDate IS NULL OR EndDate = '1899-12-30')";
        }

        $sql .= " ORDER BY LastName, FirstName";

        $searchTerm = '%' . $term . '%';

        // Debug logging
        $currentDb = $this->db->getDatabase();
        log_message('info', "DriverModel search - Current DB: {$currentDb}, Query: {$sql}, SearchTerm: {$searchTerm}");

        $results = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm])->getResultArray();

        log_message('info', "DriverModel search - Query returned " . count($results) . " rows");

        $drivers = [];
        foreach ($results as $row) {
            // Determine active status by EndDate, not ACTIVE column
            $isActive = (empty($row['EndDate']) || $row['EndDate'] == '1899-12-30 00:00:00.000');

            $fullName = trim($row['LastName']) . ', ' . trim($row['FirstName']);
            if (!empty($row['MiddleName'])) {
                $fullName .= ' ' . trim($row['MiddleName']);
            }

            $drivers[] = [
                'id' => $row['DriverKey'],
                'label' => $fullName . ' (' . $row['DriverKey'] . ')',
                'value' => $row['DriverKey'],
                'active' => $isActive
            ];
        }

        return $drivers;
    }

    /**
     * Get driver's address (returns first address from junction table)
     *
     * @param int $driverKey Driver key
     * @return array|null Address data or null if not found
     */
    public function getDriverAddress(int $driverKey): ?array
    {
        if ($driverKey <= 0) {
            return null;
        }

        // Get NameKeys linked to this driver
        $results = $this->callStoredProcedure('spDriverNameAddresses_Get', [$driverKey]);

        // Debug logging
        log_message('info', 'DriverModel::getDriverAddress - DriverKey: ' . $driverKey);
        log_message('info', 'DriverModel::getDriverAddress - spDriverNameAddresses_Get results: ' . json_encode($results));

        if (!empty($results) && is_array($results)) {
            // The stored procedure returns NameKey column
            // Check if the column exists in the result
            if (!isset($results[0]['NameKey'])) {
                log_message('error', 'NameKey column not found in results. Available columns: ' . json_encode(array_keys($results[0])));
                return null;
            }

            // Get the first NameKey (drivers have exactly 1 address in practice)
            $nameKey = $results[0]['NameKey'];
            log_message('info', 'DriverModel::getDriverAddress - Found NameKey: ' . $nameKey);

            // Load the address details
            $addressModel = new \App\Models\AddressModel();
            // Ensure the model's database is set to the current customer database
            $addressModel->db = $this->db;

            return $addressModel->getAddress($nameKey);
        }

        log_message('info', 'DriverModel::getDriverAddress - No address found for driver');
        return null;
    }

    /**
     * Get driver's contacts using 3-level chain retrieval
     *
     * Chain: Driver → tDriver_tNameAddress → tNameAddress → tNameAddress_tContact → tContact
     *
     * @param int $driverKey Driver key
     * @return array Array of contact data with proper column mapping
     */
    public function getDriverContacts(int $driverKey): array
    {
        if ($driverKey <= 0) {
            return [];
        }

        $contacts = [];

        try {
            // Step 1: Get NameKeys linked to this driver
            $nameKeyResults = $this->callStoredProcedure('spDriverNameAddresses_Get', [$driverKey]);

            log_message('info', 'DriverModel::getDriverContacts - DriverKey: ' . $driverKey);
            log_message('info', 'DriverModel::getDriverContacts - spDriverNameAddresses_Get results: ' . json_encode($nameKeyResults));

            if (!empty($nameKeyResults) && is_array($nameKeyResults)) {
                $contactModel = new \App\Models\ContactModel();
                // Ensure the model's database is set to the current customer database
                $contactModel->db = $this->db;

                // Step 2: For each NameKey, get ContactKeys
                foreach ($nameKeyResults as $nameKeyRow) {
                    $nameKey = $nameKeyRow['NameKey'] ?? 0;

                    if ($nameKey > 0) {
                        $contactKeys = $contactModel->getContactKeysForNameKey($nameKey);

                        log_message('info', 'DriverModel::getDriverContacts - NameKey: ' . $nameKey . ', ContactKeys: ' . json_encode($contactKeys));

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

            log_message('info', 'DriverModel::getDriverContacts - Total contacts found: ' . count($contacts));
        } catch (\Exception $e) {
            log_message('error', 'DriverModel::getDriverContacts - Error: ' . $e->getMessage());
        }

        return $contacts;
    }

    /**
     * Get all comments for a driver
     * Returns array of full comment data with user and date information
     *
     * @param int $driverKey Driver key
     * @return array Array of comments
     */
    public function getDriverComments(int $driverKey): array
    {
        $comments = [];

        try {
            log_message('info', 'DriverModel::getDriverComments - DriverKey: ' . $driverKey);

            if ($driverKey > 0) {
                $commentModel = new \App\Models\CommentModel();

                // Step 1: Get CommentKeys for this driver
                $commentKeys = $commentModel->getCommentKeysForDriver($driverKey);

                log_message('info', 'DriverModel::getDriverComments - CommentKeys: ' . json_encode($commentKeys));

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

            log_message('info', 'DriverModel::getDriverComments - Total comments found: ' . count($comments));
        } catch (\Exception $e) {
            log_message('error', 'DriverModel::getDriverComments - Error: ' . $e->getMessage());
        }

        return $comments;
    }
}
