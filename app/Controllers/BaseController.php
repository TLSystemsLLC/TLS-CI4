<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\TLSAuth;
use App\Libraries\MenuManager;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * TLS Authentication instance
     *
     * @var TLSAuth
     */
    protected $auth;

    /**
     * Session instance
     *
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * Database instance
     *
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * Menu Manager instance
     *
     * @var MenuManager|null
     */
    protected $menuManager;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload TLS authentication, session, and database
        $this->auth = new TLSAuth();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();

        // CRITICAL: Set database context to customer database if logged in
        // This ensures all database operations happen in the correct tenant database
        if ($this->auth->isLoggedIn()) {
            $customerDb = $this->session->get('customer_db');
            if ($customerDb) {
                $this->db->setDatabase($customerDb);
            }

            // Initialize MenuManager for logged-in users
            // MenuManager reads user permissions from session (loaded by TLSAuth at login)
            $this->menuManager = new MenuManager($this->session);
        }
    }

    /**
     * Require authentication for this controller
     * Call this in controller constructor or method to protect routes
     *
     * @param string $redirectUrl URL to redirect to for login
     * @return void
     */
    protected function requireAuth(string $redirectUrl = '/login'): void
    {
        if (!$this->auth->isLoggedIn()) {
            // Store intended URL
            $this->session->set('redirect_url', current_url());
            redirect()->to($redirectUrl)->send();
            exit;
        }
    }

    /**
     * Check if user has menu access
     *
     * @param string $menuName Menu name to check
     * @return bool
     */
    protected function hasMenuAccess(string $menuName): bool
    {
        return $this->auth->hasMenuAccess($menuName);
    }

    /**
     * Require specific menu permission
     * Returns 403 if user doesn't have access
     *
     * @param string $menuName Menu name required
     * @return void
     */
    protected function requireMenuPermission(string $menuName): void
    {
        if (!$this->hasMenuAccess($menuName)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'You do not have permission to access this page.'
            );
        }
    }

    /**
     * Get current user information
     *
     * @return array|null
     */
    protected function getCurrentUser(): ?array
    {
        return $this->auth->getCurrentUser();
    }

    /**
     * Get current customer database
     *
     * @return string|null
     */
    protected function getCurrentDatabase(): ?string
    {
        $user = $this->getCurrentUser();
        return $user['customer_db'] ?? null;
    }

    /**
     * Get database connection with guaranteed tenant context
     * This method ensures the database connection is set to the current customer's database
     *
     * @return \CodeIgniter\Database\BaseConnection
     * @throws \RuntimeException if not logged in or no customer database context
     */
    protected function getCustomerDb(): \CodeIgniter\Database\BaseConnection
    {
        if (!$this->auth->isLoggedIn()) {
            throw new \RuntimeException('Cannot get customer database: User not logged in');
        }

        $customerDb = $this->getCurrentDatabase();
        if (!$customerDb) {
            throw new \RuntimeException('Cannot get customer database: No customer database in session');
        }

        // Ensure database context is set correctly
        $this->db->setDatabase($customerDb);

        return $this->db;
    }

    /**
     * Prepare common view data including menu structure
     * Call this method to get standard data for all views
     *
     * @param array $additionalData Additional data to merge
     * @return array View data with menu structure and user info
     */
    protected function prepareViewData(array $additionalData = []): array
    {
        $data = $additionalData;

        // Add menu structure if user is logged in
        if ($this->auth->isLoggedIn() && $this->menuManager) {
            $data['menuStructure'] = $this->menuManager->getMenuStructure();
            $data['currentUser'] = $this->getCurrentUser();
        }

        return $data;
    }

    /**
     * Render view with automatic menu data injection
     * Use this instead of view() to automatically include menu structure
     *
     * @param string $name View name
     * @param array $data View data
     * @param array $options View options
     * @return string Rendered view
     */
    protected function renderView(string $name, array $data = [], array $options = []): string
    {
        $viewData = $this->prepareViewData($data);
        return view($name, $viewData, $options);
    }
}
