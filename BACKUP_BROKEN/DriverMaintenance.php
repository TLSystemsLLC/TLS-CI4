<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DriverModel;
use App\Models\AddressModel;
use App\Models\ContactModel;
use App\Models\CommentModel;

/**
 * Driver Maintenance Controller
 *
 * Handles driver creation, editing, and search including address, contact, and comment management.
 * Follows the Agent Maintenance pattern as the standard for entity maintenance screens.
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class DriverMaintenance extends BaseController
{
    private ?DriverModel $driverModel = null;
    private ?AddressModel $addressModel = null;
    private ?ContactModel $contactModel = null;
    private ?CommentModel $commentModel = null;

    /**
     * Get DriverModel instance with correct database context
     * Lazy initialization ensures database context is available
     */
    private function getDriverModel(): DriverModel
    {
        if ($this->driverModel === null) {
            $this->driverModel = new DriverModel();
        }

        // Ensure the model's database is set to the current customer database
        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->driverModel->db) {
            $this->driverModel->db->setDatabase($customerDb);
        }

        return $this->driverModel;
    }

    /**
     * Get AddressModel instance with correct database context
     * Lazy initialization ensures database context is available
     */
    private function getAddressModel(): AddressModel
    {
        if ($this->addressModel === null) {
            $this->addressModel = new AddressModel();
        }

        // Ensure the model's database is set to the current customer database
        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->addressModel->db) {
            $this->addressModel->db->setDatabase($customerDb);
        }

        return $this->addressModel;
    }

    /**
     * Get ContactModel instance with correct database context
     * Lazy initialization ensures database context is available
     */
    private function getContactModel(): ContactModel
    {
        if ($this->contactModel === null) {
            $this->contactModel = new ContactModel();
        }

        // Ensure the model's database is set to the current customer database
        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->contactModel->db) {
            $this->contactModel->db->setDatabase($customerDb);
        }

        return $this->contactModel;
    }

    /**
     * Get CommentModel instance with correct database context
     * Lazy initialization ensures database context is available
     */
    private function getCommentModel(): CommentModel
    {
        if ($this->commentModel === null) {
            $this->commentModel = new CommentModel();
        }

        return $this->commentModel;
    }

    /**
     * Display driver maintenance page
     */
    public function index()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Initialize variables
        $driver = null;
        $isNewDriver = false;

        // Check if this is a new driver request
        if ($this->request->getGet('new') == '1') {
            $isNewDriver = true;
            $driver = $this->getNewDriverTemplate();
        }

        // Prepare view data
        $data = [
            'pageTitle' => 'Driver Maintenance - TLS Operations',
            'driver' => $driver,
            'isNewDriver' => $isNewDriver
        ];

        return $this->renderView('safety/driver_maintenance', $data);
    }

    /**
     * Handle driver search
     */
    public function search()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $searchTerm = trim($this->request->getPost('driver_key') ?? '');

        if (empty($searchTerm)) {
            return redirect()->to('/safety/driver-maintenance')
                ->with('error', 'Please enter a Driver Key or Name.');
        }

        // Determine if search term is numeric (DriverKey) or text (Name)
        $driver = null;

        if (is_numeric($searchTerm)) {
            // Search by DriverKey
            $driverKey = intval($searchTerm);
            $driver = $this->getDriverModel()->getDriver($driverKey);
        } else {
            // Search by Name
            $driver = $this->getDriverModel()->searchDriverByName($searchTerm);
        }

        if ($driver) {
            // Set flash message
            $this->session->setFlashdata('success', 'Driver loaded successfully.');

            // Prepare view data
            $data = [
                'pageTitle' => 'Driver Maintenance - TLS Operations',
                'driver' => $driver,
                'isNewDriver' => false
            ];

            return $this->renderView('safety/driver_maintenance', $data);
        } else {
            return redirect()->to('/safety/driver-maintenance')
                ->with('warning', 'Driver not found.');
        }
    }

    /**
     * Handle driver save (create or update)
     */
    public function save()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Validate input using CI4 validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name' => 'required|max_length[15]',
            'last_name' => 'required|max_length[15]',
            'middle_name' => 'permit_empty|max_length[15]',
            'driver_id' => 'permit_empty|max_length[9]',
            'email' => 'permit_empty|valid_email|max_length[50]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            // Validation failed - reload form with errors
            $driver = $this->buildDriverFromPost();

            $data = [
                'pageTitle' => 'Driver Maintenance - TLS Operations',
                'driver' => $driver,
                'isNewDriver' => empty($this->request->getPost('driver_key'))
            ];

            return $this->renderView('safety/driver_maintenance', $data);
        }

        // Business rule validation: Active status must match End Date
        $endDate = $this->request->getPost('end_date');
        $hasEndDate = !empty($endDate);
        $isActiveChecked = $this->request->getPost('active') ? true : false;

        // Business rule:
        // - If Active = 1 (checked), End Date must be empty (or will be set to 1899-12-30)
        // - If Active = 0 (unchecked), End Date must be provided

        if (!$isActiveChecked && !$hasEndDate) {
            // User unchecked Active but didn't provide an End Date
            $driver = $this->buildDriverFromPost();
            $data = [
                'pageTitle' => 'Driver Maintenance - TLS Operations',
                'driver' => $driver,
                'isNewDriver' => empty($this->request->getPost('driver_key'))
            ];

            $this->session->setFlashdata('error', 'An inactive driver must have an End Date. Please enter an End Date to deactivate this driver.');
            return $this->renderView('safety/driver_maintenance', $data);
        }

        if ($isActiveChecked && $hasEndDate) {
            // User checked Active but provided an End Date
            $driver = $this->buildDriverFromPost();
            $data = [
                'pageTitle' => 'Driver Maintenance - TLS Operations',
                'driver' => $driver,
                'isNewDriver' => empty($this->request->getPost('driver_key'))
            ];

            $this->session->setFlashdata('error', 'An active driver cannot have an End Date. Please remove the End Date or uncheck Active to deactivate this driver.');
            return $this->renderView('safety/driver_maintenance', $data);
        }

        // Get form data
        $driverKey = intval($this->request->getPost('driver_key') ?? 0);
        $isNewDriver = ($driverKey == 0);

        try {
            // Prepare data array (33 parameters for spDriver_Save)
            $driverData = [
                'DriverKey' => $driverKey,
                'DriverID' => $this->request->getPost('driver_id'),
                'FirstName' => $this->request->getPost('first_name'),
                'MiddleName' => $this->request->getPost('middle_name'),
                'LastName' => $this->request->getPost('last_name'),
                'BirthDate' => $this->request->getPost('birth_date'),
                'LicenseNumber' => $this->request->getPost('license_number'),
                'LicenseState' => $this->request->getPost('license_state'),
                'LicenseExpires' => $this->request->getPost('license_expires'),
                'PhysicalDate' => $this->request->getPost('physical_date'),
                'PhysicalExpires' => $this->request->getPost('physical_expires'),
                'StartDate' => $this->request->getPost('start_date'),
                'EndDate' => $this->request->getPost('end_date'),
                'Active' => $this->request->getPost('active') ? 1 : 0,
                'FavoriteRoute' => $this->request->getPost('favorite_route'),
                'DriverType' => $this->request->getPost('driver_type') ?? 'F',
                'Email' => $this->request->getPost('email'),
                'TWIC' => $this->request->getPost('twic') ? 1 : 0,
                'CoilCert' => $this->request->getPost('coil_cert') ? 1 : 0,
                'CompanyID' => intval($this->request->getPost('company_id') ?? 3),
                'ARCNC' => $this->request->getPost('arcnc'),
                'TXCNC' => $this->request->getPost('txcnc'),
                'CompanyDriver' => $this->request->getPost('company_driver') ? 1 : 0,
                'EOBR' => $this->request->getPost('eobr') ? 1 : 0,
                'EOBRStart' => $this->request->getPost('eobr_start'),
                'WeeklyCash' => floatval($this->request->getPost('weekly_cash') ?? 0.00),
                'CardException' => $this->request->getPost('card_exception') ? 1 : 0,
                'DriverSpec' => $this->request->getPost('driver_spec') ?? 'OTH',
                'MedicalVerification' => $this->request->getPost('medical_verification') ? 1 : 0,
                'MVRDue' => $this->request->getPost('mvr_due'),
                'CompanyLoadedPay' => floatval($this->request->getPost('company_loaded_pay') ?? 0.00),
                'CompanyEmptyPay' => floatval($this->request->getPost('company_empty_pay') ?? 0.00),
                'PayType' => $this->request->getPost('pay_type') ?? 'P',
                'CompanyTarpPay' => floatval($this->request->getPost('company_tarp_pay') ?? 0.00),
                'CompanyStopPay' => floatval($this->request->getPost('company_stop_pay') ?? 0.00)
            ];

            if ($this->getDriverModel()->saveDriver($driverData)) {
                // If new driver, create a blank address and link it
                if ($isNewDriver) {
                    // The DriverModel's saveDriver() generates the new DriverKey
                    // We need to retrieve it by searching for the driver we just created
                    $fullName = $driverData['LastName'] . ', ' . $driverData['FirstName'];
                    $newDriver = $this->getDriverModel()->searchDriverByName($fullName);

                    if ($newDriver && isset($newDriver['DriverKey'])) {
                        $newDriverKey = $newDriver['DriverKey'];

                        // Create a blank address for the new driver
                        $newNameKey = $this->getAddressModel()->createBlankAddress('DR');

                        if ($newNameKey > 0) {
                            // Link the address to the driver
                            $this->getAddressModel()->linkDriverAddress($newDriverKey, $newNameKey);
                        }

                        // Redirect to load the newly created driver
                        return redirect()->to('/safety/driver-maintenance/load/' . $newDriverKey)
                            ->with('success', 'Driver created successfully.');
                    } else {
                        return redirect()->to('/safety/driver-maintenance')
                            ->with('success', 'Driver created successfully.');
                    }
                } else {
                    // Reload the driver to show updated data
                    return redirect()->to('/safety/driver-maintenance/load/' . $driverKey)
                        ->with('success', 'Driver updated successfully.');
                }
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to save driver.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Driver maintenance save error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred.');
        }
    }

    /**
     * Create new driver with confirmation
     * Generates DriverKey immediately and creates minimal driver record
     * This allows dependent objects (Address, Contacts, Comments) to be added
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON response with new DriverKey
     */
    public function createNewDriver()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        try {
            // Create minimal driver data with defaults
            $driverData = [
                'DriverKey' => 0,  // Will trigger getNextKey in saveDriver
                'DriverID' => '',
                'FirstName' => 'New',
                'MiddleName' => '',
                'LastName' => 'Driver',
                'BirthDate' => '',
                'LicenseNumber' => '',
                'LicenseState' => '',
                'LicenseExpires' => '',
                'PhysicalDate' => '',
                'PhysicalExpires' => '',
                'StartDate' => date('Y-m-d'),
                'EndDate' => '',
                'Active' => 1,
                'FavoriteRoute' => '',
                'DriverType' => 'F',
                'Email' => '',
                'TWIC' => 0,
                'CoilCert' => 0,
                'CompanyID' => 3,
                'ARCNC' => null,
                'TXCNC' => null,
                'CompanyDriver' => 0,
                'EOBR' => 0,
                'EOBRStart' => null,
                'WeeklyCash' => 0.00,
                'CardException' => 0,
                'DriverSpec' => 'OTH',
                'MedicalVerification' => 0,
                'MVRDue' => null,
                'CompanyLoadedPay' => 0.00,
                'CompanyEmptyPay' => 0.00,
                'PayType' => 'P',
                'CompanyTarpPay' => 0.00,
                'CompanyStopPay' => 0.00
            ];

            // Save the driver to get a real DriverKey
            $saved = $this->getDriverModel()->saveDriver($driverData);

            if ($saved) {
                // Get the newly created driver to return the DriverKey
                // Since we just created it with "Driver, New" name, find the most recent one
                $query = $db->query("SELECT TOP 1 DriverKey FROM tDriver WHERE LastName = 'Driver' AND FirstName = 'New' ORDER BY DriverKey DESC");
                $result = $query->getRowArray();

                if ($result && isset($result['DriverKey'])) {
                    $driverKey = $result['DriverKey'];

                    log_message('info', "Created new driver with DriverKey: {$driverKey}");

                    // Create blank address for the new driver
                    $blankNameKey = $this->getAddressModel()->createBlankAddress('DR');
                    if ($blankNameKey > 0) {
                        // Link the address to the driver
                        $this->getAddressModel()->linkDriverAddress($driverKey, $blankNameKey);
                        log_message('info', "Created blank address (NameKey: {$blankNameKey}) for new driver {$driverKey}");
                    }

                    return $this->response->setJSON([
                        'success' => true,
                        'driver_key' => $driverKey,
                        'message' => 'New driver created successfully'
                    ]);
                }
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create new driver'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error creating new driver: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Load driver by DriverKey (for redirects after save)
     */
    public function load(int $driverKey)
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Search for driver
        $driver = $this->getDriverModel()->getDriver($driverKey);

        if ($driver) {
            // Prepare view data
            $data = [
                'pageTitle' => 'Driver Maintenance - TLS Operations',
                'driver' => $driver,
                'isNewDriver' => false
            ];

            return $this->renderView('safety/driver_maintenance', $data);
        } else {
            return redirect()->to('/safety/driver-maintenance')
                ->with('warning', 'Driver not found.');
        }
    }

    /**
     * Autocomplete API endpoint for driver search
     */
    public function autocomplete()
    {
        // Require authentication
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $searchTerm = $this->request->getGet('term') ?? '';
        $includeInactive = $this->request->getGet('include_inactive') == '1';

        if (strlen($searchTerm) < 1) {
            return $this->response->setJSON([]);
        }

        // Debug logging
        $customerDb = $this->getCurrentDatabase();
        log_message('info', "Autocomplete search - Database: {$customerDb}, Term: {$searchTerm}, IncludeInactive: " . ($includeInactive ? 'true' : 'false'));

        $drivers = $this->getDriverModel()->searchDriversForAutocomplete($searchTerm, $includeInactive);

        log_message('info', "Autocomplete search - Found " . count($drivers) . " drivers");

        return $this->response->setJSON($drivers);
    }

    /**
     * Get new driver template
     *
     * @return array Default values for new driver
     */
    private function getNewDriverTemplate(): array
    {
        return [
            'DriverKey' => 0,
            'DriverID' => '',
            'FirstName' => '',
            'MiddleName' => '',
            'LastName' => '',
            'BirthDate' => null,
            'LicenseNumber' => '',
            'LicenseState' => '',
            'LicenseExpires' => null,
            'PhysicalDate' => null,
            'PhysicalExpires' => null,
            'StartDate' => null,
            'EndDate' => null,
            'Active' => 1,
            'FavoriteRoute' => '',
            'DriverType' => 'F',
            'Email' => '',
            'TWIC' => 0,
            'CoilCert' => 0,
            'CompanyID' => 3,
            'ARCNC' => null,
            'TXCNC' => null,
            'CompanyDriver' => 0,
            'EOBR' => 0,
            'EOBRStart' => null,
            'WeeklyCash' => 0.00,
            'CardException' => 0,
            'DriverSpec' => 'OTH',
            'MedicalVerification' => 0,
            'MVRDue' => null,
            'CompanyLoadedPay' => 0.00,
            'CompanyEmptyPay' => 0.00,
            'PayType' => 'P',
            'CompanyTarpPay' => 0.00,
            'CompanyStopPay' => 0.00
        ];
    }

    /**
     * Build driver array from POST data (for validation failure reload)
     *
     * @return array Driver data from POST
     */
    private function buildDriverFromPost(): array
    {
        return [
            'DriverKey' => intval($this->request->getPost('driver_key') ?? 0),
            'DriverID' => $this->request->getPost('driver_id'),
            'FirstName' => $this->request->getPost('first_name'),
            'MiddleName' => $this->request->getPost('middle_name'),
            'LastName' => $this->request->getPost('last_name'),
            'BirthDate' => $this->request->getPost('birth_date'),
            'LicenseNumber' => $this->request->getPost('license_number'),
            'LicenseState' => $this->request->getPost('license_state'),
            'LicenseExpires' => $this->request->getPost('license_expires'),
            'PhysicalDate' => $this->request->getPost('physical_date'),
            'PhysicalExpires' => $this->request->getPost('physical_expires'),
            'StartDate' => $this->request->getPost('start_date'),
            'EndDate' => $this->request->getPost('end_date'),
            'Active' => $this->request->getPost('active') ? 1 : 0,
            'FavoriteRoute' => $this->request->getPost('favorite_route'),
            'DriverType' => $this->request->getPost('driver_type') ?? 'F',
            'Email' => $this->request->getPost('email'),
            'TWIC' => $this->request->getPost('twic') ? 1 : 0,
            'CoilCert' => $this->request->getPost('coil_cert') ? 1 : 0,
            'CompanyID' => intval($this->request->getPost('company_id') ?? 3),
            'ARCNC' => $this->request->getPost('arcnc'),
            'TXCNC' => $this->request->getPost('txcnc'),
            'CompanyDriver' => $this->request->getPost('company_driver') ? 1 : 0,
            'EOBR' => $this->request->getPost('eobr') ? 1 : 0,
            'EOBRStart' => $this->request->getPost('eobr_start'),
            'WeeklyCash' => floatval($this->request->getPost('weekly_cash') ?? 0.00),
            'CardException' => $this->request->getPost('card_exception') ? 1 : 0,
            'DriverSpec' => $this->request->getPost('driver_spec') ?? 'OTH',
            'MedicalVerification' => $this->request->getPost('medical_verification') ? 1 : 0,
            'MVRDue' => $this->request->getPost('mvr_due'),
            'CompanyLoadedPay' => floatval($this->request->getPost('company_loaded_pay') ?? 0.00),
            'CompanyEmptyPay' => floatval($this->request->getPost('company_empty_pay') ?? 0.00),
            'PayType' => $this->request->getPost('pay_type') ?? 'P',
            'CompanyTarpPay' => floatval($this->request->getPost('company_tarp_pay') ?? 0.00),
            'CompanyStopPay' => floatval($this->request->getPost('company_stop_pay') ?? 0.00)
        ];
    }

    /**
     * Get driver's address (AJAX endpoint)
     */
    public function getAddress()
    {
        // Require authentication
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $driverKey = intval($this->request->getGet('driver_key') ?? 0);

        if ($driverKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid driver key']);
        }

        $address = $this->getDriverModel()->getDriverAddress($driverKey);

        if ($address) {
            return $this->response->setJSON(['success' => true, 'address' => $address]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Address not found']);
        }
    }

    /**
     * Save driver's address (AJAX endpoint)
     */
    public function saveAddress()
    {
        // Require authentication
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $driverKey = intval($this->request->getPost('driver_key') ?? 0);
        $nameKey = intval($this->request->getPost('name_key') ?? 0);

        if ($driverKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid driver key']);
        }

        try {
            // Prepare address data
            $addressData = [
                'NameKey' => $nameKey,
                'Name1' => $this->request->getPost('name1'),
                'Name2' => $this->request->getPost('name2'),
                'NameQual' => 'DR', // Driver qualifier
                'Address1' => $this->request->getPost('address1'),
                'Address2' => $this->request->getPost('address2'),
                'City' => $this->request->getPost('city'),
                'State' => strtoupper($this->request->getPost('state') ?? ''),
                'Zip' => $this->request->getPost('zip'),
                'Phone' => $this->request->getPost('phone')
            ];

            // Save the address
            $savedNameKey = $this->getAddressModel()->saveAddress($addressData);

            if ($savedNameKey > 0) {
                // If this was a new address, link it to the driver
                if ($nameKey == 0) {
                    $linked = $this->getAddressModel()->linkDriverAddress($driverKey, $savedNameKey);
                    if (!$linked) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Address saved but failed to link to driver'
                        ]);
                    }
                }

                // Reload the address to return fresh data
                $updatedAddress = $this->getAddressModel()->getAddress($savedNameKey);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Address saved successfully',
                    'address' => $updatedAddress
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save address'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saving address: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error occurred'
            ]);
        }
    }

    /**
     * Get contacts for a driver (AJAX endpoint)
     * Uses 3-level chain: Driver → NameAddress → Contact
     */
    public function getContacts()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');
        $db = $this->getCustomerDb();

        $driverKey = intval($this->request->getGet('driver_key') ?? 0);

        if ($driverKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid driver key']);
        }

        try {
            $contacts = $this->getDriverModel()->getDriverContacts($driverKey);

            return $this->response->setJSON([
                'success' => true,
                'contacts' => $contacts
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading contacts: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => true,
                'contacts' => []
            ]);
        }
    }

    /**
     * Save contact (AJAX endpoint)
     * Creates or updates a contact
     */
    public function saveContact()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');
        $db = $this->getCustomerDb();

        $driverKey = intval($this->request->getPost('driver_key') ?? 0);
        $contactKey = intval($this->request->getPost('contact_key') ?? 0);

        if ($driverKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid driver key']);
        }

        try {
            $contactData = [
                'ContactKey' => $contactKey,
                'ContactName' => $this->request->getPost('contact_name'),
                'ContactFunction' => $this->request->getPost('contact_function'),
                'TelephoneNo' => $this->request->getPost('telephone_no'),
                'CellNo' => $this->request->getPost('cell_no'),
                'Email' => $this->request->getPost('email'),
                'PrimaryContact' => $this->request->getPost('primary_contact') ? 1 : 0
            ];

            $savedContactKey = $this->getContactModel()->saveContact($contactData, $driverKey);

            if ($savedContactKey > 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Contact saved successfully',
                    'contact_key' => $savedContactKey
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save contact'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saving contact: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error occurred'
            ]);
        }
    }

    /**
     * Delete contact (AJAX endpoint)
     * Removes a contact
     */
    public function deleteContact()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');
        $db = $this->getCustomerDb();

        $contactKey = intval($this->request->getPost('contact_key') ?? 0);

        if ($contactKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid contact key']);
        }

        try {
            $deleted = $this->getContactModel()->deleteContact($contactKey);

            if ($deleted) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Contact deleted successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete contact'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting contact: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error occurred'
            ]);
        }
    }

    /**
     * Get comments for driver (AJAX endpoint)
     * Returns array of comments with details
     */
    public function getComments()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');
        $db = $this->getCustomerDb();

        $driverKey = intval($this->request->getGet('driver_key') ?? 0);

        if ($driverKey <= 0) {
            return $this->response->setJSON(['success' => false, 'comments' => []]);
        }

        try {
            $comments = $this->getDriverModel()->getDriverComments($driverKey);

            return $this->response->setJSON([
                'success' => true,
                'comments' => $comments
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading comments: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'comments' => []
            ]);
        }
    }

    /**
     * Save comment (AJAX endpoint)
     * Creates or updates a comment for a driver
     */
    public function saveComment()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');
        $db = $this->getCustomerDb();

        $driverKey = intval($this->request->getPost('driver_key') ?? 0);
        $commentKey = intval($this->request->getPost('comment_key') ?? 0);

        if ($driverKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid driver key']);
        }

        try {
            // Get current user ID from session
            $user = $this->getCurrentUser();
            $userId = $user['user_id'] ?? 'UNKNOWN';

            $commentData = [
                'CommentKey' => $commentKey,
                'Comment' => $this->request->getPost('comment')
            ];

            $savedCommentKey = $this->getCommentModel()->saveComment($commentData, $driverKey, $userId, 'driver');

            if ($savedCommentKey > 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Comment saved successfully',
                    'comment_key' => $savedCommentKey
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save comment'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saving comment: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error occurred'
            ]);
        }
    }

    /**
     * Delete comment (AJAX endpoint)
     * Removes a comment
     */
    public function deleteComment()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuDriverMaint');
        $db = $this->getCustomerDb();

        $commentKey = intval($this->request->getPost('comment_key') ?? 0);

        if ($commentKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid comment key']);
        }

        try {
            $deleted = $this->getCommentModel()->deleteComment($commentKey);

            if ($deleted) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Comment deleted successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete comment'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting comment: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error occurred'
            ]);
        }
    }
}
