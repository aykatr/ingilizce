<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Models\Admin;

class AuthController extends BaseController
{
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

        $username = trim((string) $request->input('username'));
        $password = (string) $request->input('password');
        $admin = Admin::findByUsername($username);

        if (!$admin || !$admin['is_active'] || !password_verify($password, $admin['password'])) {
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
