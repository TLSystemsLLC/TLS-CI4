<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OwnerModel;
use App\Models\AddressModel;
use App\Models\ContactModel;
use App\Models\CommentModel;

/**
 * Owner Maintenance Controller
 *
 * Handles owner creation, editing, and search including address, contact, and comment management.
 * Follows the Agent/Driver Maintenance pattern as the standard for entity maintenance screens.
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class OwnerMaintenance extends BaseController
{
    private ?OwnerModel $ownerModel = null;
    private ?AddressModel $addressModel = null;
    private ?ContactModel $contactModel = null;
    private ?CommentModel $commentModel = null;

    /**
     * Get OwnerModel instance with correct database context
     * Lazy initialization ensures database context is available
     */
    private function getOwnerModel(): OwnerModel
    {
        if ($this->ownerModel === null) {
            $this->ownerModel = new OwnerModel();
        }

        // Ensure the model's database is set to the current customer database
        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->ownerModel->db) {
            $this->ownerModel->db->setDatabase($customerDb);
        }

        return $this->ownerModel;
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
     * Display owner maintenance page
     */
    public function index()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuOwnerMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Initialize variables
        $owner = null;
        $isNewOwner = false;

        // Check if this is a new owner request
        if ($this->request->getGet('new') == '1') {
            $isNewOwner = true;
            $owner = $this->getNewOwnerTemplate();
        }

        // Prepare view data
        $data = [
            'pageTitle' => 'Owner Maintenance - TLS Operations',
            'owner' => $owner,
            'isNewOwner' => $isNewOwner
        ];

        return $this->renderView('safety/owner_maintenance', $data);
    }

    /**
     * Handle owner search
     */
    public function search()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuOwnerMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $searchTerm = trim($this->request->getPost('owner_key') ?? '');

        if (empty($searchTerm)) {
            return redirect()->to('/safety/owner-maintenance')
                ->with('error', 'Please enter an Owner Key or Name.');
        }

        // Determine if search term is numeric (OwnerKey) or text (Name)
        $owner = null;

        if (is_numeric($searchTerm)) {
            // Search by OwnerKey
            $ownerKey = intval($searchTerm);
            $owner = $this->getOwnerModel()->getOwner($ownerKey);
        } else {
            // Search by Name
            $owner = $this->getOwnerModel()->searchOwnerByName($searchTerm);
        }

        if ($owner) {
            // Set flash message
            $this->session->setFlashdata('success', 'Owner loaded successfully.');

            // Prepare view data
            $data = [
                'pageTitle' => 'Owner Maintenance - TLS Operations',
                'owner' => $owner,
                'isNewOwner' => false
            ];

            return $this->renderView('safety/owner_maintenance', $data);
        } else {
            return redirect()->to('/safety/owner-maintenance')
                ->with('warning', 'Owner not found.');
        }
    }

    /**
     * Handle owner save (create or update)
     */
    public function save()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuOwnerMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Validate input using CI4 validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name' => 'permit_empty|max_length[15]',
            'last_name' => 'required|max_length[35]',
            'middle_name' => 'permit_empty|max_length[15]',
            'owner_id' => 'permit_empty|max_length[9]',
            'email' => 'permit_empty|valid_email|max_length[50]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            // Validation failed - reload form with errors
            $owner = $this->buildOwnerFromPost();

            $data = [
                'pageTitle' => 'Owner Maintenance - TLS Operations',
                'owner' => $owner,
                'isNewOwner' => empty($this->request->getPost('owner_key'))
            ];

            return $this->renderView('safety/owner_maintenance', $data);
        }

        // Get form data
        $ownerKey = intval($this->request->getPost('owner_key') ?? 0);
        $isNewOwner = ($ownerKey == 0);

        try {
            // Prepare data array (18 parameters for spOwner_Save)
            $ownerData = [
                'OwnerKey' => $ownerKey,
                'OwnerID' => $this->request->getPost('owner_id'),
                'IDType' => $this->request->getPost('id_type') ?? 'O',
                'FirstName' => $this->request->getPost('first_name'),
                'MiddleName' => $this->request->getPost('middle_name'),
                'LastName' => $this->request->getPost('last_name'),
                'OtherName' => $this->request->getPost('other_name'),
                'StartDate' => $this->request->getPost('start_date'),
                'EndDate' => $this->request->getPost('end_date'),
                'DirectDeposit' => $this->request->getPost('direct_deposit') ? 1 : 0,
                'DDId' => intval($this->request->getPost('dd_id') ?? 0),
                'Email' => $this->request->getPost('email'),
                'DriverKey' => !empty($this->request->getPost('driver_key')) ? intval($this->request->getPost('driver_key')) : null,
                'MinCheck' => floatval($this->request->getPost('min_check') ?? 0.00),
                'MaxDebt' => floatval($this->request->getPost('max_debt') ?? 0.00),
                'MinDeduction' => floatval($this->request->getPost('min_deduction') ?? 0.00),
                'VerifiedCheck' => floatval($this->request->getPost('verified_check') ?? 0.00),
                'CompanyID' => intval($this->request->getPost('company_id') ?? 3),
                'ContractSigned' => $this->request->getPost('contract_signed') ? 1 : 0
            ];

            if ($this->getOwnerModel()->saveOwner($ownerData)) {
                // If new owner, create a blank address and link it
                if ($isNewOwner) {
                    // The OwnerModel's saveOwner() generates the new OwnerKey
                    // We need to retrieve it by searching for the owner we just created
                    $fullName = $ownerData['LastName'] . ', ' . $ownerData['FirstName'];
                    $newOwner = $this->getOwnerModel()->searchOwnerByName($fullName);

                    if ($newOwner && isset($newOwner['OwnerKey'])) {
                        $newOwnerKey = $newOwner['OwnerKey'];

                        // Create a blank address for the new owner
                        $newNameKey = $this->getAddressModel()->createBlankAddress('OW');

                        if ($newNameKey > 0) {
                            // Link the address to the owner
                            $this->getAddressModel()->linkOwnerAddress($newOwnerKey, $newNameKey);
                        }

                        // Redirect to load the newly created owner
                        return redirect()->to('/safety/owner-maintenance/load/' . $newOwnerKey)
                            ->with('success', 'Owner created successfully.');
                    } else {
                        return redirect()->to('/safety/owner-maintenance')
                            ->with('success', 'Owner created successfully.');
                    }
                } else {
                    // Reload the owner to show updated data
                    return redirect()->to('/safety/owner-maintenance/load/' . $ownerKey)
                        ->with('success', 'Owner updated successfully.');
                }
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to save owner.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Owner maintenance save error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred.');
        }
    }

    /**
     * Create new owner with confirmation
     * Generates OwnerKey immediately and creates minimal owner record
     * This allows dependent objects (Address, Contacts, Comments) to be added
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON response with new OwnerKey
     */
    public function createNewOwner()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuOwnerMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        try {
            // Create minimal owner data with defaults
            $ownerData = [
                'OwnerKey' => 0,  // Will trigger getNextKey in saveOwner
                'OwnerID' => '',
                'IDType' => 'O',
                'FirstName' => 'New',
                'MiddleName' => '',
                'LastName' => 'Owner',
                'OtherName' => '',
                'StartDate' => date('Y-m-d'),
                'EndDate' => '',
                'DirectDeposit' => 0,
                'DDId' => 0,
                'Email' => '',
                'DriverKey' => null,
                'MinCheck' => 0.00,
                'MaxDebt' => 0.00,
                'MinDeduction' => 0.00,
                'VerifiedCheck' => 0.00,
                'CompanyID' => 3,
                'ContractSigned' => 0
            ];

            // Save the owner to get a real OwnerKey
            $saved = $this->getOwnerModel()->saveOwner($ownerData);

            if ($saved) {
                // Get the newly created owner to return the OwnerKey
                // Since we just created it with "Owner, New" name, find the most recent one
                $query = $db->query("SELECT TOP 1 OwnerKey FROM tOwner WHERE LastName = 'Owner' AND FirstName = 'New' ORDER BY OwnerKey DESC");
                $result = $query->getRowArray();

                if ($result && isset($result['OwnerKey'])) {
                    $ownerKey = $result['OwnerKey'];

                    log_message('info', "Created new owner with OwnerKey: {$ownerKey}");

                    // Create blank address for the new owner
                    $blankNameKey = $this->getAddressModel()->createBlankAddress('OW');
                    if ($blankNameKey > 0) {
                        // Link the address to the owner
                        $this->getAddressModel()->linkOwnerAddress($ownerKey, $blankNameKey);
                        log_message('info', "Created blank address (NameKey: {$blankNameKey}) for new owner {$ownerKey}");
                    }

                    return $this->response->setJSON([
                        'success' => true,
                        'owner_key' => $ownerKey,
                        'message' => 'New owner created successfully'
                    ]);
                }
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create new owner'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error creating new owner: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Load owner by OwnerKey (for redirects after save)
     */
    public function load(int $ownerKey)
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuOwnerMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Search for owner
        $owner = $this->getOwnerModel()->getOwner($ownerKey);

        if ($owner) {
            // Prepare view data
            $data = [
                'pageTitle' => 'Owner Maintenance - TLS Operations',
                'owner' => $owner,
                'isNewOwner' => false
            ];

            return $this->renderView('safety/owner_maintenance', $data);
        } else {
            return redirect()->to('/safety/owner-maintenance')
                ->with('warning', 'Owner not found.');
        }
    }

    /**
     * Autocomplete API endpoint for owner search
     */
    public function autocomplete()
    {
        // Require authentication
        $this->requireAuth();
        $this->requireMenuPermission('mnuOwnerMaint');

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

        $owners = $this->getOwnerModel()->searchOwnersForAutocomplete($searchTerm, $includeInactive);

        log_message('info', "Autocomplete search - Found " . count($owners) . " owners");

        return $this->response->setJSON($owners);
    }

    /**
     * Get new owner template
     *
     * @return array Default values for new owner
     */
    private function getNewOwnerTemplate(): array
    {
        return [
            'OwnerKey' => 0,
            'OwnerID' => '',
            'IDType' => 'O',
            'FirstName' => '',
            'MiddleName' => '',
            'LastName' => '',
            'OtherName' => '',
            'StartDate' => null,
            'EndDate' => null,
            'DirectDeposit' => 0,
            'DDId' => 0,
            'Email' => '',
            'DriverKey' => null,
            'MinCheck' => 0.00,
            'MaxDebt' => 0.00,
            'MinDeduction' => 0.00,
            'VerifiedCheck' => 0.00,
            'CompanyID' => 3,
            'ContractSigned' => 0
        ];
    }

    /**
     * Build owner array from POST data (for validation failure reload)
     *
     * @return array Owner data from POST
     */
    private function buildOwnerFromPost(): array
    {
        return [
            'OwnerKey' => intval($this->request->getPost('owner_key') ?? 0),
            'OwnerID' => $this->request->getPost('owner_id'),
            'IDType' => $this->request->getPost('id_type') ?? 'O',
            'FirstName' => $this->request->getPost('first_name'),
            'MiddleName' => $this->request->getPost('middle_name'),
            'LastName' => $this->request->getPost('last_name'),
            'OtherName' => $this->request->getPost('other_name'),
            'StartDate' => $this->request->getPost('start_date'),
            'EndDate' => $this->request->getPost('end_date'),
            'DirectDeposit' => $this->request->getPost('direct_deposit') ? 1 : 0,
            'DDId' => intval($this->request->getPost('dd_id') ?? 0),
            'Email' => $this->request->getPost('email'),
            'DriverKey' => !empty($this->request->getPost('driver_key')) ? intval($this->request->getPost('driver_key')) : null,
            'MinCheck' => floatval($this->request->getPost('min_check') ?? 0.00),
            'MaxDebt' => floatval($this->request->getPost('max_debt') ?? 0.00),
            'MinDeduction' => floatval($this->request->getPost('min_deduction') ?? 0.00),
            'VerifiedCheck' => floatval($this->request->getPost('verified_check') ?? 0.00),
            'CompanyID' => intval($this->request->getPost('company_id') ?? 3),
            'ContractSigned' => $this->request->getPost('contract_signed') ? 1 : 0
        ];
    }

    /**
     * Get owner's address (AJAX endpoint)
     */
    public function getAddress()
    {
        // Require authentication
        $this->requireAuth();
        $this->requireMenuPermission('mnuOwnerMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $ownerKey = intval($this->request->getGet('owner_key') ?? 0);

        if ($ownerKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid owner key']);
        }

        $address = $this->getOwnerModel()->getOwnerAddress($ownerKey);

        if ($address) {
            return $this->response->setJSON(['success' => true, 'address' => $address]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Address not found']);
        }
    }

    /**
     * Save owner's address (AJAX endpoint)
     */
    public function saveAddress()
    {
        // Require authentication
        $this->requireAuth();
        $this->requireMenuPermission('mnuOwnerMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $ownerKey = intval($this->request->getPost('owner_key') ?? 0);
        $nameKey = intval($this->request->getPost('name_key') ?? 0);

        if ($ownerKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid owner key']);
        }

        try {
            // Prepare address data
            $addressData = [
                'NameKey' => $nameKey,
                'Name1' => $this->request->getPost('name1'),
                'Name2' => $this->request->getPost('name2'),
                'NameQual' => 'OW', // Owner qualifier
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
                // If this was a new address, link it to the owner
                if ($nameKey == 0) {
                    $linked = $this->getAddressModel()->linkOwnerAddress($ownerKey, $savedNameKey);
                    if (!$linked) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Address saved but failed to link to owner'
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
     * Get contacts for an owner (AJAX endpoint)
     * Uses 3-level chain: Owner → NameAddress → Contact
     */
    public function getContacts()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuOwnerMaint');
        $db = $this->getCustomerDb();

        $ownerKey = intval($this->request->getGet('owner_key') ?? 0);

        if ($ownerKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid owner key']);
        }

        try {
            $contacts = $this->getOwnerModel()->getOwnerContacts($ownerKey);

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
        $this->requireMenuPermission('mnuOwnerMaint');
        $db = $this->getCustomerDb();

        $ownerKey = intval($this->request->getPost('owner_key') ?? 0);
        $contactKey = intval($this->request->getPost('contact_key') ?? 0);

        if ($ownerKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid owner key']);
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

            $savedContactKey = $this->getContactModel()->saveContact($contactData, $ownerKey);

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
        $this->requireMenuPermission('mnuOwnerMaint');
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
     * Get comments for owner (AJAX endpoint)
     * Returns array of comments with details
     */
    public function getComments()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuOwnerMaint');
        $db = $this->getCustomerDb();

        $ownerKey = intval($this->request->getGet('owner_key') ?? 0);

        if ($ownerKey <= 0) {
            return $this->response->setJSON(['success' => false, 'comments' => []]);
        }

        try {
            $comments = $this->getOwnerModel()->getOwnerComments($ownerKey);

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
     * Creates or updates a comment for an owner
     */
    public function saveComment()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuOwnerMaint');
        $db = $this->getCustomerDb();

        $ownerKey = intval($this->request->getPost('owner_key') ?? 0);
        $commentKey = intval($this->request->getPost('comment_key') ?? 0);

        if ($ownerKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid owner key']);
        }

        try {
            // Get current user ID from session
            $user = $this->getCurrentUser();
            $userId = $user['user_id'] ?? 'UNKNOWN';

            $commentData = [
                'CommentKey' => $commentKey,
                'Comment' => $this->request->getPost('comment')
            ];

            $savedCommentKey = $this->getCommentModel()->saveComment($commentData, $ownerKey, $userId, 'owner');

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
        $this->requireMenuPermission('mnuOwnerMaint');
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
