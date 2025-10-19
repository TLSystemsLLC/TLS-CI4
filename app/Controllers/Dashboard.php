<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    /**
     * Dashboard home page
     * Requires authentication
     */
    public function index()
    {
        // Require authentication
        $this->requireAuth();

        // Get current user
        $user = $this->getCurrentUser();

        $data = [
            'pageTitle' => 'Dashboard - TLS Operations',
            'user' => $user
        ];

        return view('dashboard/index', $data);
    }
}
