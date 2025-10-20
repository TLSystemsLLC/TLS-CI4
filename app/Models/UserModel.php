<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * User Model
 *
 * NOTE: This model uses DIRECT SQL queries instead of stored procedures.
 * User maintenance is an exception to the standard stored procedure pattern
 * because it needs to work across all customer databases and predates the
 * stored procedure standard.
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class UserModel extends Model
{
    protected $table = 'tUser';
    protected $primaryKey = 'UserKey';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'UserID', 'UserName', 'FirstName', 'LastName', 'Email', 'Password',
        'Active', 'TeamKey', 'Extension', 'PasswordChanged', 'HireDate', 'TermDate',
        'DepartmentID', 'Phone', 'Title', 'CompanyID', 'DivisionID', 'UserType',
        'Fax', 'CommissionPct', 'CommissionMinProfit', 'RapidLogUser',
        'SatelliteInstalls', 'PrimaryAccount'
    ];


    /**
     * Search for user by UserID or UserName
     *
     * @param string $searchTerm UserID or UserName to search for
     * @return array|null User data if found, null otherwise
     */
    public function searchUser(string $searchTerm): ?array
    {
        $query = "SELECT UserKey, UserID, UserName, FirstName, LastName, Email, Active,
                        TeamKey, Extension, PasswordChanged, HireDate, TermDate,
                        LastLogin, Title, Phone, DepartmentID, CompanyID, DivisionID,
                        UserType, Fax, CommissionPct, CommissionMinProfit,
                        RapidLogUser, SatelliteInstalls, PrimaryAccount, LastUpdate, LastWebAccess
                FROM tUser WHERE UserID = ? OR UserName = ?";

        $result = $this->db->query($query, [$searchTerm, $searchTerm])->getRowArray();

        if ($result) {
            // Trim all string fields
            foreach ($result as $key => $value) {
                if (is_string($value)) {
                    $result[$key] = trim($value);
                }
            }
        }

        return $result ?: null;
    }

    /**
     * Get user by UserID
     *
     * @param string $userId UserID to look up
     * @return array|null User data if found, null otherwise
     */
    public function getUserByUserId(string $userId): ?array
    {
        return $this->searchUser($userId);
    }

    /**
     * Check if UserID already exists
     *
     * @param string $userId UserID to check
     * @return bool True if exists, false otherwise
     */
    public function userIdExists(string $userId): bool
    {
        $query = "SELECT COUNT(*) as count FROM tUser WHERE UserID = ?";
        $result = $this->db->query($query, [$userId])->getRowArray();
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Create new user
     *
     * @param array $data User data
     * @return bool True on success, false on failure
     */
    public function createUser(array $data): bool
    {
        // Check if user already exists
        if ($this->userIdExists($data['UserID'])) {
            return false;
        }

        // Prepare data for insert
        $insertData = $this->prepareUserData($data, true);

        // Execute insert
        $query = "INSERT INTO tUser (UserID, UserName, FirstName, LastName, Email, Password,
                                    Active, TeamKey, Extension, PasswordChanged, HireDate, TermDate,
                                    DepartmentID, Phone, Title, CompanyID, DivisionID, UserType,
                                    Fax, CommissionPct, CommissionMinProfit, RapidLogUser,
                                    SatelliteInstalls, PrimaryAccount)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->db->query($query, $insertData);
    }

    /**
     * Update existing user
     *
     * @param string $userId UserID to update
     * @param array $data User data
     * @return bool True on success, false on failure
     */
    public function updateUser(string $userId, array $data): bool
    {
        // Prepare data for update
        $updateData = $this->prepareUserData($data, false);

        // Build update query
        $query = "UPDATE tUser SET
                  UserName = ?, FirstName = ?, LastName = ?, Email = ?,
                  Active = ?, TeamKey = ?, Extension = ?, HireDate = ?, TermDate = ?,
                  DepartmentID = ?, Phone = ?, Title = ?, CompanyID = ?, DivisionID = ?,
                  UserType = ?, Fax = ?, CommissionPct = ?, CommissionMinProfit = ?,
                  RapidLogUser = ?, SatelliteInstalls = ?, PrimaryAccount = ?";

        $params = $updateData;

        // Add password update if provided
        if (!empty($data['Password'])) {
            $query .= ", Password = ?, PasswordChanged = ?";
            $params[] = trim($data['Password']); // In production, this should be hashed
            $params[] = date('Y-m-d H:i:s');
        }

        $query .= " WHERE UserID = ?";
        $params[] = $userId;

        return $this->db->query($query, $params);
    }

    /**
     * Prepare user data for insert or update
     *
     * @param array $data Raw form data
     * @param bool $isNew True if creating new user, false if updating
     * @return array Prepared data array
     */
    private function prepareUserData(array $data, bool $isNew): array
    {
        $prepared = [
            trim($data['UserName'] ?? ''),
            trim($data['FirstName'] ?? ''),
            trim($data['LastName'] ?? ''),
            trim($data['Email'] ?? ''),
            isset($data['Active']) ? 1 : 0,
            !empty($data['TeamKey']) ? (int)$data['TeamKey'] : null,
            !empty($data['Extension']) ? (int)$data['Extension'] : null,
            !empty($data['HireDate']) ? $data['HireDate'] : null,
            !empty($data['TermDate']) ? $data['TermDate'] : null,
            !empty($data['DepartmentID']) ? (int)$data['DepartmentID'] : null,
            trim($data['Phone'] ?? ''),
            trim($data['Title'] ?? ''),
            !empty($data['CompanyID']) ? (int)$data['CompanyID'] : null,
            !empty($data['DivisionID']) ? (int)$data['DivisionID'] : null,
            !empty($data['UserType']) ? (int)$data['UserType'] : null,
            trim($data['Fax'] ?? ''),
            !empty($data['CommissionPct']) ? (float)$data['CommissionPct'] / 100 : null,
            !empty($data['CommissionMinProfit']) ? (float)$data['CommissionMinProfit'] / 100 : null,
            isset($data['RapidLogUser']) ? 1 : 0,
            isset($data['SatelliteInstalls']) ? 1 : 0,
            isset($data['PrimaryAccount']) ? 1 : 0
        ];

        // For new users, prepend UserID and Password
        if ($isNew) {
            array_unshift($prepared, trim($data['UserID']));
            array_splice($prepared, 5, 0, [trim($data['Password'] ?? '')]); // Insert password after Email
            // Add PasswordChanged date after Password
            array_splice($prepared, 9, 0, [!empty($data['Password']) ? date('Y-m-d H:i:s') : null]);
        }

        return $prepared;
    }

    /**
     * Get lookup table data for dropdowns
     *
     * @return array Array of lookup data
     */
    public function getLookupTables(): array
    {
        $lookups = [
            'userTypes' => [],
            'companies' => [],
            'divisions' => [],
            'departments' => [],
            'teams' => []
        ];

        try {
            // Load UserTypes
            $lookups['userTypes'] = $this->db->query(
                "SELECT Type, Description FROM tUserType ORDER BY Description"
            )->getResultArray();

            // Load Companies
            $lookups['companies'] = $this->db->query(
                "SELECT CompanyID, CompanyName FROM tCompany ORDER BY CompanyName"
            )->getResultArray();

            // Load Divisions
            $lookups['divisions'] = $this->db->query(
                "SELECT DivisionID, Name FROM tDivision ORDER BY Name"
            )->getResultArray();

            // Load Departments
            $lookups['departments'] = $this->db->query(
                "SELECT DepartmentID, Description FROM tDepartment ORDER BY Description"
            )->getResultArray();

            // Load Teams
            $lookups['teams'] = $this->db->query(
                "SELECT TeamKey, TeamName FROM tTeam ORDER BY TeamName"
            )->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error loading lookup tables: ' . $e->getMessage());
            // Continue with empty arrays if lookup tables fail
        }

        return $lookups;
    }

    /**
     * Search users for autocomplete (by UserID or UserName)
     *
     * @param string $searchTerm Search term
     * @param bool $includeInactive Include inactive users
     * @return array Array of matching users
     */
    public function searchUsersForAutocomplete(string $searchTerm, bool $includeInactive = false): array
    {
        $query = "SELECT UserID, UserName, FirstName, LastName, Active
                 FROM tUser
                 WHERE (UserID LIKE ? OR UserName LIKE ? OR FirstName LIKE ? OR LastName LIKE ?)";

        $params = [
            $searchTerm . '%',
            '%' . $searchTerm . '%',
            '%' . $searchTerm . '%',
            '%' . $searchTerm . '%'
        ];

        if (!$includeInactive) {
            $query .= " AND Active = 1";
        }

        $query .= " ORDER BY UserName";

        $results = $this->db->query($query, $params)->getResultArray();

        // Trim all string fields
        foreach ($results as &$result) {
            foreach ($result as $key => $value) {
                if (is_string($value)) {
                    $result[$key] = trim($value);
                }
            }
        }

        return $results;
    }
}
