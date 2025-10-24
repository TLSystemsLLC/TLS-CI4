<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AddressModel;
use App\Models\ContactModel;
use App\Models\CommentModel;

/**
 * Base Entity Maintenance Controller
 *
 * Abstract base class for all entity maintenance screens (Agent, Driver, Owner, etc.)
 * Provides common CRUD operations and standard endpoints.
 *
 * Child classes must implement:
 * - getEntityName() - Return 'Agent', 'Driver', 'Owner', etc.
 * - getEntityKey() - Return 'AgentKey', 'DriverKey', etc.
 * - getSection() - Return 'safety', 'systems', etc.
 * - getMenuPermission() - Return 'mnuAgentMaint', etc.
 * - getEntityModel() - Return instance of entity's model
 * - getFormFields() - Return array of form field definitions
 * - getNewEntityTemplate() - Return array of default values for new entity
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
abstract class BaseEntityMaintenance extends BaseController
{
    protected $entityModel = null;
    protected ?AddressModel $addressModel = null;
    protected ?ContactModel $contactModel = null;
    protected ?CommentModel $commentModel = null;

    // ==================== ABSTRACT METHODS (MUST IMPLEMENT) ====================

    /**
     * Get the entity name (e.g., 'Agent', 'Driver', 'Owner')
     */
    abstract protected function getEntityName(): string;

    /**
     * Get the entity key field name (e.g., 'AgentKey', 'DriverKey')
     */
    abstract protected function getEntityKey(): string;

    /**
     * Get the section name (e.g., 'safety', 'systems', 'dispatch')
     */
    abstract protected function getSection(): string;

    /**
     * Get the menu permission key (e.g., 'mnuAgentMaint')
     */
    abstract protected function getMenuPermission(): string;

    /**
     * Get the entity model instance with proper database context
     */
    abstract protected function getEntityModel();

    /**
     * Get form field definitions
     * Returns array of field definitions for rendering form
     */
    abstract protected function getFormFields(): array;

    /**
     * Get template for new entity with default values
     */
    abstract protected function getNewEntityTemplate(): array;

    // ==================== OPTIONAL OVERRIDES (SENSIBLE DEFAULTS) ====================

    /**
     * Get table name (default: t + EntityName + s)
     * Override if different naming convention
     */
    protected function getTableName(): string
    {
        return 't' . $this->getEntityName() . 's';
    }

    /**
     * Get stored procedure prefix (default: sp + EntityName)
     */
    protected function getSpPrefix(): string
    {
        return 'sp' . $this->getEntityName();
    }

    /**
     * Get view path (default: [section]/entityname_maintenance)
     */
    protected function getViewPath(): string
    {
        return $this->getSection() . '/' . strtolower($this->getEntityName()) . '_maintenance';
    }

    /**
     * Get autocomplete API type (default: lowercase entity name + 's')
     */
    protected function getApiType(): string
    {
        return strtolower($this->getEntityName()) . 's';
    }

    /**
     * Get entity variable name for views (default: lowercase entity name)
     */
    protected function getEntityVarName(): string
    {
        return strtolower($this->getEntityName());
    }

    /**
     * Get base URL for AJAX endpoints (relative to application base)
     * Returns path like: 'safety/driver-maintenance' (WITHOUT tls-ci4 prefix)
     * The view/JavaScript should use base_url() helper or prepend site base path as needed
     */
    protected function getBaseUrl(): string
    {
        // Just return section + entity path
        // Do NOT include tls-ci4 because base_url() helper adds it automatically
        return $this->getSection() . '/' . strtolower($this->getEntityName()) . '-maintenance';
    }

    // ==================== LAZY-LOADED MODEL HELPERS ====================

    /**
     * Get AddressModel instance with correct database context
     */
    protected function getAddressModel(): AddressModel
    {
        if ($this->addressModel === null) {
            $this->addressModel = new AddressModel();
        }

        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->addressModel->db) {
            $this->addressModel->db->setDatabase($customerDb);
        }

        return $this->addressModel;
    }

    /**
     * Get ContactModel instance with correct database context
     */
    protected function getContactModel(): ContactModel
    {
        if ($this->contactModel === null) {
            $this->contactModel = new ContactModel();
        }

        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->contactModel->db) {
            $this->contactModel->db->setDatabase($customerDb);
        }

        return $this->contactModel;
    }

    /**
     * Get CommentModel instance with correct database context
     */
    protected function getCommentModel(): CommentModel
    {
        if ($this->commentModel === null) {
            $this->commentModel = new CommentModel();
        }

        return $this->commentModel;
    }

    // ==================== STANDARD ENDPOINTS (WORK FOR ALL ENTITIES) ====================

    /**
     * Display entity maintenance page
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();

        $entity = null;
        $isNew = false;

        if ($this->request->getGet('new') == '1') {
            $isNew = true;
            $entity = $this->getNewEntityTemplate();
        }

        $data = [
            'pageTitle' => $this->getEntityName() . ' Maintenance - TLS Operations',
            'entityName' => $this->getEntityName(),
            'entityKey' => $this->getEntityKey(),
            'baseUrl' => $this->getBaseUrl(),
            'apiType' => $this->getApiType(),
            'formFields' => $this->getFormFields(),
            $this->getEntityVarName() => $entity,
            'isNew' . $this->getEntityName() => $isNew
        ];

        return $this->renderView($this->getViewPath(), $data);
    }

    /**
     * Handle entity search
     */
    public function search()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();
        $entityKey = $this->getEntityKey();
        $searchTerm = trim($this->request->getPost($this->getEntityVarName() . '_key') ?? '');

        if (empty($searchTerm)) {
            return redirect()->to('/' . $this->getViewPath())
                ->with('error', 'Please enter a ' . $entityKey . ' or Name.');
        }

        $entity = null;

        if (is_numeric($searchTerm)) {
            $key = intval($searchTerm);
            $entity = $this->getEntityModel()->{'get' . $this->getEntityName()}($key);
        } else {
            $entity = $this->getEntityModel()->{'search' . $this->getEntityName() . 'ByName'}($searchTerm);
        }

        if ($entity) {
            $this->session->setFlashdata('success', $this->getEntityName() . ' loaded successfully.');

            $data = [
                'pageTitle' => $this->getEntityName() . ' Maintenance - TLS Operations',
                'entityName' => $this->getEntityName(),
                'entityKey' => $this->getEntityKey(),
                'baseUrl' => $this->getBaseUrl(),
                'apiType' => $this->getApiType(),
                'formFields' => $this->getFormFields(),
                $this->getEntityVarName() => $entity,
                'isNew' . $this->getEntityName() => false
            ];

            return $this->renderView($this->getViewPath(), $data);
        } else {
            return redirect()->to('/' . $this->getViewPath())
                ->with('warning', $this->getEntityName() . ' not found.');
        }
    }

    /**
     * Autocomplete API endpoint
     */
    public function autocomplete()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();
        $searchTerm = $this->request->getGet('term') ?? '';
        $includeInactive = $this->request->getGet('include_inactive') == '1';

        if (strlen($searchTerm) < 1) {
            return $this->response->setJSON([]);
        }

        $customerDb = $this->getCurrentDatabase();
        log_message('info', "Autocomplete search - Database: {$customerDb}, Term: {$searchTerm}, IncludeInactive: " . ($includeInactive ? 'true' : 'false'));

        $results = $this->getEntityModel()->{'search' . $this->getEntityName() . 'sForAutocomplete'}($searchTerm, $includeInactive);

        log_message('info', "Autocomplete search - Found " . count($results) . " results");

        return $this->response->setJSON($results);
    }

    /**
     * Load entity by key (for redirects after save)
     */
    public function load(int $key)
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();
        $entity = $this->getEntityModel()->{'get' . $this->getEntityName()}($key);

        if ($entity) {
            $data = [
                'pageTitle' => $this->getEntityName() . ' Maintenance - TLS Operations',
                'entityName' => $this->getEntityName(),
                'entityKey' => $this->getEntityKey(),
                'baseUrl' => $this->getBaseUrl(),
                'apiType' => $this->getApiType(),
                'formFields' => $this->getFormFields(),
                $this->getEntityVarName() => $entity,
                'isNew' . $this->getEntityName() => false
            ];

            return $this->renderView($this->getViewPath(), $data);
        } else {
            return redirect()->to('/' . $this->getViewPath())
                ->with('warning', $this->getEntityName() . ' not found.');
        }
    }

    /**
     * Create new entity (AJAX endpoint)
     * Child classes can override if special logic needed
     */
    public function createNew()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();

        try {
            $data = $this->getNewEntityTemplate();
            $saved = $this->getEntityModel()->{'save' . $this->getEntityName()}($data);

            if ($saved) {
                // Get the newly created entity
                $query = $db->query("SELECT TOP 1 " . $this->getEntityKey() . " FROM " . $this->getTableName() . " ORDER BY " . $this->getEntityKey() . " DESC");
                $result = $query->getRowArray();

                if ($result && isset($result[$this->getEntityKey()])) {
                    $newKey = $result[$this->getEntityKey()];
                    log_message('info', "Created new " . $this->getEntityName() . " with key: {$newKey}");

                    // Create blank address
                    $nameQual = strtoupper(substr($this->getEntityName(), 0, 2));
                    $blankNameKey = $this->getAddressModel()->createBlankAddress($nameQual);

                    if ($blankNameKey > 0) {
                        $this->getAddressModel()->{'link' . $this->getEntityName() . 'Address'}($newKey, $blankNameKey);
                        log_message('info', "Created blank address (NameKey: {$blankNameKey}) for new " . $this->getEntityName() . " {$newKey}");
                    }

                    return $this->response->setJSON([
                        'success' => true,
                        strtolower($this->getEntityKey()) => $newKey,
                        'message' => 'New ' . $this->getEntityName() . ' created successfully'
                    ]);
                }
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create new ' . $this->getEntityName()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error creating new ' . $this->getEntityName() . ': ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save entity (create or update)
     * Child classes should override this to handle entity-specific validation and field mapping
     */
    abstract public function save();

    // ==================== ADDRESS ENDPOINTS ====================

    /**
     * Get entity's address (AJAX endpoint)
     */
    public function getAddress()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();
        $key = intval($this->request->getGet(strtolower($this->getEntityKey())) ?? 0);

        if ($key <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid ' . $this->getEntityKey()]);
        }

        $address = $this->getEntityModel()->{'get' . $this->getEntityName() . 'Address'}($key);

        if ($address) {
            return $this->response->setJSON(['success' => true, 'address' => $address]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Address not found']);
        }
    }

    /**
     * Save entity's address (AJAX endpoint)
     */
    public function saveAddress()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();
        $key = intval($this->request->getPost(strtolower($this->getEntityKey())) ?? 0);
        $nameKey = intval($this->request->getPost('name_key') ?? 0);

        if ($key <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid ' . $this->getEntityKey()]);
        }

        try {
            $nameQual = strtoupper(substr($this->getEntityName(), 0, 2));

            $addressData = [
                'NameKey' => $nameKey,
                'Name1' => $this->request->getPost('name1'),
                'Name2' => $this->request->getPost('name2'),
                'NameQual' => $nameQual,
                'Address1' => $this->request->getPost('address1'),
                'Address2' => $this->request->getPost('address2'),
                'City' => $this->request->getPost('city'),
                'State' => strtoupper($this->request->getPost('state') ?? ''),
                'Zip' => $this->request->getPost('zip'),
                'Phone' => $this->request->getPost('phone')
            ];

            $savedNameKey = $this->getAddressModel()->saveAddress($addressData);

            if ($savedNameKey > 0) {
                if ($nameKey == 0) {
                    $linked = $this->getAddressModel()->{'link' . $this->getEntityName() . 'Address'}($key, $savedNameKey);
                    if (!$linked) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Address saved but failed to link to ' . $this->getEntityName()
                        ]);
                    }
                }

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

    // ==================== CONTACT ENDPOINTS ====================

    /**
     * Get contacts for entity (AJAX endpoint)
     */
    public function getContacts()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();
        $key = intval($this->request->getGet(strtolower($this->getEntityKey())) ?? 0);

        if ($key <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid ' . $this->getEntityKey()]);
        }

        try {
            $contacts = $this->getEntityModel()->{'get' . $this->getEntityName() . 'Contacts'}($key);

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
     */
    public function saveContact()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();
        $key = intval($this->request->getPost(strtolower($this->getEntityKey())) ?? 0);
        $contactKey = intval($this->request->getPost('contact_key') ?? 0);

        if ($key <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid ' . $this->getEntityKey()]);
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

            $savedContactKey = $this->getContactModel()->saveContact($contactData, $key);

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
     */
    public function deleteContact()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

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
     * Get Contact Function options from validation table
     */
    public function getContactFunctionOptions()
    {
        $this->requireAuth();
        $db = $this->getCustomerDb();

        try {
            $options = $this->getEntityModel()->getValidationOptions('ContactFunction');

            return $this->response->setJSON([
                'success' => true,
                'options' => $options
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading ContactFunction options: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'options' => []
            ]);
        }
    }

    // ==================== COMMENT ENDPOINTS ====================

    /**
     * Get comments for entity (AJAX endpoint)
     */
    public function getComments()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();
        $key = intval($this->request->getGet(strtolower($this->getEntityKey())) ?? 0);

        if ($key <= 0) {
            return $this->response->setJSON(['success' => false, 'comments' => []]);
        }

        try {
            $comments = $this->getEntityModel()->{'get' . $this->getEntityName() . 'Comments'}($key);

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
     */
    public function saveComment()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();
        $key = intval($this->request->getPost(strtolower($this->getEntityKey())) ?? 0);
        $commentKey = intval($this->request->getPost('comment_key') ?? 0);

        if ($key <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid ' . $this->getEntityKey()]);
        }

        try {
            $user = $this->getCurrentUser();
            $userId = $user['user_id'] ?? 'UNKNOWN';

            $commentData = [
                'CommentKey' => $commentKey,
                'Comment' => $this->request->getPost('comment')
            ];

            $entityType = strtolower($this->getEntityName());
            $savedCommentKey = $this->getCommentModel()->saveComment($commentData, $key, $userId, $entityType);

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
     */
    public function deleteComment()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

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
