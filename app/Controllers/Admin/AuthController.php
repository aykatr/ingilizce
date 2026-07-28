<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Repositories\AdminRepository;
use App\Services\AuthService;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService(new AdminRepository());
    }

    public function showLoginForm(): void
    {
        if (Auth::check()) {
            $this->redirect(base_url('admin/dashboard'));
        }

        $this->view('admin.login', [
            'title' => 'Admin Girişi',
            'error' => Session::getFlash('error'),
        ]);
    }

    public function login(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/login'));
        }

        $admin = $this->authService->attempt(
            trim((string) $request->input('username')),
            (string) $request->input('password')
        );

        if (!$admin) {
            Session::flash('error', 'Kullanıcı adı veya şifre hatalı.');
            $this->redirect(base_url('admin/login'));
        }

        Auth::login($admin['id']);
        $this->redirect(base_url('admin/dashboard'));
    }

    public function logout(): void
    {
        $request = new Request();

        if (Session::verifyCsrf($request->input('_csrf'))) {
            Auth::logout();
        }

        $this->redirect(base_url('admin/login'));
    }
}
