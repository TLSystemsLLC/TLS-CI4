<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Redirect root to dashboard (which will redirect to login if not authenticated)
$routes->get('/', 'Dashboard::index', ['filter' => 'auth']);

// Authentication routes
$routes->get('/login', 'Login::index');
$routes->post('/login/attempt', 'Login::attempt');
$routes->get('/logout', 'Login::logout');

// Dashboard (requires authentication)
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);

// Systems routes (require authentication)
$routes->group('systems', ['filter' => 'auth'], function($routes) {
    // User Maintenance
    $routes->get('user-maintenance', 'UserMaintenance::index');
    $routes->post('user-maintenance/search', 'UserMaintenance::search');
    $routes->post('user-maintenance/save', 'UserMaintenance::save');
    $routes->get('user-maintenance/load/(:segment)', 'UserMaintenance::load/$1');
    $routes->get('user-maintenance/autocomplete', 'UserMaintenance::autocomplete');

    // User Security
    $routes->get('user-security', 'UserSecurity::index');
    $routes->post('user-security/get-permissions', 'UserSecurity::getUserPermissions');
    $routes->post('user-security/save-permissions', 'UserSecurity::savePermissions');
    $routes->post('user-security/apply-role', 'UserSecurity::applyRoleTemplate');

    // Company & Division Maintenance
    $routes->get('company-division-maintenance', 'CompanyDivisionMaintenance::index');
    $routes->get('company-division-maintenance/load-company/(:num)', 'CompanyDivisionMaintenance::loadCompany/$1');
    $routes->post('company-division-maintenance/save-company', 'CompanyDivisionMaintenance::saveCompany');
    $routes->post('company-division-maintenance/create-company', 'CompanyDivisionMaintenance::createNewCompany');

    // Division endpoints
    $routes->get('company-division-maintenance/divisions/(:num)', 'CompanyDivisionMaintenance::getDivisions/$1');
    $routes->get('company-division-maintenance/division/(:num)/(:num)', 'CompanyDivisionMaintenance::loadDivision/$1/$2');
    $routes->post('company-division-maintenance/save-division', 'CompanyDivisionMaintenance::saveDivision');
    $routes->post('company-division-maintenance/create-division', 'CompanyDivisionMaintenance::createNewDivision');

    // Department endpoints
    $routes->get('company-division-maintenance/departments/(:num)/(:num)', 'CompanyDivisionMaintenance::getDepartments/$1/$2');
    $routes->post('company-division-maintenance/save-department', 'CompanyDivisionMaintenance::saveDepartment');
    $routes->delete('company-division-maintenance/delete-department/(:num)/(:num)/(:num)', 'CompanyDivisionMaintenance::deleteDepartment/$1/$2/$3');

    // Team endpoints
    $routes->get('company-division-maintenance/teams/(:num)/(:num)', 'CompanyDivisionMaintenance::getTeams/$1/$2');
    $routes->post('company-division-maintenance/save-team', 'CompanyDivisionMaintenance::saveTeam');
    $routes->delete('company-division-maintenance/delete-team/(:num)', 'CompanyDivisionMaintenance::deleteTeam/$1');
});

// Safety routes (require authentication)
$routes->group('safety', ['filter' => 'auth'], function($routes) {
    // Agent Maintenance
    $routes->get('agent-maintenance', 'AgentMaintenance::index');
    $routes->post('agent-maintenance/search', 'AgentMaintenance::search');
    $routes->post('agent-maintenance/create-new', 'AgentMaintenance::createNewAgent');
    $routes->post('agent-maintenance/save', 'AgentMaintenance::save');
    $routes->get('agent-maintenance/load/(:num)', 'AgentMaintenance::load/$1');
    $routes->get('agent-maintenance/autocomplete', 'AgentMaintenance::autocomplete');
    $routes->get('agent-maintenance/get-address', 'AgentMaintenance::getAddress');
    $routes->post('agent-maintenance/save-address', 'AgentMaintenance::saveAddress');
    $routes->get('agent-maintenance/get-contacts', 'AgentMaintenance::getContacts');
    $routes->get('agent-maintenance/get-contact-function-options', 'AgentMaintenance::getContactFunctionOptions');
    $routes->post('agent-maintenance/save-contact', 'AgentMaintenance::saveContact');
    $routes->post('agent-maintenance/delete-contact', 'AgentMaintenance::deleteContact');
    $routes->get('agent-maintenance/get-comments', 'AgentMaintenance::getComments');
    $routes->post('agent-maintenance/save-comment', 'AgentMaintenance::saveComment');
    $routes->post('agent-maintenance/delete-comment', 'AgentMaintenance::deleteComment');

    // Driver Maintenance (uses base template)
    $routes->get('driver-maintenance', 'DriverMaintenance::index');
    $routes->post('driver-maintenance/search', 'DriverMaintenance::search');
    $routes->post('driver-maintenance/create-new', 'DriverMaintenance::createNew');
    $routes->post('driver-maintenance/save', 'DriverMaintenance::save');
    $routes->get('driver-maintenance/load/(:num)', 'DriverMaintenance::load/$1');
    $routes->get('driver-maintenance/autocomplete', 'DriverMaintenance::autocomplete');
    $routes->get('driver-maintenance/get-address', 'DriverMaintenance::getAddress');
    $routes->post('driver-maintenance/save-address', 'DriverMaintenance::saveAddress');
    $routes->get('driver-maintenance/get-contacts', 'DriverMaintenance::getContacts');
    $routes->get('driver-maintenance/get-contact-function-options', 'DriverMaintenance::getContactFunctionOptions');
    $routes->post('driver-maintenance/save-contact', 'DriverMaintenance::saveContact');
    $routes->post('driver-maintenance/delete-contact', 'DriverMaintenance::deleteContact');
    $routes->get('driver-maintenance/get-comments', 'DriverMaintenance::getComments');
    $routes->post('driver-maintenance/save-comment', 'DriverMaintenance::saveComment');
    $routes->post('driver-maintenance/delete-comment', 'DriverMaintenance::deleteComment');

    // Owner Maintenance
    $routes->get('owner-maintenance', 'OwnerMaintenance::index');
    $routes->post('owner-maintenance/search', 'OwnerMaintenance::search');
    $routes->post('owner-maintenance/create-new', 'OwnerMaintenance::createNewOwner');
    $routes->post('owner-maintenance/save', 'OwnerMaintenance::save');
    $routes->get('owner-maintenance/load/(:num)', 'OwnerMaintenance::load/$1');
    $routes->get('owner-maintenance/autocomplete', 'OwnerMaintenance::autocomplete');
    $routes->get('owner-maintenance/get-address', 'OwnerMaintenance::getAddress');
    $routes->post('owner-maintenance/save-address', 'OwnerMaintenance::saveAddress');
    $routes->get('owner-maintenance/get-contacts', 'OwnerMaintenance::getContacts');
    $routes->post('owner-maintenance/save-contact', 'OwnerMaintenance::saveContact');
    $routes->post('owner-maintenance/delete-contact', 'OwnerMaintenance::deleteContact');
    $routes->get('owner-maintenance/get-comments', 'OwnerMaintenance::getComments');
    $routes->post('owner-maintenance/save-comment', 'OwnerMaintenance::saveComment');
    $routes->post('owner-maintenance/delete-comment', 'OwnerMaintenance::deleteComment');
});

// Keep test route for development
$routes->get('/home/test', 'Home::test');
