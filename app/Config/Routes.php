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
});

// Keep test route for development
$routes->get('/home/test', 'Home::test');
