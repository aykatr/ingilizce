<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Helpers\Str;
use App\Models\License;

class LicenseController extends AdminBaseController
{
    public function index(): void
    {
        $this->view('admin.licenses.index', [
            'title' => 'Lisanslar',
            'licenses' => License::all(),
            'success' => Session::getFlash('success'),
            'newLink' => Session::getFlash('license_link'),
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('admin.licenses.create', [
            'title' => 'Yeni Lisans',
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function store(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/licenses/create'));
        }

        $name = trim((string) $request->input('name'));

        if ($name === '') {
            Session::flash('error', 'Lisans adı gerekli.');
            $this->redirect(base_url('admin/licenses/create'));
        }

        do {
            $token = Str::random(32);
        } while (License::findByToken($token));

        License::create([
            'name' => $name,
            'token' => $token,
            'is_active' => 1,
        ]);

        Session::flash('success', 'Lisans oluşturuldu.');
        Session::flash('license_link', base_url('play.php?token=' . $token));
        $this->redirect(base_url('admin/licenses'));
    }

    public function toggle(int|string $id): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            $this->redirect(base_url('admin/licenses'));
        }

        $license = License::find($id);

        if ($license) {
            License::update($id, ['is_active' => $license['is_active'] ? 0 : 1]);
        }

        $this->redirect(base_url('admin/licenses'));
    }
}
