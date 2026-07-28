<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function showLoginForm(): void
    {
        $this->view('admin.login', ['title' => 'Admin Girişi']);
    }
}
