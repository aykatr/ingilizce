<?php

namespace App\Controllers;

use App\Core\Database;
use PDOException;

class HomeController extends BaseController
{
    public function index(): void
    {
        $this->view('home.index', ['title' => 'Ana Sayfa']);
    }

    public function health(): void
    {
        try {
            Database::connection();
            $dbStatus = 'connected';
        } catch (PDOException) {
            $dbStatus = 'unreachable';
        }

        $this->json([
            'status' => 'ok',
            'database' => $dbStatus,
        ]);
    }
}
