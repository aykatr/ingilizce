<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\AdminRepository;
use App\Services\AuthService;
use App\Services\Exceptions\ValidationException;

class PasswordController extends AdminBaseController
{
    private AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService(new AdminRepository());
    }

    public function edit(): void
    {
        $this->view('admin.password', [
            'title' => 'Şifre Değiştir',
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
        ], 'admin');
    }

    public function update(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/password'));
        }

        try {
            $this->authService->changePassword(
                $this->admin['id'],
                $this->admin,
                (string) $request->input('current_password'),
                (string) $request->input('new_password'),
                (string) $request->input('new_password_confirmation')
            );
            Session::flash('success', 'Şifreniz güncellendi.');
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect(base_url('admin/password'));
    }
}
