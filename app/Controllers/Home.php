<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function test(): string
    {
        $data = [
            'pageTitle' => 'TLS CodeIgniter 4 - Proof of Concept',
            'testMessage' => 'If you can see this page with the TLS theme, the setup is working!'
        ];

        return view('test_page', $data);
    }
}
