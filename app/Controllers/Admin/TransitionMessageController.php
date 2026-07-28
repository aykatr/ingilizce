<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\TransitionMessageRepository;
use App\Services\AnimationTypes;
use App\Services\Exceptions\ValidationException;
use App\Services\MediaUploadService;
use App\Services\TransitionMessageService;

class TransitionMessageController extends AdminBaseController
{
    private TransitionMessageService $messageService;

    public function __construct()
    {
        parent::__construct();

        $media = new MediaUploadService(config('app.uploads_path'));
        $this->messageService = new TransitionMessageService(new TransitionMessageRepository(), $media);
    }

    public function index(): void
    {
        $this->view('admin.transition_messages.index', [
            'title' => 'Geçiş Mesajları',
            'messages' => $this->messageService->list(),
            'success' => Session::getFlash('success'),
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('admin.transition_messages.create', [
            'title' => 'Yeni Geçiş Mesajı',
            'animationTypes' => AnimationTypes::OPTIONS,
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function store(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/transition-messages/create'));
        }

        try {
            $this->messageService->create($request->all(), $_FILES);
            Session::flash('success', 'Mesaj oluşturuldu.');
            $this->redirect(base_url('admin/transition-messages'));
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect(base_url('admin/transition-messages/create'));
        }
    }

    public function edit(int|string $id): void
    {
        $message = $this->messageService->find($id);

        if (!$message) {
            $this->redirect(base_url('admin/transition-messages'));
        }

        $this->view('admin.transition_messages.edit', [
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
            $this->redirect(base_url("admin/transition-messages/{$id}/edit"));
        }

        try {
            $this->messageService->update($id, $request->all(), $_FILES);
            Session::flash('success', 'Mesaj güncellendi.');
            $this->redirect(base_url('admin/transition-messages'));
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect(base_url("admin/transition-messages/{$id}/edit"));
        }
    }

    public function destroy(int|string $id): void
    {
        $request = new Request();

        if (Session::verifyCsrf($request->input('_csrf'))) {
            $this->messageService->delete($id);
            Session::flash('success', 'Mesaj silindi.');
        }

        $this->redirect(base_url('admin/transition-messages'));
    }
}
