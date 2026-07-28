<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Auth;

abstract class AdminBaseController extends BaseController
{
    protected array $admin;

    public function __construct()
    {
        if (!Auth::check()) {
            $this->redirect(base_url('admin/login'));
        }

        $this->admin = Auth::user();
    }
}
