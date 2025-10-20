<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgentModel;

/**
 * Agent Maintenance Controller
 *
 * Handles agent creation, editing, and search.
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class AgentMaintenance extends BaseController
{
    private ?AgentModel $agentModel = null;

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
                // If new agent, we need to get the key that was generated
                if ($isNewAgent) {
                    return redirect()->to('/safety/agent-maintenance')
                        ->with('success', 'Agent created successfully.');
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
}
