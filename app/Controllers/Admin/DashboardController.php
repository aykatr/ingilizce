<?php

namespace App\Controllers\Admin;

class DashboardController extends AdminBaseController
{
    public function index(): void
    {
        $this->view('admin.dashboard', [
            'title' => 'Panel',
            'admin' => $this->admin,
        ], 'admin');
    }
}
