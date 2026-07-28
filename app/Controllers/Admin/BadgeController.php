<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\BadgeRepository;
use App\Services\AnimationTypes;
use App\Services\BadgeService;
use App\Services\Exceptions\ValidationException;
use App\Services\MediaUploadService;

class BadgeController extends AdminBaseController
{
    private BadgeService $badgeService;

    public function __construct()
    {
        parent::__construct();

        $media = new MediaUploadService(config('app.uploads_path'));
        $this->badgeService = new BadgeService(new BadgeRepository(), $media);
    }

    public function index(): void
    {
        $this->view('admin.badges.index', [
            'title' => 'Rozetler',
            'badges' => $this->badgeService->list(),
            'conditionLabels' => $this->badgeService->conditionTypes(),
            'success' => Session::getFlash('success'),
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('admin.badges.create', [
            'title' => 'Yeni Rozet',
            'conditionTypes' => $this->badgeService->conditionTypes(),
            'animationTypes' => AnimationTypes::OPTIONS,
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function store(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/badges/create'));
        }

        try {
            $this->badgeService->create($request->all(), $_FILES);
            Session::flash('success', 'Rozet oluşturuldu.');
            $this->redirect(base_url('admin/badges'));
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect(base_url('admin/badges/create'));
        }
    }

    public function edit(int|string $id): void
    {
        $badge = $this->badgeService->find($id);

        if (!$badge) {
            $this->redirect(base_url('admin/badges'));
        }

        $this->view('admin.badges.edit', [
            'title' => 'Rozeti Düzenle',
            'badge' => $badge,
            'conditionTypes' => $this->badgeService->conditionTypes(),
            'animationTypes' => AnimationTypes::OPTIONS,
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function update(int|string $id): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url("admin/badges/{$id}/edit"));
        }

        try {
            $this->badgeService->update($id, $request->all(), $_FILES);
            Session::flash('success', 'Rozet güncellendi.');
            $this->redirect(base_url('admin/badges'));
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect(base_url("admin/badges/{$id}/edit"));
        }
    }

    public function destroy(int|string $id): void
    {
        $request = new Request();

        if (Session::verifyCsrf($request->input('_csrf'))) {
            $this->badgeService->delete($id);
            Session::flash('success', 'Rozet silindi.');
        }

        $this->redirect(base_url('admin/badges'));
    }
}
