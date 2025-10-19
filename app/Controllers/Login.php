<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Login extends BaseController
{
    /**
     * Display login form
     */
    public function index()
    {
        // If already logged in, redirect to dashboard
        if ($this->auth->isLoggedIn()) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'pageTitle' => 'Login - TLS Operations',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/login', $data);
    }

    /**
     * Process login form submission
     */
    public function attempt()
    {
        // Validate form input using CI4's validation
        $validationRules = [
            'customer' => [
                'label' => 'Customer',
                'rules' => 'required|alpha_numeric_punct|max_length[50]',
                'errors' => [
                    'required' => 'Customer ID is required',
                    'alpha_numeric_punct' => 'Customer ID contains invalid characters',
                    'max_length' => 'Customer ID is too long'
                ]
            ],
            'user_id' => [
                'label' => 'User ID',
                'rules' => 'required|max_length[50]',
                'errors' => [
                    'required' => 'User ID is required',
                    'max_length' => 'User ID is too long'
                ]
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Password is required'
                ]
            ]
        ];

        if (!$this->validate($validationRules)) {
            // Validation failed - return to login with errors
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get form data
        $customer = $this->request->getPost('customer');
        $userId = $this->request->getPost('user_id');
        $password = $this->request->getPost('password');

        // Attempt login
        $result = $this->auth->login($customer, $userId, $password);

        if ($result['success']) {
            // Login successful
            log_message('info', "Successful login: {$userId} to {$customer}");

            // Check if there's a redirect URL stored
            $redirectUrl = $this->session->get('redirect_url');
            if ($redirectUrl) {
                $this->session->remove('redirect_url');
                return redirect()->to($redirectUrl);
            }

            // Default redirect to dashboard
            return redirect()->to('/dashboard');
        } else {
            // Login failed
            log_message('warning', "Failed login: {$userId} to {$customer} - {$result['message']}");

            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $this->auth->logout();

        return redirect()->to('/login')->with('message', 'You have been logged out successfully');
    }
}
