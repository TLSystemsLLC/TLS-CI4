<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgentModel;
use App\Models\AddressModel;
use App\Models\ContactModel;
use App\Models\CommentModel;

/**
 * Agent Maintenance Controller
 *
 * Handles agent creation, editing, and search including address management.
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class AgentMaintenance extends BaseController
{
    private ?AgentModel $agentModel = null;
    private ?AddressModel $addressModel = null;
    private ?ContactModel $contactModel = null;
    private ?CommentModel $commentModel = null;

    /**
     * Get AgentModel instance with correct database context
     * Lazy initialization ensures database context is available
     */
    private function getAgentModel(): AgentModel
    {
        if ($this->agentModel === null) {
            $this->agentModel = new AgentModel();
        }

        // Ensure the model's database is set to the current customer database
        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->agentModel->db) {
            $this->agentModel->db->setDatabase($customerDb);
        }

        return $this->agentModel;
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
     * Display agent maintenance page
     */
    public function index()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Initialize variables
        $agent = null;
        $isNewAgent = false;

        // Check if this is a new agent request
        if ($this->request->getGet('new') == '1') {
            $isNewAgent = true;
            $agent = $this->getNewAgentTemplate();
        }

        // Prepare view data
        $data = [
            'pageTitle' => 'Agent Maintenance - TLS Operations',
            'agent' => $agent,
            'isNewAgent' => $isNewAgent
        ];

        return $this->renderView('safety/agent_maintenance', $data);
    }

    /**
     * Handle agent search
     */
    public function search()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $searchTerm = trim($this->request->getPost('agent_key') ?? '');

        if (empty($searchTerm)) {
            return redirect()->to('/safety/agent-maintenance')
                ->with('error', 'Please enter an Agent Key or Name.');
        }

        // Determine if search term is numeric (AgentKey) or text (Name)
        $agent = null;

        if (is_numeric($searchTerm)) {
            // Search by AgentKey
            $agentKey = intval($searchTerm);
            $agent = $this->getAgentModel()->getAgent($agentKey);
        } else {
            // Search by Name
            $agent = $this->getAgentModel()->searchAgentByName($searchTerm);
        }

        if ($agent) {
            // Set flash message
            $this->session->setFlashdata('success', 'Agent loaded successfully.');

            // Prepare view data
            $data = [
                'pageTitle' => 'Agent Maintenance - TLS Operations',
                'agent' => $agent,
                'isNewAgent' => false
            ];

            return $this->renderView('safety/agent_maintenance', $data);
        } else {
            return redirect()->to('/safety/agent-maintenance')
                ->with('warning', 'Agent not found.');
        }
    }

    /**
     * Handle agent save (create or update)
     */
    public function save()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Validate input using CI4 validation
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|max_length[35]',
            'email' => 'permit_empty|valid_email|max_length[50]',
            'broker_pct' => 'permit_empty|decimal|max_length[10]',
            'fleet_pct' => 'permit_empty|decimal|max_length[10]',
            'company_pct' => 'permit_empty|decimal|max_length[10]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            // Validation failed - reload form with errors
            $agent = $this->buildAgentFromPost();

            $data = [
                'pageTitle' => 'Agent Maintenance - TLS Operations',
                'agent' => $agent,
                'isNewAgent' => empty($this->request->getPost('agent_key'))
            ];

            return $this->renderView('safety/agent_maintenance', $data);
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
            $agent = $this->buildAgentFromPost();
            $data = [
                'pageTitle' => 'Agent Maintenance - TLS Operations',
                'agent' => $agent,
                'isNewAgent' => empty($this->request->getPost('agent_key'))
            ];

            $this->session->setFlashdata('error', 'An inactive agent must have an End Date. Please enter an End Date to deactivate this agent.');
            return $this->renderView('safety/agent_maintenance', $data);
        }

        if ($isActiveChecked && $hasEndDate) {
            // User checked Active but provided an End Date
            $agent = $this->buildAgentFromPost();
            $data = [
                'pageTitle' => 'Agent Maintenance - TLS Operations',
                'agent' => $agent,
                'isNewAgent' => empty($this->request->getPost('agent_key'))
            ];

            $this->session->setFlashdata('error', 'An active agent cannot have an End Date. Please remove the End Date or uncheck Active to deactivate this agent.');
            return $this->renderView('safety/agent_maintenance', $data);
        }

        // Get form data
        $agentKey = intval($this->request->getPost('agent_key') ?? 0);
        $isNewAgent = ($agentKey == 0);

        try {
            // Prepare data array
            $agentData = [
                'AgentKey' => $agentKey,
                'Name' => $this->request->getPost('name'),
                'Active' => $this->request->getPost('active') ? 1 : 0,
                'StartDate' => $this->request->getPost('start_date'),
                'EndDate' => $this->request->getPost('end_date'),
                'Email' => $this->request->getPost('email'),
                'Division' => intval($this->request->getPost('division') ?? 1),
                'BrokerPct' => floatval($this->request->getPost('broker_pct') ?? 0),
                'FleetPct' => floatval($this->request->getPost('fleet_pct') ?? 0),
                'CompanyPct' => floatval($this->request->getPost('company_pct') ?? 0),
                'FullFreightPay' => $this->request->getPost('full_freight_pay') ? 1 : 0,
                'TaxID' => $this->request->getPost('tax_id'),
                'IDType' => $this->request->getPost('id_type')
            ];

            if ($this->getAgentModel()->saveAgent($agentData)) {
                // If new agent, create a blank address and link it
                if ($isNewAgent) {
                    // The AgentModel's saveAgent() generates the new AgentKey
                    // We need to retrieve it by searching for the agent we just created
                    $newAgent = $this->getAgentModel()->searchAgentByName($agentData['Name']);

                    if ($newAgent && isset($newAgent['AgentKey'])) {
                        $newAgentKey = $newAgent['AgentKey'];

                        // Create a blank address for the new agent
                        $newNameKey = $this->getAddressModel()->createBlankAddress('AG');

                        if ($newNameKey > 0) {
                            // Link the address to the agent
                            $this->getAddressModel()->linkAgentAddress($newAgentKey, $newNameKey);
                        }

                        // Redirect to load the newly created agent
                        return redirect()->to('/safety/agent-maintenance/load/' . $newAgentKey)
                            ->with('success', 'Agent created successfully.');
                    } else {
                        return redirect()->to('/safety/agent-maintenance')
                            ->with('success', 'Agent created successfully.');
                    }
                } else {
                    // Reload the agent to show updated data
                    return redirect()->to('/safety/agent-maintenance/load/' . $agentKey)
                        ->with('success', 'Agent updated successfully.');
                }
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to save agent.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Agent maintenance save error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred.');
        }
    }

    /**
     * Create new agent with confirmation
     * Generates AgentKey immediately and creates minimal agent record
     * This allows dependent objects (Address, Contacts, Comments) to be added
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON response with new AgentKey
     */
    public function createNewAgent()
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        try {
            // Create minimal agent data with defaults
            $agentData = [
                'AgentKey' => 0,  // Will trigger getNextKey in saveAgent
                'Name' => 'New Agent',
                'Active' => 1,
                'StartDate' => date('Y-m-d'),
                'EndDate' => '',
                'Email' => '',
                'Division' => 1,
                'BrokerPct' => 0,
                'FleetPct' => 0,
                'CompanyPct' => 0,
                'FullFreightPay' => 0,
                'TaxID' => '',
                'IDType' => 'O'
            ];

            // Save the agent to get a real AgentKey
            $saved = $this->getAgentModel()->saveAgent($agentData);

            if ($saved) {
                // Get the newly created agent to return the AgentKey
                // The saveAgent method doesn't return the key, so we need to search for it
                // Since we just created it with "New Agent" name, find the most recent one
                $query = $db->query("SELECT TOP 1 AgentKey FROM tAgents WHERE NAME = 'New Agent' ORDER BY AgentKey DESC");
                $result = $query->getRowArray();

                if ($result && isset($result['AgentKey'])) {
                    $agentKey = $result['AgentKey'];

                    log_message('info', "Created new agent with AgentKey: {$agentKey}");

                    // Create blank address for the new agent
                    $blankNameKey = $this->getAddressModel()->createBlankAddress('AG');
                    if ($blankNameKey > 0) {
                        // Link the address to the agent
                        $this->getAddressModel()->linkAgentAddress($agentKey, $blankNameKey);
                        log_message('info', "Created blank address (NameKey: {$blankNameKey}) for new agent {$agentKey}");
                    }

                    return $this->response->setJSON([
                        'success' => true,
                        'agent_key' => $agentKey,
                        'message' => 'New agent created successfully'
                    ]);
                }
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create new agent'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error creating new agent: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Load agent by AgentKey (for redirects after save)
     */
    public function load(int $agentKey)
    {
        // Require authentication and permission
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        // Search for agent
        $agent = $this->getAgentModel()->getAgent($agentKey);

        if ($agent) {
            // Prepare view data
            $data = [
                'pageTitle' => 'Agent Maintenance - TLS Operations',
                'agent' => $agent,
                'isNewAgent' => false
            ];

            return $this->renderView('safety/agent_maintenance', $data);
        } else {
            return redirect()->to('/safety/agent-maintenance')
                ->with('warning', 'Agent not found.');
        }
    }

    /**
     * Autocomplete API endpoint for agent search
     */
    public function autocomplete()
    {
        // Require authentication
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');

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

        $agents = $this->getAgentModel()->searchAgentsForAutocomplete($searchTerm, $includeInactive);

        log_message('info', "Autocomplete search - Found " . count($agents) . " agents");

        return $this->response->setJSON($agents);
    }

    /**
     * Get new agent template
     *
     * @return array Default values for new agent
     */
    private function getNewAgentTemplate(): array
    {
        return [
            'AgentKey' => 0,
            'NAME' => '',
            'ACTIVE' => 1,
            'StartDate' => null,
            'EndDate' => null,
            'Email' => '',
            'Division' => 1,
            'BrokerPct' => 0,
            'FleetPct' => 0,
            'CompanyPct' => 0,
            'FullFreightPay' => 0,
            'TaxID' => '',
            'IDType' => 'O'
        ];
    }

    /**
     * Build agent array from POST data (for validation failure reload)
     *
     * @return array Agent data from POST
     */
    private function buildAgentFromPost(): array
    {
        return [
            'AgentKey' => intval($this->request->getPost('agent_key') ?? 0),
            'NAME' => $this->request->getPost('name'),
            'ACTIVE' => $this->request->getPost('active') ? 1 : 0,
            'StartDate' => $this->request->getPost('start_date'),
            'EndDate' => $this->request->getPost('end_date'),
            'Email' => $this->request->getPost('email'),
            'Division' => intval($this->request->getPost('division') ?? 1),
            'BrokerPct' => floatval($this->request->getPost('broker_pct') ?? 0),
            'FleetPct' => floatval($this->request->getPost('fleet_pct') ?? 0),
            'CompanyPct' => floatval($this->request->getPost('company_pct') ?? 0),
            'FullFreightPay' => $this->request->getPost('full_freight_pay') ? 1 : 0,
            'TaxID' => $this->request->getPost('tax_id'),
            'IDType' => $this->request->getPost('id_type')
        ];
    }

    /**
     * Get agent's address (AJAX endpoint)
     */
    public function getAddress()
    {
        // Require authentication
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $agentKey = intval($this->request->getGet('agent_key') ?? 0);

        if ($agentKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid agent key']);
        }

        $address = $this->getAgentModel()->getAgentAddress($agentKey);

        if ($address) {
            return $this->response->setJSON(['success' => true, 'address' => $address]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Address not found']);
        }
    }

    /**
     * Save agent's address (AJAX endpoint)
     */
    public function saveAddress()
    {
        // Require authentication
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');

        // Get database with guaranteed tenant context
        $db = $this->getCustomerDb();

        $agentKey = intval($this->request->getPost('agent_key') ?? 0);
        $nameKey = intval($this->request->getPost('name_key') ?? 0);

        if ($agentKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid agent key']);
        }

        try {
            // Prepare address data
            $addressData = [
                'NameKey' => $nameKey,
                'Name1' => $this->request->getPost('name1'),
                'Name2' => $this->request->getPost('name2'),
                'NameQual' => 'AG', // Agent qualifier
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
                // If this was a new address, link it to the agent
                if ($nameKey == 0) {
                    $linked = $this->getAddressModel()->linkAgentAddress($agentKey, $savedNameKey);
                    if (!$linked) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Address saved but failed to link to agent'
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
     * Get contacts for an agent (AJAX endpoint)
     * Uses 3-level chain: Agent → NameAddress → Contact
     */
    public function getContacts()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');
        $db = $this->getCustomerDb();

        $agentKey = intval($this->request->getGet('agent_key') ?? 0);

        if ($agentKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid agent key']);
        }

        try {
            $contacts = $this->getAgentModel()->getAgentContacts($agentKey);

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
     * Get validation options for ContactFunction (AJAX endpoint)
     * Returns cached validation table entries
     */
    public function getContactFunctionOptions()
    {
        $this->requireAuth();
        $db = $this->getCustomerDb();

        try {
            $options = $this->getAgentModel()->getValidationOptions('ContactFunction');

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

    /**
     * Save contact (AJAX endpoint)
     * Creates or updates a contact
     */
    public function saveContact()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');
        $db = $this->getCustomerDb();

        $agentKey = intval($this->request->getPost('agent_key') ?? 0);
        $contactKey = intval($this->request->getPost('contact_key') ?? 0);

        if ($agentKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid agent key']);
        }

        try {
            $contactData = [
                'ContactKey' => $contactKey,
                'ContactName' => $this->request->getPost('contact_name'),
                'ContactFunction' => $this->request->getPost('contact_function'),
                'TelephoneNo' => $this->request->getPost('telephone_no')
            ];

            $savedContactKey = $this->getContactModel()->saveContact($contactData, $agentKey);

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
        $this->requireMenuPermission('mnuAgentMaint');
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
     * Get comments for agent (AJAX endpoint)
     * Returns array of comments with details
     */
    public function getComments()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');
        $db = $this->getCustomerDb();

        $agentKey = intval($this->request->getGet('agent_key') ?? 0);

        if ($agentKey <= 0) {
            return $this->response->setJSON(['success' => false, 'comments' => []]);
        }

        try {
            $comments = $this->getAgentModel()->getAgentComments($agentKey);

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
     * Creates or updates a comment for an agent
     */
    public function saveComment()
    {
        $this->requireAuth();
        $this->requireMenuPermission('mnuAgentMaint');
        $db = $this->getCustomerDb();

        $agentKey = intval($this->request->getPost('agent_key') ?? 0);
        $commentKey = intval($this->request->getPost('comment_key') ?? 0);

        if ($agentKey <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid agent key']);
        }

        try {
            // Get current user ID from session
            $user = $this->getCurrentUser();
            $userId = $user['user_id'] ?? 'UNKNOWN';

            $commentData = [
                'CommentKey' => $commentKey,
                'Comment' => $this->request->getPost('comment')
            ];

            $savedCommentKey = $this->getCommentModel()->saveComment($commentData, $agentKey, $userId);

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
        $this->requireMenuPermission('mnuAgentMaint');
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
