<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\AuditLogRepository;
use App\Repositories\CategoryRepository;
use App\Services\AuditLogService;
use App\Services\CategoryService;
use App\Services\Exceptions\ValidationException;

class CategoryController extends AdminBaseController
{
    private CategoryService $categoryService;
    private AuditLogService $auditLog;

    public function __construct()
    {
        parent::__construct();
        $this->categoryService = new CategoryService(new CategoryRepository());
        $this->auditLog = new AuditLogService(new AuditLogRepository());
    }

    public function index(): void
    {
        $this->view('admin.categories.index', [
            'title' => 'Kategoriler',
            'categories' => $this->categoryService->list(),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('admin.categories.create', [
            'title' => 'Yeni Kategori',
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function store(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/categories/create'));
        }

        try {
            $name = (string) $request->input('name');
            $this->categoryService->create($name);
            $this->auditLog->record('category.create', "'{$name}' kategorisi oluşturuldu.");
            Session::flash('success', 'Kategori oluşturuldu.');
            $this->redirect(base_url('admin/categories'));
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect(base_url('admin/categories/create'));
        }
    }

    public function edit(int|string $id): void
    {
        $category = $this->categoryService->find($id);

        if (!$category) {
            $this->redirect(base_url('admin/categories'));
        }

        $this->view('admin.categories.edit', [
            'title' => 'Kategori Düzenle',
            'category' => $category,
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function update(int|string $id): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url("admin/categories/{$id}/edit"));
        }

        try {
            $name = (string) $request->input('name');
            $this->categoryService->update($id, $name);
            $this->auditLog->record('category.update', "'{$name}' kategorisi güncellendi (#{$id}).");
            Session::flash('success', 'Kategori güncellendi.');
            $this->redirect(base_url('admin/categories'));
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect(base_url("admin/categories/{$id}/edit"));
        }
    }

    public function destroy(int|string $id): void
    {
        $request = new Request();

        if (Session::verifyCsrf($request->input('_csrf'))) {
            try {
                $this->categoryService->delete($id);
                $this->auditLog->record('category.delete', "Kategori silindi (#{$id}).");
                Session::flash('success', 'Kategori silindi.');
            } catch (ValidationException $e) {
                Session::flash('error', $e->getMessage());
            }
        }

        $this->redirect(base_url('admin/categories'));
    }
}
