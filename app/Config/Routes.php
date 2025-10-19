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

// Keep test route for development
$routes->get('/home/test', 'Home::test');
