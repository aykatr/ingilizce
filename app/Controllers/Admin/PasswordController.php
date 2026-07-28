<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\Admin;

class PasswordController extends AdminBaseController
{
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

        $current = (string) $request->input('current_password');
        $new = (string) $request->input('new_password');
        $confirm = (string) $request->input('new_password_confirmation');

        if (!password_verify($current, $this->admin['password'])) {
            Session::flash('error', 'Mevcut şifre hatalı.');
            $this->redirect(base_url('admin/password'));
        }

        if (mb_strlen($new) < 8) {
            Session::flash('error', 'Yeni şifre en az 8 karakter olmalı.');
            $this->redirect(base_url('admin/password'));
        }

        if ($new !== $confirm) {
            Session::flash('error', 'Yeni şifreler eşleşmiyor.');
            $this->redirect(base_url('admin/password'));
        }

        Admin::update($this->admin['id'], [
            'password' => password_hash($new, PASSWORD_DEFAULT),
        ]);

        Session::flash('success', 'Şifreniz güncellendi.');
        $this->redirect(base_url('admin/password'));
    }
}
