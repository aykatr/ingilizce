<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\AchievementMessageRepository;
use App\Services\AchievementMessageService;
use App\Services\AnimationTypes;
use App\Services\Exceptions\ValidationException;
use App\Services\MediaUploadService;

class AchievementMessageController extends AdminBaseController
{
    private AchievementMessageService $messageService;

    public function __construct()
    {
        parent::__construct();

        $media = new MediaUploadService(config('app.uploads_path'));
        $this->messageService = new AchievementMessageService(new AchievementMessageRepository(), $media);
    }

    public function index(): void
    {
        $all = $this->messageService->list();

        $this->view('admin.messages.index', [
            'title' => 'Başarı Mesajları',
            'correctMessages' => array_values(array_filter($all, fn ($m) => $m['type'] === 'correct')),
            'wrongMessages' => array_values(array_filter($all, fn ($m) => $m['type'] === 'wrong')),
            'success' => Session::getFlash('success'),
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('admin.messages.create', [
            'title' => 'Yeni Başarı Mesajı',
            'animationTypes' => AnimationTypes::OPTIONS,
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function store(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/messages/create'));
        }

        try {
            $this->messageService->create($request->all(), $_FILES);
            Session::flash('success', 'Mesaj oluşturuldu.');
            $this->redirect(base_url('admin/messages'));
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect(base_url('admin/messages/create'));
        }
    }

    public function edit(int|string $id): void
    {
        $message = $this->messageService->find($id);

        if (!$message) {
            $this->redirect(base_url('admin/messages'));
        }

        $this->view('admin.messages.edit', [
            'title' => 'Mesajı Düzenle',
            'message' => $message,
            'animationTypes' => AnimationTypes::OPTIONS,
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function update(int|string $id): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url("admin/messages/{$id}/edit"));
        }

        try {
            $this->messageService->update($id, $request->all(), $_FILES);
            Session::flash('success', 'Mesaj güncellendi.');
            $this->redirect(base_url('admin/messages'));
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect(base_url("admin/messages/{$id}/edit"));
        }
    }

    public function destroy(int|string $id): void
    {
        $request = new Request();

        if (Session::verifyCsrf($request->input('_csrf'))) {
            $this->messageService->delete($id);
            Session::flash('success', 'Mesaj silindi.');
        }

        $this->redirect(base_url('admin/messages'));
    }
}
