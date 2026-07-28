<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Repositories\AdminRepository;
use App\Repositories\AuditLogRepository;
use App\Services\AuditLogService;
use App\Services\AuthService;

class AuthController extends BaseController
{
    private AuthService $authService;
    private AuditLogService $auditLog;

    public function __construct()
    {
        $this->authService = new AuthService(new AdminRepository());
        $this->auditLog = new AuditLogService(new AuditLogRepository());
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
        $this->auditLog->record('auth.login', "'{$admin['username']}' giriş yaptı.");
        $this->redirect(base_url('admin/dashboard'));
    }

    public function logout(): void
    {
        $request = new Request();

        if (Session::verifyCsrf($request->input('_csrf'))) {
            $admin = Auth::user();

            if ($admin) {
                $this->auditLog->record('auth.logout', "'{$admin['username']}' çıkış yaptı.");
            }

            Auth::logout();
        }

        $this->redirect(base_url('admin/login'));
    }
}
